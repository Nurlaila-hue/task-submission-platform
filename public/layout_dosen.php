<?php
// Ambil data user dan set variabel sidebar di sini
session_start();
require_once '../src/config/database.php';
$db = (new Database())->getConnection();
$stmt = $db->prepare("SELECT username, profile_pic FROM users WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$profile_pic = $user['profile_pic'] ?? 'assets/default-avatar.png';
$lecturerName = $user['username'] ?? 'Dosen';
// $menu di-set dari halaman konten
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $pageTitle ?? 'Dashboard Dosen'; ?></title>
    <!-- ...CSS dan style... -->
</head>
<body>
    <?php include 'sidebar_dosen.php'; ?>
    <div class="main-content">
        <?php include $contentFile; ?>
    </div>
</body>
</html>