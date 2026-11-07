<?php
session_start();
// Perbaikan: cek role lecturer, bukan student
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
    header("Location: login.php");
    exit();
}
require_once '../src/config/database.php';
$db = (new Database())->getConnection();

// Ambil data user untuk sidebar
$stmt = $db->prepare("SELECT username, profile_pic FROM users WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$profile_pic = $user['profile_pic'] ?? 'assets/default-avatar.png';
$lecturerName = $user['username'] ?? 'Dosen';
$menu = 'kelas';

// Ambil class_id dari URL
$class_id = $_GET['class_id'] ?? null;
if (!$class_id) {
    $errorMsg = "Class ID tidak ditemukan.";
    $showTable = false;
} else {
    $showTable = true;
}

// Ambil daftar tugas berdasarkan class_id
$tasks = [];
if ($showTable) {
    $stmt = $db->prepare("SELECT * FROM tasks WHERE class_id = :class_id ORDER BY deadline ASC");
    $stmt->bindParam(':class_id', $class_id);
    $stmt->execute();
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Tugas</title>
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
        .container {
            max-width: 800px;
            width: 100%;
            background: #fff;
            border-radius: 32px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.13);
            padding: 36px 0 36px 0;
            margin-top: 40px;
        }
        .header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 0 48px 32px 48px;
            gap: 12px;
        }
        .header-row h2 {
            font-size: 2rem;
            font-weight: bold;
            color: #23235b;
            margin: 0;
        }
        .btn-group {
            display: flex;
            gap: 12px;
        }
        .back-btn, .buat-tugas-btn {
            background: #4f8cff;
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 10px 28px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 8px #4f8cff22;
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        .back-btn:hover, .buat-tugas-btn:hover {
            background: #23235b;
        }
        .task-table {
            width: 90%;
            margin: 0 auto;
            border-collapse: separate;
            border-spacing: 0 14px;
        }
        .task-table th, .task-table td {
            padding: 18px 18px;
            text-align: left;
            font-size: 1.08rem;
        }
        .task-table th {
            background: #23235b;
            color: #fff;
            font-size: 1.15rem;
            font-weight: bold;
            border-radius: 18px 18px 0 0;
            border: none;
        }
        .task-table td {
            background: #f4f6fa;
            color: #23235b;
            border-radius: 0 0 18px 18px;
            font-size: 1.08rem;
            border-bottom: 1px solid #e0e7ff;
        }
        .task-table tr {
            box-shadow: 0 2px 8px #4f8cff11;
            transition: box-shadow 0.2s;
        }
        .task-table tr:hover td {
            background: #eaf3ff;
        }
        .submit-link {
            color: #4f8cff;
            font-weight: bold;
            text-decoration: none;
            font-size: 1.08rem;
            transition: color 0.2s;
        }
        .submit-link:hover {
            color: #23235b;
            text-decoration: underline;
        }
        .error-msg {
            background: #f8d7da;
            color: #721c24;
            border-radius: 10px;
            padding: 16px 0;
            margin: 24px auto;
            font-size: 1.1rem;
            font-weight: bold;
            max-width: 500px;
            text-align: center;
        }
        @media (max-width: 900px) {
            .container { padding: 10px 0; }
            .main-content { padding: 24px 0; }
            .header-row { flex-direction: column; gap: 18px; margin: 0 10px 24px 10px; }
            .task-table th, .task-table td { padding: 10px 6px; font-size: 0.98rem; }
            .btn-group { flex-direction: column; gap: 8px; }
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <?php include 'sidebar_dosen.php'; ?>
    <div class="main-content">
        <div class="container">
            <div class="header-row">
                <h2>Daftar Tugas</h2>
                <div class="btn-group">
                    <a href="lecturer_dashboard.php?menu=kelas" class="back-btn">&#8592; Kembali</a>
                    <a href="submit_task.php?class_id=<?php echo urlencode($class_id); ?>" class="buat-tugas-btn">+ Buat Tugas Baru</a>
                </div>
            </div>
            <?php if (!$showTable): ?>
                <div class="error-msg"><?php echo htmlspecialchars($errorMsg); ?></div>
            <?php else: ?>
                <table class="task-table">
                    <tr>
                        <th>Nama Tugas</th>
                        <th>Deadline</th>
                        <th>Aksi</th>
                    </tr>
                    <?php if (count($tasks) > 0): ?>
                        <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($task['task_title']); ?></td>
                            <td><?php echo htmlspecialchars($task['deadline']); ?></td>
                            <td>
                                <a href="list_submission.php?task_id=<?php echo $task['id']; ?>" class="submit-link">Detail Tugas</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align:center;color:#888;">Belum ada tugas untuk kelas ini.</td>
                        </tr>
                    <?php endif; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>