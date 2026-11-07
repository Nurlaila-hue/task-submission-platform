<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
    header("Location: login.php");
    exit();
}
require_once '../src/config/database.php';
$db = (new Database())->getConnection();

$class_id = $_GET['class_id'] ?? null;
$errorMsg = '';
$successMsg = '';

if (!$class_id) {
    $errorMsg = "Kelas tidak ditemukan.";
}

// Proses submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$errorMsg) {
    $task_title = $_POST['task_title'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $deadline = $_POST['deadline'] ?? '';

    if (!$task_title || !$deadline) {
        $errorMsg = "Judul tugas dan deadline wajib diisi.";
    } else {
        $stmt = $db->prepare("INSERT INTO tasks (class_id, task_title, notes, deadline, created_at) VALUES (:class_id, :task_title, :notes, :deadline, NOW())");
        $stmt->bindParam(':class_id', $class_id);
        $stmt->bindParam(':task_title', $task_title);
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':deadline', $deadline);
        $stmt->execute();
        $successMsg = "Tugas baru berhasil dibuat!";
    }
}

// Ambil data user untuk sidebar
$stmt = $db->prepare("SELECT username, profile_pic FROM users WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$profile_pic = $user['profile_pic'] ?? 'assets/default-avatar.png';
$lecturerName = $user['username'] ?? 'Dosen';
$menu = 'kelas';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Tugas Baru</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            background: linear-gradient(120deg, #4f8cff 0%, #a6d1ff 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
            padding: 48px 0;
            background: #f4f6fa;
            border-radius: 24px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .form-card {
            max-width: 480px;
            width: 100%;
            background: #fff;
            border-radius: 32px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.13);
            padding: 38px 36px 36px 36px;
            margin-top: 40px;
        }
        h2 {
            font-size: 2rem;
            font-weight: bold;
            color: #23235b;
            margin-bottom: 28px;
            text-align: center;
        }
        label {
            display: block;
            font-size: 1.1rem;
            font-weight: 500;
            color: #23235b;
            margin-bottom: 8px;
        }
        input[type="text"], input[type="datetime-local"], textarea {
            width: 100%;
            border-radius: 12px;
            border: 1.5px solid #bcd;
            font-size: 1.08rem;
            padding: 12px 14px;
            margin-bottom: 18px;
            margin-top: 6px;
            resize: vertical;
            box-sizing: border-box;
        }
        textarea {
            min-height: 70px;
        }
        .submit-btn {
            display: block;
            background: #4f8cff;
            color: #fff;
            font-weight: bold;
            border: none;
            border-radius: 14px;
            padding: 14px 0;
            width: 100%;
            font-size: 1.15rem;
            margin-bottom: 18px;
            margin-top: 6px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .submit-btn:hover {
            background: #23235b;
        }
        .back-link {
            display: inline-block;
            margin-top: 8px;
            color: #4f8cff;
            font-size: 1.1rem;
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: #23235b;
            text-decoration: underline;
        }
        .success-msg {
            background: #d4edda;
            color: #155724;
            border-radius: 10px;
            padding: 16px 0;
            margin-bottom: 18px;
            font-size: 1.1rem;
            font-weight: bold;
            text-align: center;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            border-radius: 10px;
            padding: 16px 0;
            margin-bottom: 18px;
            font-size: 1.1rem;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <?php include 'sidebar_dosen.php'; ?>
    <div class="main-content">
        <div class="form-card">
            <h2>Buat Tugas Baru</h2>
            <?php if ($errorMsg): ?>
                <div class="error-msg"><?php echo htmlspecialchars($errorMsg); ?></div>
                <a href="javascript:history.back()" class="back-link">&#8592; Kembali</a>
            <?php elseif ($successMsg): ?>
                <div class="success-msg"><?php echo htmlspecialchars($successMsg); ?></div>
                <a href="list_task.php?class_id=<?php echo urlencode($class_id); ?>" class="back-link">&#8592; Kembali ke Daftar Tugas</a>
            <?php else: ?>
                <form method="post">
                    <label for="task_title">Judul Tugas</label>
                    <input type="text" name="task_title" id="task_title" required placeholder="Contoh: Membuat Website">
                    <label for="notes">Catatan / Deskripsi Tugas</label>
                    <textarea name="notes" id="notes" placeholder="Deskripsi tugas (opsional)"></textarea>
                    <label for="deadline">Deadline</label>
                    <input type="datetime-local" name="deadline" id="deadline" required>
                    <button type="submit" class="submit-btn">Buat Tugas</button>
                </form>
                <a href="list_task.php?class_id=<?php echo urlencode($class_id); ?>" class="back-link">&#8592; Kembali ke Daftar Tugas</a>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>