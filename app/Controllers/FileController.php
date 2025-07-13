<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\File;
use Core\Logger;

class FileController extends Controller
{
    private $fileModel;
    private Logger $logger;

    public function __construct(File $fileModel)
    {
        parent::__construct();
        $this->fileModel = $fileModel;
        $this->logger = new Logger();
    }

    // проверяет авторизован ли пользователь
    private function checkAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->logger->warning("Необходима авторизация");
            $this->response->json(['error' => 'Необходима авторизация'], 401);
        }
    }

    // вывести список файлов пользователя
    public function listFiles()
    {
        try {
            $this->checkAuth();

            $files = $this->fileModel->getFilesByUser($_SESSION['user_id']);
            if (!$files) {
                $this->logger->info("Файлы не найдены для пользователя ID: {$_SESSION['user_id']}");
                $this->response->json(['error' => 'Файлы не найдены'], 404);
            }
            $this->logger->info("Файлы успешно получены для пользователя ID: {$_SESSION['user_id']}");
            $this->response->json($files);
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при получении списка файлов: " . $e->getMessage());
            return $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // получить информацию о конкретном файле
    public function getFile($id)
    {
        try {
            $this->checkAuth();

            $file = $this->fileModel->getFileById($id, $_SESSION['user_id']);
            if (!$file) {
                $this->logger->warning("Файл ID $id не найден для пользователя ID {$_SESSION['user_id']}");
                $this->response->json(['error' => 'Файл не найден'], 404);
            }

            $this->logger->info("Файл ID $id успешно получен пользователем ID {$_SESSION['user_id']}");
            $this->response->json($file);
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при получении файла ID $id: " . $e->getMessage());
            return $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // загрузка нового файла
    public function addFile()
    {
        try {
            $this->checkAuth();

            if (!isset($_FILES['file'])) {
                $this->logger->warning("Файл не загружен");
                $this->response->json(['error' => 'Файл не загружен'], 400);
            }
    
            $originalName = $_FILES['file']['name'];
            $tempPath     = $_FILES['file']['tmp_name'];
            $extension    = pathinfo($originalName, PATHINFO_EXTENSION);
            $encryptedName = md5(uniqid()) . '.' . $extension;
            $destination  = "../uploads/$encryptedName";
    
            if (move_uploaded_file($tempPath, $destination)) {
                $this->fileModel->create($_SESSION['user_id'], $originalName, $encryptedName, $destination);
                
                $this->logger->info("Файл '{$originalName}' успешно загружен пользователем ID {$_SESSION['user_id']}");
                $this->response->json(['success' => 'Файл загружен', 'file' => $originalName]);
            } else {
                $this->logger->error("Не удалось переместить файл '{$originalName}' на сервере");
                $this->response->json(['error' => 'Ошибка загрузки файла'], 500);
            }
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при загрузке файла: " . $e->getMessage());
            return $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // удаляет файл
    public function removeFile($id)
    {
        try {
            $this->checkAuth();

            $file = $this->fileModel->getFileById($id, $_SESSION['user_id']);
            if (!$file) {
                $this->logger->warning("Удаление файла: файл с ID {$id} не найден для пользователя ID {$_SESSION['user_id']}");
                $this->response->json(['error' => 'Файл не найден'], 404);
            }
            $filePath = __DIR__ . '/../../public/' . $file['file_path'];
            if (!file_exists($filePath)) {
                $this->logger->error("Файл {$file['file_path']} отсутствует в файловой системе");
                $this->response->json(['error' => 'Файл не найден в файловой системе'], 404);
            }
            if (unlink($filePath)) {
                $this->fileModel->remove($id);
                $this->logger->info("Файл ID {$id} удалён пользователем ID {$_SESSION['user_id']}");
                $this->response->json(['success' => 'Файл удалён']);
            } else {
                $this->logger->error("Не удалось удалить файл: {$filePath}");
                $this->response->json(['error' => 'Ошибка удаления файла'], 500);
            }
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при удалении файла: " . $e->getMessage());
            return $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // переименование файла
    public function renameFile()
    {
        $this->checkAuth();

        $data = $this->request->getBody();
        if (empty($data['id']) || empty($data['new_name'])) {
            $this->logger->warning("Не указаны данные для переименования файла. ID: {$data['id']}, новое имя: {$data['new_name']}");
            $this->response->json(['error' => 'Не указаны данные'], 400);
        }
        $result = $this->fileModel->rename($data['id'], $data['new_name']);
        if ($result) {
            $this->logger->info("Файл с ID {$data['id']} был переименован в {$data['new_name']}");
            $this->response->json(['success' => 'Файл переименован']);
        } else {
            $this->logger->error("Ошибка при переименовании файла с ID {$data['id']} в {$data['new_name']}");
            $this->response->json(['error' => 'Ошибка переименования'], 500);
        }
    }

    // скачивание файла
    public function downloadFile($id)
    {
        try {
            $this->checkAuth();

            $file = $this->fileModel->getFileById($id, $_SESSION['user_id']);
            if (!$file) {
                $this->logger->warning("Файл с ID {$id} не найден в базе данных для пользователя {$_SESSION['user_id']}");
                $this->response->json(['error' => 'Файл не найден'], 404);
            }
            $filePath = __DIR__ . '/../../uploads/' . basename($file['file_path']);
            if (!file_exists($filePath)) {
                $this->logger->warning("Файл с ID {$id} не найден на сервере. Путь: {$filePath}");
                $this->response->json(['error' => 'Файл отсутствует на сервере'], 404);
            }
            $finfo    = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            $inlineTypes = [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'text/plain',
                'text/html'
            ];
            $contentDisposition = in_array($mimeType, $inlineTypes) ? 'inline' : 'attachment';
            $this->logger->info("Запрос на скачивание файла с ID {$id}. MIME тип: {$mimeType}");

            header('Content-Description: File Transfer');
            header('Content-Type: ' . $mimeType);
            header("Content-Disposition: {$contentDisposition}; filename=\"" . basename($file['original_name']) . "\"");
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);

            $this->logger->info("Файл с ID {$id} успешно скачан пользователем {$_SESSION['user_id']}");

            exit;
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при скачивании файла: " . $e->getMessage());
            $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // получить список пользователей с доступом к файлу
    public function getSharedUsers($fileId)
    {
        try {
            $this->checkAuth();
        
            $users = $this->fileModel->getFileAccess($fileId);
            if (empty($users)) {
                $this->logger->info("Нет пользователей с доступом к файлу с ID: {$fileId}");
                $this->response->json(['message' => 'Нет пользователей с доступом к файлу'], 404);
                return;
            }

            $this->response->json($users);            
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при получении пользователей с доступом к файлу с ID {$fileId}: " . $e->getMessage());
            $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // предоставить доступ к файлу
    public function shareFile($fileId, $userId)
    {
        try {
            $this->checkAuth();

            if ($this->fileModel->addFileAccess($fileId, $userId)) {
                $this->logger->info("Пользователю с ID {$userId} предоставлен доступ к файлу с ID {$fileId}");
                $this->response->json(['success' => 'Доступ предоставлен']);
            } else {
                $this->logger->error("Ошибка при предоставлении доступа пользователю с ID {$userId} к файлу с ID {$fileId}");
                $this->response->json(['error' => 'Ошибка предоставления доступа'], 500);
            }
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при предоставлении доступа к файлу с ID {$fileId} пользователю с ID {$userId}: " . $e->getMessage());
            $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // удалить доступ к файлу пользователю
    public function unshareFile($fileId, $userId)
    {
        try {
            $this->checkAuth();

            if ($this->fileModel->removeFileAccess($fileId, $userId)) {
                $this->logger->info("Доступ к файлу с ID {$fileId} удалён у пользователя с ID {$userId}");
                $this->response->json(['success' => 'Доступ удалён']);
            } else {
                $this->logger->error("Ошибка при удалении доступа к файлу с ID {$fileId} у пользователя с ID {$userId}");
                $this->response->json(['error' => 'Ошибка'], 500);
            }   
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при удалении доступа к файлу с ID {$fileId} у пользователя с ID {$userId}: " . $e->getMessage());
            $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }
}