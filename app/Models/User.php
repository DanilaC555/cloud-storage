<?php
namespace App\Models;

use Core\Model;
use Exception;

class User extends Model
{
    protected $table = 'users'; // имя таблицы для модели пользователей
    
    // создать нового пользователя
    public function create($email, $password, $role = 'user')
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        return $this->insert([
            'email'    => $email,
            'password' => $hashedPassword,
            'role'     => $role
        ]);
    }

    // обновление пользователя
    public function updateUser($id, $email, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        return $this->update($id, ['email' => $email, 'password' => $hashedPassword]);
    }

    // Получение пользователя по email
    public function getUserByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }
}