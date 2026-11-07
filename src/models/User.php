<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $username;
    public $password;
    public $role;
    public $email;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Cari user berdasarkan username ATAU email (untuk register & forgot password)
    public function findByUsernameOrEmail($username, $email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username OR email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cari user berdasarkan username saja (untuk login)
    public function findByUsername($username) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Register user baru (dengan email)
    public function register() {
        $query = "INSERT INTO " . $this->table_name . " (username, password, role, email) VALUES (:username, :password, :role, :email)";
        $stmt = $this->conn->prepare($query);

        $username = htmlspecialchars(strip_tags($this->username));
        $password = password_hash($this->password, PASSWORD_BCRYPT);
        $role = htmlspecialchars(strip_tags($this->role));
        $email = htmlspecialchars(strip_tags($this->email));

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':email', $email);

        return $stmt->execute();
    }

    // Update profile (username & password)
    public function updateProfile($new_username, $new_password) {
        $query = "UPDATE " . $this->table_name . " SET username = :username, password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $new_username = htmlspecialchars(strip_tags($new_username));
        $new_password = password_hash($new_password, PASSWORD_BCRYPT);

        $stmt->bindParam(':username', $new_username);
        $stmt->bindParam(':password', $new_password);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    // Update password saja (untuk forgot password)
    public function updatePassword($id, $new_password) {
        $query = "UPDATE " . $this->table_name . " SET password = :password WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt->bindParam(':password', $hashed);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}