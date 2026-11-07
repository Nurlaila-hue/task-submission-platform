<?php
session_start();

function login($username, $password) {
    // Assuming a database connection is established
    include_once '../config/database.php';
    global $conn;

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role']; // 'student' or 'lecturer'
            return true;
        }
    }
    return false;
}

function register($username, $password, $role) {
    include_once '../config/database.php';
    global $conn;

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashed_password, $role);
    return $stmt->execute();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function logout() {
    session_destroy();
    header("Location: ../public/index.php");
    exit();
}

function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}
?>