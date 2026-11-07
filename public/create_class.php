<?php
session_start();
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

$message = "";
$class_code = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $class_name = trim($_POST['class_name'] ?? '');
    $class_code = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6);

    if ($class_name !== '') {
        $stmt = $db->prepare("INSERT INTO classes (class_name, lecturer_name, created_at, class_code) VALUES (:class_name, :lecturer_name, NOW(), :class_code)");
        $stmt->bindParam(':class_name', $class_name);
        $stmt->bindParam(':lecturer_name', $lecturerName);
        $stmt->bindParam(':class_code', $class_code);
        $stmt->execute();

        $message = "Kelas berhasil dibuat! Kode Kelas: <b class='code'>$class_code</b>";
    } else {
        $message = "<div class='error'>Nama kelas tidak boleh kosong.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buat Kelas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { background: linear-gradient(120deg, #4f8cff 0%, #a6d1ff 100%); font-family: 'Segoe UI', Arial, sans-serif; margin: 0; }
        .dashboard-container { display: flex; min-height: 100vh; }
        .main-content {
            flex: 1;
            padding: 48px 0;
            background: #f4f6fa;
            border-radius: 24px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            max-width: 600px;
            width: 100%;
            background: #fff;
            border-radius: 40px;
            box-shadow: 0 8px 32px #4f8cff13;
            padding: 48px 32px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .form-group {
            width: 100%;
            margin-bottom: 24px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #23235b;
            font-size: 1.1rem;
        }
        .input-class {
            width: 100%;
            padding: 16px 18px;
            border-radius: 16px;
            border: 1.5px solid #bcd;
            font-size: 1.2rem;
            background: #f4f6fa;
            margin-bottom: 0;
            box-sizing: border-box;
        }
        .btn {
            padding: 18px 48px;
            border-radius: 32px;
            border: none;
            background: #4f8cff;
            color: #fff;
            font-weight: bold;
            font-size: 1.3rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover { background: #357ae8; }
        .success {
            background: #d8ffe0;
            color: #228B22;
            padding: 18px 0;
            border-radius: 12px;
            margin: 32px 0 0 0;
            text-align: center;
            font-size: 1.2rem;
        }
        .error {
            background: #f8d7da;
            color: #842029;
            padding: 12px 0;
            border-radius: 12px;
            margin-bottom: 18px;
            text-align: center;
            font-size: 1.1rem;
        }
        .code { font-weight: bold; color: #228B22; }
        .lihat-kelas-btn {
            margin-top: 32px;
            background: #4f8cff;
            color: #fff;
            border: none;
            border-radius: 32px;
            padding: 14px 36px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
        }
        .lihat-kelas-btn:hover {
            background: #357ae8;
        }
        @media (max-width: 900px) {
            .container { padding: 24px 8px; }
            .main-content { padding: 24px 0; }
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <?php include 'sidebar_dosen.php'; ?>
    <div class="main-content">
        <div class="container">
            <?php if ($message && strpos($message, 'berhasil') !== false): ?>
                <div class="success">
                    <?php echo $message; ?>
                </div>
                <form action="lecturer_dashboard.php" method="get">
                    <input type="hidden" name="menu" value="kelas">
                    <button type="submit" class="lihat-kelas-btn">Lihat Kelas</button>
                </form>
            <?php else: ?>
                <?php if ($message): ?>
                    <div class="error"><?php echo $message; ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="form-group">
                        <label for="class_name">Nama Kelas</label>
                        <input class="input-class" type="text" id="class_name" name="class_name" placeholder="Masukkan nama kelas" required>
                    </div>
                    <button class="btn" type="submit">Buat Kelas</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>