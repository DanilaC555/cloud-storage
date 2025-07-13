<?php
namespace App;

use Dotenv\Dotenv;
use Core\Database;
use Core\Router;
use App\Controllers\HomeController;
use App\Controllers\UserController;
use App\Controllers\AdminController;
use App\Controllers\FileController;
use App\Models\User;
use App\Models\File;

class App
{
    public static function run()
    {
        // загрузка переменных из файла .env
        $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
        $dotenv->load();

        session_start(); // Запускаем сессию

        // подключение к бд
        $db = new Database(
            $_ENV['DB_HOST'],
            $_ENV['DB_USER'],
            $_ENV['DB_PASSWORD'],
            $_ENV['DB_NAME']
        );

        // инициализируем роутер
        $router = new Router();

        // создаем объекты моделей
        $userModel = new User($db);
        $fileModel = new File($db);

        // создаем контроллеры и передаем им зависимости (модели)
        $userController  = new UserController($userModel);
        $adminController = new AdminController($userModel);
        $fileController  = new FileController($fileModel);
        $homeController  = new HomeController();

        // регистрация маршрутов:
        
        // главная страница
        $router->addRoute('GET', '/', [$homeController, 'index']);

        // пользовательские
        $router->addRoute('POST', '/register', [$userController, 'register']);
        $router->addRoute('POST', '/login', [$userController, 'login']);
        $router->addRoute('GET', '/logout', [$userController, 'logout']);
        $router->addRoute('GET', '/users/list', [$userController, 'listUsers']);
        $router->addRoute('GET', '/user/me', [$userController, 'getCurrentUser']);
        $router->addRoute('GET', '/user/search/(.*)', function($email) use ($userController) {
            $userController->searchUser($email);
        });

        // страницы ренедеринг
        $router->addRoute('GET', '/', [$homeController, 'showIndexPage']);
        $router->addRoute('GET', '/login', [$userController, 'showLoginPage']);
        $router->addRoute('GET', '/dashboard', [$userController, 'showDashboardPage']);
        $router->addRoute('GET', '/register', [$userController, 'showRegisterUserPage']);
        $router->addRoute('GET', '/adminPanel', [$adminController, 'showAdminPanelPage']);

        // для администратора
        $router->addRoute('GET', '/admin/users/list', [$adminController, 'listUsers']);
        $router->addRoute('GET', '/admin/users/get/{id}', function($id) use ($adminController) {
            $adminController->getUser($id);
        });
        $router->addRoute('PUT', '/admin/users/update/{id}', function($id) use ($adminController) {
            $adminController->updateUser($id);
        });
        $router->addRoute('DELETE', '/admin/users/delete/{id}', function($id) use ($adminController) {
            $adminController->deleteUser($id);
        });

        // для работы с файлами
        $router->addRoute('GET', '/files/list', [$fileController, 'listFiles']);
        $router->addRoute('GET', '/files/get/{id}', function($id) use ($fileController) {
            $fileController->getFile($id);
        });
        $router->addRoute('POST', '/files/add', [$fileController, 'addFile']);
        $router->addRoute('DELETE', '/files/remove/{id}', function($id) use ($fileController) {
            $fileController->removeFile($id);
        });
        $router->addRoute('PUT', '/files/rename', [$fileController, 'renameFile']);

        // для управления доступом к файлам
        $router->addRoute('GET', '/files/share/{id}', function($id) use ($fileController) {
            $fileController->getSharedUsers($id);
        });
        $router->addRoute('PUT', '/files/share/{id}/{user_id}', function($id, $user_id) use ($fileController) {
            $fileController->shareFile($id, $user_id);
        });
        $router->addRoute('DELETE', '/files/share/{id}/{user_id}', function($id, $user_id) use ($fileController) {
            $fileController->unshareFile($id, $user_id);
        });

        // для скачивания файла
        $router->addRoute('GET', '/files/download/{id}', function($id) use ($fileController) {
            $fileController->downloadFile($id);
        });

        // запуск обработки маршрутов
        $router->processRequest();
    }
}