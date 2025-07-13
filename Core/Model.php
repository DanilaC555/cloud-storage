<?php

namespace Core;

use Exception;
use Core\Database;

abstract class Model
{
    protected $db; // подключение к бд
    protected $table; // название таблтцы 
    protected $primaryKey = 'id'; // id имя столбца

    // подключение к бд
    public function __construct(Database $db) 
    {
        $this->db = $db->conn; // конструктор связывает объект базы данных с моделью
    }

    // получить запись по идентификатору
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Ошибка подготовки запроса: " . $this->db->error);
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    // получить все записи из таблицы (пользователи)
    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table}";
        $result = $this->db->query($sql);
        if (!$result) {
            throw new Exception("Ошибка запроса: " . $this->db->error);
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    // вставка новой записи
    public function insert(array $data)
    {
        // формирование списка колонок и плейсхолдеров
        $columns = implode(", ", array_keys($data));
        $placeholders = implode(", ", array_fill(0, count($data), "?"));
        $sql = "INSERT INTO {$this->table} ($columns) VALUES ($placeholders)";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Ошибка подготовки запроса: " . $this->db->error);
        }
        // для упрощения считаем, что все значения – строки
        $types = str_repeat("s", count($data));
        $values = array_values($data);
        $stmt->bind_param($types, ...$values);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // обновление записи по идентификатору
    public function update($id, array $data)
    {
        $setString = "";
        $params = [];
        $types = "";

        foreach ($data as $column => $value) {
            $setString .= "$column = ?, ";
            $params[] = $value;
            $types .= "s";
        }
        $setString = rtrim($setString, ", ");
        $sql = "UPDATE {$this->table} SET $setString WHERE {$this->primaryKey} = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Ошибка подготовки запроса: " . $this->db->error);
        }
        // тип и значение для идентификатора
        $types .= "i";
        $params[] = $id;

        $stmt->bind_param($types, ...$params);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // удаление записи по идентификатору для файлов
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Ошибка подготовки запроса: " . $this->db->error);
        }
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}