<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
require_once '../src/config/database.php';
$db = (new Database())->getConnection();

$user_id = $_SESSION['user_id'];
// Ambil username dan foto profil
$stmt = $db->prepare("SELECT username, password, profile_pic, role FROM users WHERE id = :id");
$stmt->bindParam(':id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$username = $user['username'] ?? '';
$old_password_hash = $user['password'] ?? '';
$profile_pic = (!empty($user['profile_pic'])) ? $user['profile_pic'] : 'assets/default-avatar.png';
$role = $user['role'] ?? '';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_password = $_POST['old_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
        $message = '<div class="alert error">Semua field harus diisi.</div>';
    } elseif (!password_verify($old_password, $old_password_hash)) {
        $message = '<div class="alert error">Password lama salah.</div>';
    } elseif ($new_password !== $confirm_password) {
        $message = '<div class="alert error">Konfirmasi password baru tidak cocok.</div>';
    } elseif (strlen($new_password) < 5) {
        $message = '<div class="alert error">Password baru minimal 5 karakter.</div>';
    } else {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->bindParam(':password', $new_hash);
        $stmt->bindParam(':id', $user_id);
        if ($stmt->execute()) {
            $_SESSION['profile_message'] = '<div class="alert success">Password berhasil diubah.</div>';
            // Redirect ke profile.php untuk dosen, student_dashboard.php untuk mahasiswa
            if ($role === 'lecturer') {
                header("Location: profile.php");
            } else {
                header("Location: student_dashboard.php");
            }
            exit();
        } else {
            $message = '<div class="alert error">Gagal mengubah password.</div>';
        }
    }
}
$menu = 'profile';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ganti Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(120deg, #4f8cff 0%, #7bb6ff 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 100px;
            background: #23235b;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 32px 0 24px 0;
            border-radius: 0 24px 24px 0;
            box-shadow: 0 8px 32px rgba(0,0,0,0.13);
            z-index: 10;
        }
        .sidebar .profile-mini {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #fff;
            margin-bottom: 18px;
            overflow: hidden;
            border: 3px solid #4f8cff;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sidebar .profile-mini img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .sidebar .profile-name {
            color: #fff;
            font-size: 1rem;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
        .sidebar .menu-link {
            margin: 12px 0;
            color: #fff;
            text-decoration: none;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            transition: background 0.2s;
            position: relative;
        }
        .sidebar .menu-link.active, .sidebar .menu-link:hover {
            background: #4f8cff;
        }
        .sidebar .menu-label {
            position: absolute;
            left: 60px;
            background: #23235b;
            color: #fff;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 0.95rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
        }
        .sidebar .menu-link:hover .menu-label {
            opacity: 1;
        }
        .sidebar .logout-link {
            margin: 18px 0 0 0;
            color: #fff;
            text-decoration: none;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: auto;
            height: 44px;
            border-radius: 12px;
            gap: 7px;
            padding: 0 14px;
            transition: background 0.2s;
        }
        .sidebar .logout-link:hover {
            background: #4f8cff;
        }
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .center-card {
            max-width: 400px;
            width: 100%;
            background: #fff;
            border-radius: 40px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.13);
            padding: 38px 32px 32px 32px;
            text-align: center;
        }
        .center-card h2 {
            margin-top: 0;
            color: #23235b;
            font-size: 2rem;
            margin-bottom: 28px;
        }
        .form-label {
            display: block;
            text-align: left;
            margin-bottom: 6px;
            font-weight: bold;
            color: #23235b;
        }
        .form-input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1.5px solid #e0e0e0;
            font-size: 1rem;
            margin-bottom: 18px;
            background: #f4f6fa;
        }
        .form-input[readonly] {
            background: #e9ecef;
            color: #888;
        }
        .change-pass-btn {
            padding: 12px 28px;
            border-radius: 12px;
            border: none;
            background: #4f8cff;
            color: #fff;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s;
            display: block;
            width: 100%;
            margin-top: 10px;
            margin-bottom: 18px;
            text-align: center;
            text-decoration: none;
        }
        .change-pass-btn:hover {
            background: #23235b;
        }
        .alert {
            padding: 10px 18px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 1rem;
        }
        .alert.success { background: #d4edda; color: #155724; }
        .alert.error { background: #f8d7da; color: #721c24; }
        .back-link {
            color: #4f8cff;
            text-decoration: none;
            font-size: 1rem;
            display: inline-block;
            margin-top: 10px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 900px) {
            .main-content { padding: 16px; }
        }
        @media (max-width: 700px) {
            .dashboard-container { flex-direction: column; }
            .sidebar { flex-direction: row; width: 100vw; height: 70px; border-radius: 0 0 24px 24px; padding: 0 12px; }
            .sidebar .profile-mini { margin-bottom: 0; margin-right: 10px; }
            .main-content { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="profile-mini">
                <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" onerror="this.onerror=null;this.src='assets/default-avatar.png';">
            </div>
            <div class="profile-name"><?php echo htmlspecialchars($username); ?></div>
            <!-- Profile Menu -->
            <a href="profile.php" class="menu-link<?php if($menu=='profile') echo ' active'; ?>" title="Profile Menu">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="8" r="4" stroke="#fff" stroke-width="2"/>
                    <path d="M4 20c0-4 4-7 8-7s8 3 8 7" stroke="#fff" stroke-width="2"/>
                </svg>
                <span class="menu-label">Profile</span>
            </a>
            <!-- Daftar Kelas -->
            <?php if ($role === 'lecturer'): ?>
            <a href="lecturer_dashboard.php?menu=kelas" class="menu-link" title="Daftar Kelas">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="4" width="18" height="16" rx="2" stroke="#fff" stroke-width="2"/>
                    <path d="M7 4v16" stroke="#fff" stroke-width="2"/>
                </svg>
                <span class="menu-label">Daftar Kelas</span>
            </a>
            <a href="lecturer_dashboard.php?menu=dashboard" class="menu-link" title="Dashboard">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                    <path d="M3 10.5L12 4L21 10.5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5 10V20H19V10" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="menu-label">Dashboard</span>
            </a>
            <?php else: ?>
            <a href="student_dashboard.php?menu=kelas" class="menu-link" title="Daftar Kelas">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                    <rect x="3" y="4" width="18" height="16" rx="2" stroke="#fff" stroke-width="2"/>
                    <path d="M7 4v16" stroke="#fff" stroke-width="2"/>
                </svg>
                <span class="menu-label">Daftar Kelas</span>
            </a>
            <a href="student_dashboard.php?menu=dashboard" class="menu-link" title="Dashboard">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none">
                    <path d="M3 10.5L12 4L21 10.5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5 10V20H19V10" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span class="menu-label">Dashboard</span>
            </a>
            <?php endif; ?>
            <!-- Logout -->
            <a href="logout.php" class="logout-link" title="Logout">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                    <path d="M8 7l-5 5 5 5" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M3 12h12" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="15" y="6" width="6" height="12" rx="2" stroke="#fff" stroke-width="2"/>
                </svg>
                <span style="color:#fff;font-size:1rem;font-weight:500;">Logout</span>
            </a>
        </div>
        <!-- Main Content -->
        <div class="main-content">
            <div class="center-card">
                <h2>Ganti Password</h2>
                <?php if (!empty($message)) echo $message; ?>
                <form method="post" action="change_password.php" autocomplete="off">
                    <label class="form-label">Username</label>
                    <input class="form-input" type="text" value="<?php echo htmlspecialchars($username); ?>" readonly>
                    <label class="form-label">Password Lama</label>
                    <input class="form-input" type="password" name="old_password" required autocomplete="current-password">
                    <label class="form-label">Password Baru</label>
                    <input class="form-input" type="password" name="new_password" required autocomplete="new-password">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input class="form-input" type="password" name="confirm_password" required autocomplete="new-password">
                    <button type="submit" class="change-pass-btn">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>