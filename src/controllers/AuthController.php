<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../models/User.php');

class AuthController {
    private $db;
    private $userModel;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->userModel = new User($this->db);
    }

    // Getter untuk userModel agar bisa diakses dari luar class
    public function getUserModel() {
        return $this->userModel;
    }

    // Register user baru (dengan email)
    public function register($username, $password, $role, $email) {
        // Cek apakah username atau email sudah ada
        if ($this->userModel->findByUsernameOrEmail($username, $email)) {
            return 'exists';
        }
        $this->userModel->username = $username;
        $this->userModel->password = $password;
        $this->userModel->role = $role;
        $this->userModel->email = $email;

        if ($this->userModel->register()) {
            return true;
        } else {
            return false;
        }
    }

    // Login hanya dengan username dan password
    public function login($username, $password, $role = null, $email = null) {
    if (empty($password)) {
        return "Password is required.";
    }
    $user = $this->userModel->findByUsername($username);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        // Redirect ke index.php, bukan langsung ke dashboard
        header("Location: index.php");
        exit();
    } else {
        return "Invalid credentials.";
        }
    }

    // Logout user
    public function logout() {
        session_destroy();
        header("Location: ../public/login.php");
        exit();
    }
}