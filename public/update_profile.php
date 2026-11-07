<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../src/config/database.php';
$db = (new Database())->getConnection();

$user_id = $_SESSION['user_id'];
$_SESSION['profile_message'] = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $maxSize = 2 * 1024 * 1024; // 2MB

    if ($file['error'] === 0 && in_array($ext, $allowed) && $file['size'] <= $maxSize) {
        $uploadDir = 'assets/profile/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $newName = 'profile_' . $user_id . '_' . time() . '.' . $ext;
        $dest = $uploadDir . $newName;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            // Update database
            $stmt = $db->prepare("UPDATE users SET profile_pic = :profile_pic WHERE id = :id");
            $stmt->bindParam(':profile_pic', $dest);
            $stmt->bindParam(':id', $user_id);
            if ($stmt->execute()) {
                $_SESSION['profile_message'] = '<div class="alert success">Foto profil berhasil diubah.</div>';
            } else {
                $_SESSION['profile_message'] = '<div class="alert error">Gagal memperbarui foto profil di database.</div>';
            }
        } else {
            $_SESSION['profile_message'] = '<div class="alert error">Gagal mengunggah file.</div>';
        }
    } else {
        $_SESSION['profile_message'] = '<div class="alert error">File tidak valid (format/jumlah ukuran max 2MB).</div>';
    }

    // Redirect langsung ke menu profile sesuai role
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student') {
        header("Location: student_dashboard.php?menu=profile");
    } else {
        header("Location: lecturer_dashboard.php?menu=profile");
    }
    exit();
} else {
    // Redirect langsung ke menu profile jika akses tidak valid
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student') {
        header("Location: student_dashboard.php?menu=profile");
    } else {
        header("Location: lecturer_dashboard.php?menu=profile");
    }
    exit();
}