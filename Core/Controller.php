<?php
namespace Core;

abstract class Controller
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    // инициализирует объекты для работы с запросом и ответом
    public function __construct()
    {
        $this->request = new Request();
        $this->response = new Response();
    }

    // рендер
    protected function render($view, $params = [])
    {
        extract($params); // превращает массив в переменные
        $viewPath = __DIR__ . '/../App/Views/' . $view . '.php';
        
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            $this->response->json(['error' => 'Представление не найдено'], 404);
        }
    }
}