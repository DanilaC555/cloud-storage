<?php
namespace Core;

class Response
{
    // отправляет JSON-ответ клиенту
    public function json($data, int $status = 200)
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    // // отправляет простой текстовый или html контент
    // public function send(string $content, int $status = 200)
    // {
    //     http_response_code($status);
    //     echo $content;
    //     exit;
    // }
}