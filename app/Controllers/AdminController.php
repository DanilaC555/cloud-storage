<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\User;
use Core\Logger;

class AdminController extends Controller
{
    private $userModel;
    private Logger $logger;

    public function __construct(User $userModel)
    {
        parent::__construct();
        $this->userModel = $userModel;
        $this->logger = new Logger();
    }

    // рендеринг:
    public function showAdminPanelPage()
    {
        $this->render('adminPanel');
    }

    // проверка является ли текущий пользователь администратором
    private function checkAdmin()
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                $this->logger->warning("Необходима авторизация");
                $this->response->json(['error' => 'Необходима авторизация'], 401);
                return;
            }
            $user = $this->userModel->findById($_SESSION['user_id']);
            if (!$user || $user['role'] !== 'admin') {
                $this->logger->warning("Доступ запрещён для пользователя с ID: " . $_SESSION['user_id']);
                $this->response->json(['error' => 'Доступ запрещён'], 403);
                return;
            }
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при проверке прав администратора: " . $e->getMessage());
            $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // Возвращает список всех пользователей.
    public function listUsers()
    {
        try {
            $this->checkAdmin();
            $users = $this->userModel->findAll();
            $this->response->json($users);
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при получении списка пользователей: " . $e->getMessage());
            $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // возвращает данные конкретного пользователя по id
    public function getUser($id)
    {
        try {
            $this->checkAdmin();

            $user = $this->userModel->findById($id);
            if ($user) {
                $this->response->json($user);
            } else {
                $this->response->json(['error' => 'Пользователь не найден'], 404);
            }
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при получении данных пользователя с ID $id: " . $e->getMessage());
            $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // обновляет данные пользователя
    public function updateUser($id)
    {
        try {
            $this->checkAdmin();

            $data = $this->request->getBody();
            $result = $this->userModel->updateUser($id, $data['email'] ?? null, $data['password'] ?? null);
            $message = $result ? 'Пользователь обновлён' : 'Ошибка обновления';
            $this->response->json(['success' => $message]);
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при обновлении данных пользователя с ID $id: " . $e->getMessage());
            $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // удаляет пользователя
    public function deleteUser($id)
    {
        try {
            $this->checkAdmin();

            $result = $this->userModel->delete($id);
            $message = $result ? 'Пользователь удалён' : 'Ошибка удаления';
            $this->response->json(['success' => $message]);
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при удалении пользователя с ID $id: " . $e->getMessage());
            $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }
}