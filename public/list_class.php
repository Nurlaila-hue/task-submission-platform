<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'lecturer') {
    header("Location: login.php");
    exit();
}
require_once '../src/config/database.php';
$db = (new Database())->getConnection();

// Ambil data user
$stmt = $db->prepare("SELECT username, profile_pic FROM users WHERE id = :id");
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$lecturerName = $user['username'] ?? '';
$profile_pic = (!empty($user['profile_pic'])) ? $user['profile_pic'] : 'assets/default-avatar.png';

// Ambil semua kelas yang dibuat oleh dosen ini
$stmt = $db->prepare("SELECT * FROM classes WHERE lecturer_name = :lecturer_name ORDER BY created_at DESC");
$stmt->bindParam(':lecturer_name', $lecturerName);
$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Kelas Anda</title>
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
        }
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 120px;
            height: 100vh;
            background: #23235b;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 32px;
            z-index: 10;
        }
        .sidebar .profile-mini {
            width: 70px;
            height: 70px;
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
            font-size: 1.1rem;
            font-weight: bold;
            margin-bottom: 18px;
            text-align: center;
        }
        .sidebar .menu-link {
            margin: 12px 0;
            color: #fff;
            text-decoration: none;
            font-size: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            border-radius: 12px;
            transition: background 0.2s;
            position: relative;
            cursor: pointer;
        }
        .sidebar .menu-link.active, .sidebar .menu-link:hover {
            background: #4f8cff;
        }
        .sidebar .logout-link {
            margin: 24px 0 0 0;
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
        /* Modal styles */
        .modal-profile {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100vw; height: 100vh;
            background: rgba(0,0,0,0.25);
            align-items: center;
            justify-content: center;
        }
        .modal-profile.active {
            display: flex;
        }
        .modal-content {
            background: #fff;
            border-radius: 24px;
            padding: 36px 32px 28px 32px;
            min-width: 340px;
            box-shadow: 0 8px 32px #23235b22;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }
        .modal-content .close-modal {
            position: absolute;
            top: 18px;
            right: 22px;
            font-size: 1.5rem;
            color: #23235b;
            background: none;
            border: none;
            cursor: pointer;
        }
        .modal-content .profile-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: #eaf3ff;
            overflow: hidden;
            margin-bottom: 18px;
            border: 3px solid #4f8cff;
        }
        .modal-content .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .modal-content .profile-username {
            font-size: 1.2rem;
            font-weight: bold;
            color: #23235b;
            margin-bottom: 18px;
        }
        .modal-content .profile-actions {
            display: flex;
            flex-direction: column;
            gap: 14px;
            width: 100%;
        }
        .modal-content .profile-actions a {
            background: #4f8cff;
            color: #fff;
            text-decoration: none;
            border-radius: 18px;
            padding: 12px 0;
            text-align: center;
            font-weight: bold;
            font-size: 1.05rem;
            transition: background 0.2s;
        }
        .modal-content .profile-actions a:hover {
            background: #23235b;
        }
        .main-wrapper {
            margin-left: 120px;
            padding: 36px 0 0 0;
        }
        .welcome-banner {
            background: #4f8cff;
            color: #fff;
            border-radius: 40px;
            padding: 32px 48px;
            margin: 32px 48px 36px 48px;
            display: flex;
            align-items: center;
            gap: 28px;
            box-shadow: 0 4px 24px #4f8cff33;
            max-width: 90vw;
        }
        .welcome-banner .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #fff;
            overflow: hidden;
            border: 4px solid #fff;
            box-shadow: 0 2px 12px #4f8cff22;
        }
        .welcome-banner .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .welcome-banner .welcome-text {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .welcome-banner .welcome-desc {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-top: 4px;
        }
        .section-title {
            font-size: 1.3rem;
            font-weight: bold;
            color: #23235b;
            margin-bottom: 18px;
            margin-left: 80px;
        }
        .class-list-card {
            background: #fff;
            border-radius: 48px;
            box-shadow: 0 8px 32px #4f8cff13;
            padding: 48px 0 48px 0;
            max-width: 900px;
            margin: 0 auto;
        }
        .class-table {
            width: 90%;
            margin: 0 auto;
            border-collapse: separate;
            border-spacing: 0 18px;
        }
        .class-table th, .class-table td {
            padding: 22px 28px;
            text-align: left;
            font-size: 1.25rem;
        }
        .class-table th {
            background: #f6faff;
            color: #23235b;
            font-size: 1.3rem;
            font-weight: bold;
            border: none;
        }
        .class-table tr.class-row {
            background: #f6faff;
            border-radius: 32px;
            box-shadow: 0 2px 12px #4f8cff11;
            transition: box-shadow 0.2s;
        }
        .class-table tr.class-row:hover {
            box-shadow: 0 4px 24px #4f8cff22;
            background: #eaf3ff;
        }
        .class-name {
            display: flex;
            align-items: center;
            gap: 16px;
            font-weight: 500;
        }
        .class-icon {
            font-size: 2.1rem;
            background: #4f8cff;
            color: #fff;
            border-radius: 50%;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px #4f8cff22;
        }
        .class-code {
            background: #eaf3ff;
            color: #4f8cff;
            padding: 10px 28px;
            border-radius: 22px;
            font-weight: bold;
            letter-spacing: 1px;
            font-size: 1.15rem;
            display: inline-block;
        }
        .aksi {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        .task-link {
            background: #4f8cff;
            color: #fff !important;
            padding: 16px 38px;
            border-radius: 28px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.15rem;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px #4f8cff22;
            display: inline-block;
            border: none;
            outline: none;
        }
        .task-link.tambah {
            background: #43a0ff;
        }
        .task-link:hover {
            background: #23235b;
            color: #fff;
            box-shadow: 0 4px 16px #4f8cff33;
            text-decoration: none;
        }
        @media (max-width: 1100px) {
            .main-wrapper { margin-left: 0; }
            .class-list-card { padding: 24px 0; }
            .welcome-banner { padding: 18px 10px; margin: 18px 10px 24px 10px; }
        }
        @media (max-width: 700px) {
            .main-wrapper { padding: 8px 0 0 0; }
            .welcome-banner { flex-direction: column; gap: 12px; }
            .class-list-card { padding: 8px 0; }
            .class-table th, .class-table td { padding: 10px 6px; font-size: 0.98rem; }
            .class-name .class-icon { width: 32px; height: 32px; font-size: 1.2rem; }
            .task-link { padding: 8px 12px; font-size: 0.95rem; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="profile-mini">
            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" onerror="this.onerror=null;this.src='assets/default-avatar.png';">
        </div>
        <div class="profile-name"><?php echo htmlspecialchars($lecturerName); ?></div>
        <a href="#" class="menu-link" id="profileMenu" title="Profil">&#128100;</a>
        <a href="#" class="menu-link active" title="Daftar Kelas">&#128452;</a>
        <a href="#" class="menu-link" title="Tambah Kelas">&#43;</a>
        <a href="logout.php" class="logout-link" title="Logout">&#x21b6; Logout</a>
    </div>

    <!-- Modal Profile -->
    <div class="modal-profile" id="modalProfile">
        <div class="modal-content">
            <button class="close-modal" id="closeModalProfile" title="Tutup">&times;</button>
            <div class="profile-avatar">
                <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" onerror="this.onerror=null;this.src='assets/default-avatar.png';">
            </div>
            <div class="profile-username"><?php echo htmlspecialchars($lecturerName); ?></div>
            <div class="profile-actions">
                <a href="profile.php">Lihat &amp; Edit Profil</a>
                <a href="change_profile_pic.php">Ganti Foto Profil</a>
                <a href="change_password.php">Ganti Password</a>
            </div>
        </div>
    </div>

    <div class="main-wrapper">
        <div class="welcome-banner">
            <div class="avatar">
                <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" onerror="this.onerror=null;this.src='assets/default-avatar.png';">
            </div>
            <div>
                <div class="welcome-text">Welcome, <?php echo htmlspecialchars($lecturerName); ?>!</div>
                <div class="welcome-desc">Have a productive day and don't forget to submit your assignments!</div>
            </div>
        </div>
        <div class="section-title">Daftar Kelas Anda</div>
        <div class="class-list-card">
            <table class="class-table">
                <thead>
                    <tr>
                        <th>Nama Kelas</th>
                        <th>Kode Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classes as $class): ?>
                    <tr class="class-row">
                        <td>
                            <div class="class-name">
                                <span class="class-icon">&#128187;</span>
                                <?php echo htmlspecialchars($class['class_name']); ?>
                            </div>
                        </td>
                        <td>
                            <span class="class-code"><?php echo htmlspecialchars($class['class_code']); ?></span>
                        </td>
                        <td class="aksi">
                            <a class="task-link" href="list_task.php?class_id=<?php echo $class['id']; ?>">Lihat Tugas</a>
                            <a class="task-link tambah" href="assign_task.php?class_id=<?php echo $class['id']; ?>">Tambah Tugas</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        // Modal Profile logic
        const profileMenu = document.getElementById('profileMenu');
        const modalProfile = document.getElementById('modalProfile');
        const closeModalProfile = document.getElementById('closeModalProfile');
        profileMenu.addEventListener('click', function(e) {
            e.preventDefault();
            modalProfile.classList.add('active');
        });
        closeModalProfile.addEventListener('click', function() {
            modalProfile.classList.remove('active');
        });
        window.addEventListener('click', function(e) {
            if (e.target === modalProfile) {
                modalProfile.classList.remove('active');
            }
        });
    </script>
</body>
</html>