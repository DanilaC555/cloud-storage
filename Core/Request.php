<?php
namespace Core;

class Request
{
    // возвращает HTTP-метод запроса
    public function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    // возвращает очищенный путь запроса
    public function getPath(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return rtrim($uri, '/') ?: '/';
    }

    // получает данные запроса GET POST/PUT/DELETE
    public function getBody(): array
    {
        $body = [];
        if ($this->getMethod() === 'GET') {
            $body = $_GET;
        } elseif (in_array($this->getMethod(), ['POST', 'PUT', 'DELETE'])) {
            // предпочтение отдаётся json-данным, если они присутствуют
            $rawInput = file_get_contents('php://input');
            $decoded = json_decode($rawInput, true);
            $body = $decoded ? $decoded : $_POST;
        }
        return $body;
    }

    // возвращает данные загруженных файлов
    public function getFiles(): array
    {
        return $_FILES;
    }
}
