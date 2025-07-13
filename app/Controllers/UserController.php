<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\User;
use Core\Logger;

class UserController extends Controller
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
    public function showLoginPage()
    {
        $this->render('loginAuth');
    }

    public function showDashboardPage()
    {
        $this->render('dashboard');
    }

    public function showRegisterUserPage()
    {
        $this->render('registerUser');
    }

    // регистрирует нового пользователя
    public function register()
    {
        try {
            $data = $this->request->getBody();
            if (empty($data['email']) || empty($data['password'])) {
                $this->logger->warning("Регистрация — не все поля заполнены");
                $this->response->json(['error' => 'Заполните все поля'], 400);
            }
            
            $result = $this->userModel->create($data['email'], $data['password']);
            if ($result) {
                // получаем данные только что созданного пользователя
                $user = $this->userModel->getUserByEmail($data['email']);
                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    setcookie("session_id", session_id(), time() + 3600, "/", "", false, true);

                    $this->logger->info("Пользователь зарегистрирован: {$data['email']}");
                    $this->response->json(['success' => 'Пользователь зарегистрирован и вошел в систему']);
                } else {
                    $this->logger->error("Не удалось получить данные пользователя после регистрации");
                    $this->response->json(['error' => 'Ошибка получения данных пользователя'], 500);
                }
            } else {
                $this->logger->error("Ошибка при создании пользователя: {$data['email']}");
                $this->response->json(['error' => 'Ошибка регистрации пользователя'], 500);
            }
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при регистрации: " . $e->getMessage());
            return $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // авторизует пользователя
    public function login()
    {
        try {
            $data = $this->request->getBody();
            if (empty($data['email']) || empty($data['password'])) {
                $this->logger->warning("Авторизация — не все поля заполнены");
                $this->response->json(['error' => 'Заполните все поля'], 400);
            }
            // получаем данные пользователя
            $user = $this->userModel->getUserByEmail($data['email']);
            if (!$user || !password_verify($data['password'], $user['password'])) {
                $this->logger->error("Ошибка получения данных пользователя");
                $this->response->json(['error' => 'Неверный email или пароль'], 401);
            }
        
            $_SESSION['user_id'] = $user['id'];
            setcookie("session_id", session_id(), time() + 3600, "/", "", false, true);

            $this->logger->info("Вход выполнен");
            $this->response->json(['success' => 'Вход выполнен']);
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при авторизации: " . $e->getMessage());
            return $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // завершает сессию пользователя
    public function logout()
    {
        try {
            session_destroy();
            setcookie("session_id", "", time() - 3600, "/");
            $this->logger->info("Выход выполнен");
            $this->response->json(['success' => 'Выход выполнен']);
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при выходе: " . $e->getMessage());
            return $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // возвращает список всех пользователей
    public function listUsers()
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                $this->logger->warning("Доступ запрещен");
                $this->response->json(['error' => 'Доступ запрещен'], 403);
            }
            $users = $this->userModel->findAll();
            $this->logger->info("Список пользователей успешно получен. Количество: " . count($users));
            $this->response->json($users);
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка данных: " . $e->getMessage());
            return $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // возвращает данные конкретного пользователя по id
    public function getUser($id)
    {
        try {
            $user = $this->userModel->findById($id);
            if ($user) {
                $this->logger->info("Получены данные пользователя с ID: {$id}");
                $this->response->json($user);
            } else {
                $this->logger->warning("Пользователь с ID {$id} не найден");
                $this->response->json(['error' => 'Пользователь не найден'], 404);
            }
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка получения пользователя с ID {$id}: " . $e->getMessage());
            return $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // возвращает данные текущего авторизованного пользователя
    public function getCurrentUser()
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                $this->logger->warning("Необходима авторизация");
                $this->response->json(['error' => 'Необходима авторизация'], 401);
            }
            $user = $this->userModel->findById($_SESSION['user_id']);
            
            if (!$user) {
                $this->logger->warning("Пользователь не найден");
                $this->response->json(['error' => 'Пользователь не найден'], 404);
            }
            
            $this->logger->info("Текущий пользователь получен: ID {$user['id']}, email {$user['email']}");
            $this->response->json([
                'id'    => $user['id'],
                'email' => $user['email'],
                'role'  => $user['role']
            ]);
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при получении текущего пользователя: " . $e->getMessage());
            return $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }

    // обновляет профиль текущего пользователя
    public function updateUser()
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                $this->logger->warning("Доступ запрещен");
                $this->response->json(['error' => 'Доступ запрещен'], 403);
            }
    
            $data = $this->request->getBody();
            $result = $this->userModel->updateUser($_SESSION['user_id'], $data['email'] ?? null, $data['password'] ?? null);
            $message = $result ? 'Профиль обновлён' : 'Ошибка обновления';
            
            $this->response->json(['success' => $message]);
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при обновлении профиля: " . $e->getMessage());
            return $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        } 
    }

    // поиск пользователя по email
    public function searchUser($email)
    {
        try {
            $email = urldecode($email); // декодируем email из URL

            if (!isset($_SESSION['user_id'])) {
                $this->logger->warning("Необходима авторизация");
                $this->response->json(['error' => 'Необходима авторизация'], 401);
            }
    
            $user = $this->userModel->getUserByEmail($email);
            if (!$user) {
                $this->logger->info("Поиск пользователя: {$email} — не найден");
                $this->response->json(['error' => 'Пользователь не найден'], 404);
            }
    
            $this->logger->info("Пользователь найден по email: {$email}");
            $this->response->json([
                'id'    => $user['id'],
                'email' => $user['email']
            ]);
        } catch (\Throwable $e) {
            $this->logger->error("Ошибка при поиске пользователя: " . $e->getMessage());
            return $this->response->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }
    }
}