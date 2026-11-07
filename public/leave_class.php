<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: login.php");
    exit();
}
require_once '../src/config/database.php';
$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class_id'])) {
    $class_id = $_POST['class_id'];
    $student_id = $_SESSION['user_id'];
    // Hapus keanggotaan siswa dari kelas
    $stmt = $db->prepare("DELETE FROM class_members WHERE class_id = :class_id AND student_id = :student_id");
    $stmt->bindParam(':class_id', $class_id);
    $stmt->bindParam(':student_id', $student_id);
    $stmt->execute();
}
header("Location: student_dashboard.php?menu=kelas");
exit();