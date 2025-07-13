<?php
namespace Core;

class Database
{
    public $conn;

    // принимает параметры подключения к БД
    public function __construct($host, $user, $password, $database)
    {
        $this->conn = new \mysqli($host, $user, $password, $database);
        if ($this->conn->connect_error) {
            throw new \Exception("Ошибка подключения: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8mb4"); // кодировка UTF-8
    }
}
