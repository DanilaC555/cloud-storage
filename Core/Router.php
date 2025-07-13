<?php
namespace Core;

class Router
{
    private $routes = [];

    public function addRoute($method, $url, $callback)
    {
        // если маршрут - "/", оставляем его без изменений
        $cleanUrl = ($url === '/') ? '/' : rtrim($url, '/');

        $this->routes[$method][$cleanUrl] = $callback;
    }

    public function processRequest()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestUrl = rtrim($requestUrl, '/');
    
        // если URL пустой, явно задаем "/"
        if ($requestUrl === '' || $requestUrl === false) {
            $requestUrl = '/';
        }
    
        // проверяем, есть ли точное совпадение (например, "/")
        if (isset($this->routes[$requestMethod][$requestUrl])) {
            call_user_func($this->routes[$requestMethod][$requestUrl]);
            return;
        }
    
        // если не нашли точного совпадения, проверяем параметры
        foreach ($this->routes[$requestMethod] as $route => $callback) {
            // заменяем {id} на регулярное выражение для чисел
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(\d+)', $route);
            if (preg_match("#^$pattern$#", $requestUrl, $matches)) {
                array_shift($matches); // убираем первый элемент (полный URL)
                call_user_func_array($callback, $matches);
                return;
            }
        }
    
        // если ничего не нашли, отдаём 404
        if (strpos($requestUrl, '/public/') === 0) {
            http_response_code(404);
            echo '<h1>404 - Page not found</h1>';
            return;
        }
    }   
}