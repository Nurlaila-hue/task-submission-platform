<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
    header("Location: login.php");
    exit();
}
require_once '../src/config/database.php';
$db = (new Database())->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class_id'])) {
    $class_id = $_POST['class_id'];
    // Hapus seluruh data terkait kelas (tugas, submissions, keanggotaan, lalu kelas)
    $stmt = $db->prepare("DELETE FROM submissions WHERE task_id IN (SELECT id FROM tasks WHERE class_id = :class_id)");
    $stmt->bindParam(':class_id', $class_id);
    $stmt->execute();

    $stmt = $db->prepare("DELETE FROM tasks WHERE class_id = :class_id");
    $stmt->bindParam(':class_id', $class_id);
    $stmt->execute();

    $stmt = $db->prepare("DELETE FROM class_members WHERE class_id = :class_id");
    $stmt->bindParam(':class_id', $class_id);
    $stmt->execute();

    $stmt = $db->prepare("DELETE FROM classes WHERE id = :class_id");
    $stmt->bindParam(':class_id', $class_id);
    $stmt->execute();
}
header("Location: lecturer_dashboard.php?menu=kelas");
exit();