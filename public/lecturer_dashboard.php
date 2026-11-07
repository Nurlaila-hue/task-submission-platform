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
$lecturerName = $user['username'] ?? 'Dosen';
// Gunakan default-avatar.png jika belum upload foto
$profile_pic = (!empty($user['profile_pic'])) ? $user['profile_pic'] : 'assets/default-avatar.png';

// Tentukan menu aktif
$menu = $_GET['menu'] ?? 'dashboard';

// Ambil daftar kelas dosen
$stmt = $db->prepare("SELECT * FROM classes WHERE lecturer_name = :lecturer_name");
$stmt->bindParam(':lecturer_name', $lecturerName);
$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Dosen</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(120deg, #4f8cff 0%, #a6d1ff 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .main-content {
            flex: 1;
            padding: 36px;
            background: #f4f6fa;
            border-radius: 24px;
            margin: 24px 0 24px 24px;
        }
        .welcome-card {
            background: #4f8cff;
            color: #fff;
            border-radius: 32px;
            padding: 28px 36px;
            display: flex;
            align-items: center;
            box-shadow: 0 4px 24px #4f8cff33;
            min-width: 320px;
            margin-bottom: 32px;
        }
        .welcome-card .avatar {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: #fff;
            margin-right: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 3px solid #a6d1ff;
        }
        .welcome-card .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .welcome-card .welcome-text {
            font-size: 1.2rem;
            font-weight: 500;
        }
        .dashboard-section {
            margin-top: 0;
        }
        .section-title {
            font-size: 1.1rem;
            font-weight: bold;
            color: #23235b;
            margin-bottom: 18px;
        }
        .create-class-card {
            background: #fff;
            border-radius: 32px;
            box-shadow: 0 2px 12px #4f8cff22;
            padding: 36px 28px;
            margin-bottom: 28px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        .create-form {
            display: flex;
            align-items: center;
            gap: 24px;
            justify-content: center;
            margin: 0;
            padding: 0;
        }
        .create-input {
            flex: 1 1 320px;
            min-width: 220px;
            max-width: 400px;
            padding: 18px 24px;
            border-radius: 18px;
            border: 2px solid #bcd;
            font-size: 1.2rem;
            background: #f4f6fa;
            margin: 0;
            box-sizing: border-box;
        }
        .create-btn {
            padding: 18px 48px;
            border-radius: 32px;
            border: none;
            background: #4f8cff;
            color: #fff;
            font-weight: bold;
            font-size: 1.3rem;
            cursor: pointer;
            transition: background 0.2s;
            margin: 0;
            white-space: nowrap;
        }
        .create-btn:hover {
            background: #357ae8;
        }
        .class-list-card {
            background: #fff;
            border-radius: 40px;
            box-shadow: 0 4px 24px #4f8cff22;
            padding: 36px 28px;
            margin-bottom: 28px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        .class-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
        }
        .class-table th, .class-table td {
            padding: 16px 18px;
            text-align: left;
            font-size: 1.08rem;
        }
        .class-table th {
            background: none;
            color: #23235b;
            font-size: 1.15rem;
            font-weight: bold;
            border-bottom: 2px solid #e0e7ff;
        }
        .class-table tr {
            background: #f7faff;
            border-radius: 18px;
            box-shadow: 0 2px 8px #4f8cff11;
            transition: box-shadow 0.2s;
        }
        .class-table tr:hover {
            box-shadow: 0 4px 16px #4f8cff22;
            background: #eaf3ff;
        }
        .class-name {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }
        .class-icon {
            font-size: 1.3rem;
            background: #4f8cff;
            color: #fff;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .class-code {
            background: #eaf3ff;
            color: #4f8cff;
            padding: 6px 16px;
            border-radius: 16px;
            font-weight: bold;
            letter-spacing: 1px;
            font-size: 1.05rem;
            display: inline-block;
        }
        .task-link {
            background: #4f8cff;
            color: #fff !important;
            padding: 10px 22px;
            border-radius: 18px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1rem;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px #4f8cff22;
            display: inline-block;
        }
        .task-link:hover {
            background: #23235b;
            color: #fff;
            box-shadow: 0 4px 16px #4f8cff33;
            text-decoration: none;
        }
        .delete-btn {
            background: #ff4d4f;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            margin-left: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.2s;
        }
        .delete-btn:hover {
            background: #d9363e;
        }
        .no-data {
            color: #888;
            font-size: 1.1rem;
            text-align: center;
            padding: 24px 0;
        }
        @media (max-width: 900px) {
            .main-content { padding: 16px; margin: 0; border-radius: 0; }
            .welcome-card { flex-direction: column; gap: 18px; }
            .create-class-card { padding: 16px 8px; }
        }
        @media (max-width: 700px) {
            .dashboard-container { flex-direction: column; }
            .main-content { margin: 0; }
            .class-list-card { padding: 16px 4px; }
            .class-table th, .class-table td { padding: 10px 6px; font-size: 0.98rem; }
            .class-name .class-icon { width: 24px; height: 24px; font-size: 1rem; }
            .task-link { padding: 8px 12px; font-size: 0.95rem; }
            .create-form { flex-direction: column; align-items: stretch; gap: 14px; }
            .create-btn { width: 100%; }
        }

        * {
            box-sizing: border-box;
        }

        body {
            background:
                radial-gradient(circle at 8% 8%, rgba(166, 209, 255, 0.65), transparent 28%),
                linear-gradient(135deg, #eaf2ff 0%, #dceaff 48%, #eff6ff 100%);
            color: #202858;
        }

        .dashboard-container {
            gap: 18px;
            padding: 18px;
        }

        .main-content {
            min-width: 0;
            min-height: calc(100vh - 36px);
            margin: 0;
            padding: clamp(24px, 4vw, 48px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.88);
            box-shadow: 0 20px 60px rgba(35, 35, 91, 0.1);
            backdrop-filter: blur(12px);
        }

        .page-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 28px;
        }

        .page-eyebrow {
            margin: 0 0 7px;
            color: #4f8cff;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .page-title {
            margin: 0;
            color: #23235b;
            font-size: clamp(1.55rem, 3vw, 2rem);
            font-weight: 750;
            letter-spacing: -0.04em;
        }

        .page-description {
            margin: 7px 0 0;
            color: #76809a;
            font-size: 0.95rem;
        }

        .class-count {
            min-width: 126px;
            padding: 14px 18px;
            border: 1px solid #e4edff;
            border-radius: 18px;
            background: #f5f8ff;
            text-align: center;
        }

        .class-count span,
        .class-count strong {
            display: block;
        }

        .class-count span {
            color: #77819b;
            font-size: 0.76rem;
            font-weight: 600;
        }

        .class-count strong {
            margin-top: 3px;
            color: #23235b;
            font-size: 1.45rem;
        }

        .welcome-card {
            position: relative;
            min-width: 0;
            min-height: 176px;
            overflow: hidden;
            margin-bottom: 28px;
            padding: 32px 38px;
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 26px;
            background: linear-gradient(115deg, #4f8cff 0%, #619bff 55%, #8abaff 100%);
            box-shadow: 0 16px 34px rgba(79, 140, 255, 0.23);
        }

        .welcome-card::after {
            position: absolute;
            top: -90px;
            right: -20px;
            width: 290px;
            height: 290px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.07);
            content: "";
            pointer-events: none;
        }

        .welcome-card::before {
            position: absolute;
            right: 90px;
            bottom: -155px;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: rgba(35, 35, 91, 0.08);
            content: "";
            pointer-events: none;
        }

        .welcome-card .avatar,
        .welcome-card > div:last-child {
            position: relative;
            z-index: 1;
        }

        .welcome-card .avatar {
            width: 76px;
            height: 76px;
            flex: 0 0 76px;
            margin-right: 24px;
            border: 4px solid rgba(255, 255, 255, 0.72);
            box-shadow: 0 8px 18px rgba(35, 35, 91, 0.15);
        }

        .welcome-card .welcome-text {
            margin-bottom: 7px;
            font-size: clamp(1.2rem, 2.5vw, 1.55rem);
            font-weight: 600;
        }

        .welcome-card > div:last-child > div:last-child {
            color: rgba(255, 255, 255, 0.88);
            font-size: 0.94rem;
        }

        .section-title {
            margin-bottom: 14px;
            font-size: 1.12rem;
            letter-spacing: -0.02em;
        }

        .create-class-card,
        .class-list-card {
            max-width: none;
            margin: 0 0 28px;
            border: 1px solid #e9eef8;
            border-radius: 22px;
            background: #fff;
            box-shadow: 0 10px 28px rgba(35, 35, 91, 0.055);
        }

        .create-class-card {
            position: relative;
            overflow: hidden;
            padding: 28px;
        }

        .create-class-card::before {
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(180deg, #4f8cff, #a6d1ff);
            content: "";
        }

        .create-form {
            justify-content: flex-start;
            gap: 14px;
        }

        .create-input {
            min-height: 48px;
            max-width: 520px;
            padding: 11px 14px;
            border: 1px solid #dfe7f4;
            border-radius: 12px;
            background: #f8faff;
            color: #23235b;
            font-size: 1rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .create-input::placeholder {
            color: #9aa4ba;
        }

        .create-input:focus {
            outline: none;
            border-color: #4f8cff;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(79, 140, 255, 0.13);
        }

        .create-btn {
            min-height: 48px;
            padding: 11px 24px;
            border-radius: 12px;
            background: #4f8cff;
            box-shadow: 0 8px 16px rgba(79, 140, 255, 0.2);
            font-size: 1rem;
        }

        .class-list-card {
            overflow-x: auto;
            padding: 18px 22px;
        }

        .class-table {
            border-spacing: 0 8px;
        }

        .class-table th,
        .class-table td {
            padding: 14px 16px;
            font-size: 0.95rem;
        }

        .class-table th {
            background: #f3f6fd;
            color: #6f7892;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .class-table tr {
            background: transparent;
            box-shadow: none;
        }

        .class-table td {
            border-top: 1px solid #eff2f8;
            border-bottom: 1px solid #eff2f8;
            background: #fff;
            color: #23235b;
        }

        .class-table td:first-child {
            border-left: 1px solid #eff2f8;
            border-radius: 12px 0 0 12px;
        }

        .class-table td:last-child {
            border-right: 1px solid #eff2f8;
            border-radius: 0 12px 12px 0;
        }

        .class-table tr:hover {
            background: #fff;
            box-shadow: 0 8px 20px rgba(35, 35, 91, 0.06);
        }

        .class-icon {
            width: 36px;
            height: 36px;
            flex: 0 0 36px;
            border-radius: 12px;
            background: #eef4ff;
            color: #4f8cff;
            font-size: 1rem;
        }

        .class-code {
            padding: 7px 12px;
            border: 1px solid #e1ebff;
            border-radius: 9px;
            background: #f2f6ff;
            color: #3478ec;
            font-size: 0.88rem;
        }

        .task-link {
            padding: 8px 12px;
            border-radius: 9px;
            background: #eef4ff;
            color: #3478ec !important;
            box-shadow: none;
            font-size: 0.9rem;
        }

        .task-link:hover {
            background: #dfeaff;
            color: #23235b !important;
            box-shadow: none;
        }

        .delete-btn {
            padding: 8px 11px;
            border: 1px solid #ffe0e0;
            border-radius: 9px;
            background: #fff5f5;
            color: #d5474a;
            font-size: 0.88rem;
        }

        .delete-btn:hover {
            background: #ffe8e8;
        }

        .no-data {
            padding: 40px 18px;
            border: 1px dashed #d9e3f3;
            border-radius: 16px;
            background: #f9fbff;
            color: #77819a;
            font-size: 0.95rem;
        }

        @media (max-width: 900px) {
            .main-content {
                padding: 24px;
            }

            .welcome-card {
                flex-direction: row;
                gap: 0;
            }
        }

        @media (max-width: 700px) {
            .dashboard-container {
                flex-direction: column;
                gap: 10px;
                padding: 10px;
            }

            .main-content {
                min-height: calc(100vh - 90px);
                margin: 0;
                padding: 22px 16px;
                border-radius: 22px;
            }

            .page-heading {
                align-items: flex-start;
                margin-bottom: 20px;
            }

            .page-description {
                max-width: 240px;
                font-size: 0.84rem;
                line-height: 1.45;
            }

            .class-count {
                min-width: 82px;
                padding: 10px;
                border-radius: 14px;
            }

            .class-count span {
                font-size: 0.68rem;
            }

            .class-count strong {
                font-size: 1.2rem;
            }

            .welcome-card {
                min-height: 150px;
                margin-bottom: 22px;
                padding: 22px 18px;
                border-radius: 22px;
            }

            .welcome-card .avatar {
                width: 58px;
                height: 58px;
                flex-basis: 58px;
                margin-right: 16px;
            }

            .welcome-card > div:last-child > div:last-child {
                font-size: 0.82rem;
                line-height: 1.45;
            }

            .create-class-card {
                padding: 22px 18px;
            }

            .create-form {
                align-items: stretch;
                flex-direction: column;
            }

            .create-input {
                width: 100%;
                min-width: 0;
                max-width: none;
            }

            .create-btn {
                width: 100%;
            }

            .class-list-card {
                padding: 12px;
            }

            .class-table th,
            .class-table td {
                padding: 12px 10px;
                font-size: 0.86rem;
            }

            .task-link {
                margin-bottom: 6px;
            }
        }

        @media (max-width: 430px) {
            .welcome-card {
                align-items: flex-start;
                flex-direction: column;
                gap: 14px;
            }

            .welcome-card .avatar {
                margin-right: 0;
            }

            .page-title {
                font-size: 1.35rem;
            }

            .class-count {
                min-width: 70px;
                padding: 9px 7px;
            }
        }

        @media (max-width: 700px) {
            .dashboard-container {
                min-width: 0;
            }

            .main-content {
                width: 100%;
                min-width: 0;
                padding: 20px 14px;
            }

            .page-heading {
                flex-wrap: wrap;
                gap: 14px;
            }

            .page-heading > div:first-child {
                min-width: 0;
                flex: 1 1 190px;
            }

            .page-description {
                max-width: none;
            }

            .class-count {
                flex: 0 0 auto;
            }

            .welcome-card {
                width: 100%;
                min-width: 0;
                padding: 20px 16px;
            }

            .class-list-card {
                max-width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .class-table {
                min-width: 560px;
            }
        }

        @media (max-width: 430px) {
            .main-content {
                padding: 18px 12px;
            }

            .page-title {
                font-size: clamp(1.25rem, 6vw, 1.55rem);
                overflow-wrap: anywhere;
            }

            .welcome-card {
                align-items: flex-start;
                flex-direction: row;
                gap: 12px;
                padding: 18px 14px;
            }

            .welcome-card .avatar {
                flex: 0 0 46px;
                width: 46px;
                height: 46px;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar_dosen.php'; ?>
        <div class="main-content">
            <header class="page-heading">
                <div>
                    <p class="page-eyebrow">Area dosen</p>
                    <h1 class="page-title">
                        <?php echo $menu === 'kelas' ? 'Kelas Saya' : 'Dashboard Dosen'; ?>
                    </h1>
                    <p class="page-description">
                        <?php echo $menu === 'kelas'
                            ? 'Kelola kelas dan akses tugas yang diberikan.'
                            : 'Buat kelas dan kelola aktivitas pembelajaran.'; ?>
                    </p>
                </div>
                <div class="class-count" aria-label="<?php echo count($classes); ?> kelas dibuat">
                    <span>Kelas dibuat</span>
                    <strong><?php echo count($classes); ?></strong>
                </div>
            </header>

            <div class="welcome-card">
                <div class="avatar">
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" onerror="this.onerror=null;this.src='assets/default-avatar.png';">
                </div>
                <div>
                    <div class="welcome-text">Welcome, <?php echo htmlspecialchars($lecturerName); ?>!</div>
                    <div style="font-size:0.98rem;opacity:0.85;">Have a productive day and don't forget to submit your assignments!</div>
                </div>
            </div>
            <?php if ($menu == 'dashboard'): ?>
            <div class="dashboard-section">
                <div class="section-title">Buat Kelas Baru</div>
                <div class="create-class-card">
                    <form class="create-form" action="create_class.php" method="post">
                        <input 
                            class="create-input"
                            type="text" 
                            name="class_name" 
                            placeholder="Nama Kelas" 
                            required
                        >
                        <button class="create-btn" type="submit">Buat Kelas</button>
                    </form>
                </div>
            </div>
            <?php elseif ($menu == 'kelas'): ?>
            <div class="dashboard-section">
                <div class="section-title">Daftar Kelas Anda</div>
                <div class="class-list-card">
                    <?php if (count($classes) > 0): ?>
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
                            <tr>
                                <td>
                                    <div class="class-name">
                                        <span class="class-icon">📘</span>
                                        <?php echo htmlspecialchars($class['class_name']); ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="class-code"><?php echo htmlspecialchars($class['class_code']); ?></span>
                                </td>
                                <td>
                                    <a class="task-link" href="list_task.php?class_id=<?php echo $class['id']; ?>">Lihat Tugas</a>
                                    <form action="delete_class_lecturer.php" method="post" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus kelas ini beserta seluruh tugas dan data terkait?');">
                                        <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                        <button type="submit" class="delete-btn">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-data">Anda belum memiliki kelas. Silakan buat kelas baru.</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
