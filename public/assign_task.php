<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
    header("Location: login.php");
    exit();
}

require_once '../src/config/database.php';

$class_id = $_GET['class_id'] ?? null;
if (!$class_id) {
    echo "Kelas tidak ditemukan.";
    exit();
}

// Ambil info kelas
$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT * FROM classes WHERE id = :id");
$stmt->bindParam(':id', $class_id);
$stmt->execute();
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    echo "Kelas tidak ditemukan.";
    exit();
}

// Proses pemberian tugas
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $task_title = trim($_POST['task_title'] ?? '');
    $deadline_date = trim($_POST['deadline_date'] ?? '');
    $deadline_time = trim($_POST['deadline_time'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    $deadline = $deadline_date . ' ' . $deadline_time . ':00';

    if ($task_title && $deadline_date && $deadline_time) {
        $stmt = $db->prepare("INSERT INTO tasks (class_id, task_title, deadline, notes) VALUES (:class_id, :task_title, :deadline, :notes)");
        $stmt->bindParam(':class_id', $class_id);
        $stmt->bindParam(':task_title', $task_title);
        $stmt->bindParam(':deadline', $deadline);
        $stmt->bindParam(':notes', $notes);
        if ($stmt->execute()) {
            $message = '<div class="alert success">Tugas berhasil diberikan.</div>';
        } else {
            $message = '<div class="alert error">Gagal memberikan tugas.</div>';
        }
    } else {
        $message = '<div class="alert error">Semua field harus diisi.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pemberian Tugas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { background: #f4f6f8; font-family: 'Segoe UI', Arial, sans-serif; margin: 0; }
        .container { max-width: 400px; margin: 60px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.07); padding: 32px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 7px; color: #37474f; font-weight: 500; }
        input[type="text"], input[type="date"], input[type="time"], textarea { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 15px; background: #f9fafb; }
        textarea { resize: vertical; min-height: 60px; }
        .btn { width: 100%; padding: 12px; background: #4f8cff; color: #fff; border: none; border-radius: 6px; font-size: 16px; font-weight: 600; cursor: pointer; margin-top: 10px; transition: background 0.2s; }
        .btn:hover { background: #2d3e46; }
        .alert { padding: 12px; margin-bottom: 18px; border-radius: 6px; font-size: 15px; text-align: center; }
        .alert.success { background: #d1e7dd; color: #0f5132; }
        .alert.error { background: #f8d7da; color: #842029; }
        .back-link { display: block; margin-bottom: 18px; color: #4f8cff; text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <a href="list_class.php" class="back-link">&larr; Kembali ke Daftar Kelas</a>
        <h2>Pemberian Tugas untuk Kelas:<br><?php echo htmlspecialchars($class['class_name']); ?></h2>
        <?php echo $message; ?>
        <form method="POST" autocomplete="off">
            <div class="form-group">
                <label for="task_title">Judul Tugas:</label>
                <input type="text" id="task_title" name="task_title" required>
            </div>
            <div class="form-group">
                <label for="deadline_date">Deadline (Tanggal):</label>
                <input type="date" id="deadline_date" name="deadline_date" required>
            </div>
            <div class="form-group">
                <label for="deadline_time">Deadline (Jam):</label>
                <input type="time" id="deadline_time" name="deadline_time" required>
            </div>
            <div class="form-group">
                <label for="notes">Catatan:</label>
                <textarea id="notes" name="notes" placeholder="Catatan untuk tugas (opsional)"></textarea>
            </div>
            <button type="submit" class="btn">Beri Tugas</button>
        </form>
    </div>
</body>
</html>