<?php
session_start();

require_once '../src/config/database.php';
$db = (new Database())->getConnection();

function redirectWithClassFeedback($message, $status = 'error')
{
    $_SESSION['class_join_feedback'] = [
        'message' => $message,
        'status' => $status,
    ];

    header('Location: student_dashboard.php');
    exit();
}

$studentId = $_SESSION['user_id'] ?? null;

if (!$studentId || ($_SESSION['user_role'] ?? null) !== 'student') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectWithClassFeedback('Permintaan tidak valid.');
}

$classCode = isset($_POST['class_code']) && is_string($_POST['class_code'])
    ? trim($_POST['class_code'])
    : '';

if ($classCode === '') {
    redirectWithClassFeedback('Masukkan kode kelas terlebih dahulu.');
}

$stmt = $db->prepare('SELECT id FROM classes WHERE class_code = :class_code');
$stmt->execute([':class_code' => $classCode]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    redirectWithClassFeedback('Kode kelas tidak ditemukan.');
}

$stmt = $db->prepare(
    'SELECT 1
     FROM class_members
     WHERE class_id = :class_id AND student_id = :student_id'
);
$stmt->execute([
    ':class_id' => $class['id'],
    ':student_id' => $studentId,
]);

if ($stmt->fetch()) {
    redirectWithClassFeedback('Anda sudah bergabung ke kelas ini.', 'info');
}

$stmt = $db->prepare(
    'INSERT INTO class_members (class_id, student_id)
     VALUES (:class_id, :student_id)'
);
$stmt->execute([
    ':class_id' => $class['id'],
    ':student_id' => $studentId,
]);

redirectWithClassFeedback('Berhasil bergabung ke kelas!', 'success');