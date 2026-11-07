<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
    header("Location: login.php");
    exit();
}
require_once '../src/config/database.php';
$db = (new Database())->getConnection();

$task_id = $_GET['task_id'] ?? null;
if (!$task_id) {
    die("Task ID tidak ditemukan.");
}

// Ambil data user untuk sidebar
$stmt = $db->prepare("SELECT username, profile_pic FROM users WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$profile_pic = $user['profile_pic'] ?? 'assets/default-avatar.png';
$lecturerName = $user['username'] ?? 'Dosen';
$menu = 'kelas';

// Ambil data tugas
$stmt = $db->prepare("SELECT * FROM tasks WHERE id = :id");
$stmt->bindParam(':id', $task_id);
$stmt->execute();
$task = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil data pengumpulan
$stmt = $db->prepare("SELECT s.username, sub.submitted_at, sub.file_path 
    FROM submissions sub 
    JOIN users s ON sub.student_id = s.id 
    WHERE sub.task_id = :task_id 
    ORDER BY sub.submitted_at ASC");
$stmt->bindParam(':task_id', $task_id);
$stmt->execute();
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengumpulan Tugas</title>
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
        .submission-card {
            max-width: 700px;
            width: 100%;
            background: #fff;
            border-radius: 40px;
            box-shadow: 0 8px 32px #4f8cff13;
            padding: 48px 32px 32px 32px;
            margin-top: 40px;
            text-align: center;
        }
        .submission-title {
            font-size: 2rem;
            font-weight: bold;
            color: #23235b;
            margin-bottom: 32px;
        }
        .submission-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            margin-bottom: 24px;
        }
        .submission-table th, .submission-table td {
            padding: 16px 12px;
            font-size: 1.08rem;
        }
        .submission-table th {
            background: #23235b;
            color: #fff;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 16px 16px 0 0;
            border: none;
        }
        .submission-table td {
            background: #f4f6fa;
            color: #23235b;
            border-radius: 0 0 16px 16px;
            border-bottom: 1px solid #e0e7ff;
            text-align: center;
        }
        .submission-table tr {
            box-shadow: 0 2px 8px #4f8cff11;
            transition: box-shadow 0.2s;
        }
        .submission-table tr:hover td {
            background: #eaf3ff;
        }
        .download-link {
            color: #4f8cff;
            font-weight: bold;
            text-decoration: none;
            font-size: 1.08rem;
            transition: color 0.2s;
            border-radius: 8px;
            padding: 6px 18px;
            background: #eaf3ff;
            display: inline-block;
        }
        .download-link:hover {
            background: #4f8cff;
            color: #fff;
            text-decoration: none;
        }
        .back-link {
            display: inline-block;
            margin-top: 24px;
            color: #4f8cff;
            font-size: 1.1rem;
            font-weight: bold;
            text-decoration: none;
            transition: color 0.2s;
        }
        .back-link:hover {
            color: #23235b;
            text-decoration: underline;
        }
        @media (max-width: 900px) {
            .submission-card { padding: 24px 4px; }
            .main-content { padding: 24px 0; }
            .submission-title { font-size: 1.3rem; }
            .submission-table th, .submission-table td { padding: 10px 6px; font-size: 0.98rem; }
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <?php include 'sidebar_dosen.php'; ?>
    <div class="main-content">
        <div class="submission-card">
            <div class="submission-title">
                Pengumpulan untuk Tugas: <span style="color:#4f8cff;"><?php echo htmlspecialchars($task['task_title'] ?? '-'); ?></span>
            </div>
            <table class="submission-table">
                <tr>
                    <th>Nama Siswa</th>
                    <th>Waktu Submit</th>
                    <th>File</th>
                </tr>
                <?php if (count($submissions) > 0): ?>
                    <?php foreach ($submissions as $sub): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sub['username']); ?></td>
                        <td><?php echo htmlspecialchars($sub['submitted_at']); ?></td>
                        <td>
                            <a href="<?php echo htmlspecialchars($sub['file_path']); ?>" class="download-link" target="_blank">Download</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="color:#888;">Belum ada pengumpulan tugas.</td>
                    </tr>
                <?php endif; ?>
            </table>
            <a href="list_task.php?class_id=<?php echo htmlspecialchars($task['class_id'] ?? ''); ?>" class="back-link">&#8592; Kembali ke Daftar Tugas</a>
        </div>
    </div>
</div>
</body>
</html>