<?php
namespace App\Models;

use Core\Model;
use Exception;

class File extends Model
{
    protected $table = 'files'; // имя таблицы для модели файлы

    // сохранение нового файла
    public function create($userId, $originalName, $encryptedName, $filePath)
    {
        return $this->insert([
            'user_id'       => $userId,
            'original_name' => $originalName,
            'encrypted_name'=> $encryptedName,
            'file_path'     => $filePath,
        ]);
    }

    // переименование файла
    public function rename($id, $newName)
    {
        return $this->update($id, ['original_name' => $newName]);
    }

    // удаление файла по идентификатору
    public function remove($id)
    {
        return $this->delete($id);
    } 

    // возвращает список файлов для конкретного пользователя (с учетом доступа)
    public function getFilesByUser($userId)
    {
         // если файл был расшарен (то есть в таблице file_access появилась запись с его id и id получателя), то он будет включён в выборку и отображён в личном кабинете пользователя, которому предоставлен доступ
        $sql = "SELECT DISTINCT files.id, files.original_name, files.file_path
                FROM {$this->table} AS files
                LEFT JOIN file_access ON files.id = file_access.file_id
                WHERE files.user_id = ? OR file_access.user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

   // получение информации о конкретном файле
    public function getFileById($id, $userId)
    {
        // Проверяем, владелец ли пользователь или у него есть доступ через file_access
        $sql = "SELECT files.* FROM {$this->table} AS files
                LEFT JOIN file_access ON files.id = file_access.file_id
                WHERE files.id = ? AND (files.user_id = ? OR file_access.user_id = ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iii", $id, $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    // получить список пользователей, имеющих доступ к файлу
    public function getFileAccess($fileId)
    {
        $sql = "SELECT users.id, users.email FROM file_access 
                JOIN users ON file_access.user_id = users.id 
                WHERE file_access.file_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $fileId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    // добавить доступ к файлу пользователю
    public function addFileAccess($fileId, $userId)
    {
        // сначала проверяем, существует ли файл
        $sql = "SELECT id FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $fileId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$result) {
            return false;
        }
    
        // добавляем доступ
        $sql = "INSERT INTO file_access (file_id, user_id) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $fileId, $userId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // прекратить доступ к файлу пользователю
    public function removeFileAccess($fileId, $userId)
    {
        $sql = "DELETE FROM file_access WHERE file_id = ? AND user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $fileId, $userId);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
