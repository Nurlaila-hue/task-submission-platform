<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['user_role'] === 'lecturer') {
    header("Location: lecturer_dashboard.php");
    exit();
} elseif ($_SESSION['user_role'] === 'student') {
    header("Location: student_dashboard.php");
    exit();
} else {
    // Jika role tidak dikenali, logout
    session_destroy();
    header("Location: login.php");
    exit();
}
?>