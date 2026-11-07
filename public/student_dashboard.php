<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'student') {
    header('Location: login.php');
    exit();
}

require_once '../src/config/database.php';

$db = (new Database())->getConnection();
$escape = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$feedback = $_SESSION['class_join_feedback'] ?? null;
unset($_SESSION['class_join_feedback']);

$profileMessage = $_SESSION['profile_message'] ?? null;
unset($_SESSION['profile_message']);

$stmt = $db->prepare('SELECT username, profile_pic FROM users WHERE id = :id');
$stmt->execute([':id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$studentName = $user['username'] ?? 'Siswa';
$profilePic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'assets/default-avatar.png';
$menu = $_GET['menu'] ?? 'dashboard';

$stmt = $db->prepare(
    'SELECT c.*
     FROM classes c
     JOIN class_members m ON c.id = m.class_id
     WHERE m.student_id = :student_id'
);
$stmt->execute([':student_id' => $_SESSION['user_id']]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$feedbackStatus = is_array($feedback) ? ($feedback['status'] ?? 'error') : 'error';
if (!in_array($feedbackStatus, ['success', 'info', 'error'], true)) {
    $feedbackStatus = 'error';
}
$feedbackTitle = $feedbackStatus === 'success'
    ? 'Berhasil bergabung!'
    : ($feedbackStatus === 'info' ? 'Informasi kelas' : 'Tidak berhasil');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Siswa</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            color: #202858;
            background: linear-gradient(135deg, #eaf2ff, #dceaff 48%, #eff6ff);
            font-family: "Segoe UI", Arial, sans-serif;
        }
        button, input { font: inherit; }
        .dashboard-container { display: flex; gap: 18px; min-height: 100vh; padding: 18px; }
        .sidebar {
            position: sticky; top: 18px; display: flex; flex: 0 0 82px;
            flex-direction: column; align-items: center; height: calc(100vh - 36px);
            min-height: 420px; padding: 24px 8px; border-radius: 24px;
            background: linear-gradient(180deg, #28285f, #202052);
            box-shadow: 0 18px 45px rgba(35,35,91,.2);
        }
        .profile-mini {
            width: 54px; height: 54px; margin-bottom: 10px; overflow: hidden;
            border: 3px solid #a6d1ff; border-radius: 50%; background: #fff;
        }
        .profile-mini img, .welcome-avatar img, .profile-avatar img {
            width: 100%; height: 100%; object-fit: cover;
        }
        .profile-name {
            width: 100%; margin-bottom: 22px; color: #fff;
            font-size: .76rem; font-weight: 700; text-align: center;
            overflow-wrap: anywhere;
        }
        .menu-link, .logout-link {
            display: flex; align-items: center; justify-content: center;
            width: 48px; height: 48px; margin: 8px 0; padding: 8px;
            border: 0; border-radius: 15px; color: #fff;
            background: transparent; text-decoration: none; cursor: pointer;
        }
        .menu-link:hover, .menu-link.active, .logout-link:hover { background: #4f8cff; }
        .logout-link { width: auto; gap: 7px; margin-top: auto; font-size: .85rem; }
        .main-content {
            flex: 1; min-width: 0; min-height: calc(100vh - 36px); padding: clamp(22px, 4vw, 48px);
            border: 1px solid #fff; border-radius: 28px;
            background: rgba(255,255,255,.9); box-shadow: 0 20px 60px rgba(35,35,91,.1);
        }
        .page-heading {
            display: flex; align-items: center; justify-content: space-between;
            gap: 20px; margin-bottom: 28px;
        }
        .page-heading-copy { min-width: 0; }
        .page-eyebrow {
            margin: 0 0 7px; color: #4f8cff; font-size: .75rem;
            font-weight: 800; letter-spacing: .14em; text-transform: uppercase;
        }
        .page-title {
            margin: 0; color: #23235b; font-size: clamp(1.5rem, 3vw, 2rem);
            letter-spacing: -.04em;
        }
        .page-description { margin: 7px 0 0; color: #76809a; }
        .class-count {
            flex: 0 0 auto; min-width: 120px; padding: 13px 16px;
            border: 1px solid #e4edff; border-radius: 17px;
            background: #f5f8ff; text-align: center;
        }
        .class-count span, .class-count strong { display: block; }
        .class-count span { color: #77819b; font-size: .76rem; }
        .class-count strong { margin-top: 3px; color: #23235b; font-size: 1.4rem; }
        .welcome-card {
            display: flex; align-items: center; gap: 18px; min-width: 0;
            min-height: 150px; margin-bottom: 26px; padding: 28px;
            border-radius: 24px; color: #fff;
            background: linear-gradient(115deg, #4f8cff, #619bff 55%, #8abaff);
            box-shadow: 0 16px 34px rgba(79,140,255,.23);
        }
        .welcome-avatar {
            flex: 0 0 68px; width: 68px; height: 68px; overflow: hidden;
            border: 4px solid rgba(255,255,255,.75); border-radius: 50%; background: #fff;
        }
        .welcome-text { margin-bottom: 6px; font-size: 1.25rem; font-weight: 700; }
        .dashboard-section { min-width: 0; }
        .section-title { margin: 0 0 14px; color: #23235b; font-size: 1.12rem; }
        .join-class-card, .class-list-card {
            padding: 22px; border: 1px solid #e9eef8; border-radius: 20px;
            background: #fff; box-shadow: 0 10px 28px rgba(35,35,91,.055);
        }
        .join-form { display: flex; gap: 12px; }
        .join-input {
            flex: 1; min-width: 0; min-height: 48px; padding: 11px 14px;
            border: 1px solid #dfe7f4; border-radius: 12px;
            background: #f8faff; color: #23235b;
        }
        .join-input:focus { outline: 3px solid rgba(79,140,255,.2); border-color: #4f8cff; }
        .join-btn, .feedback-dismiss {
            min-height: 46px; padding: 10px 20px; border: 0; border-radius: 11px;
            color: #fff; background: #4f8cff; font-weight: 700; cursor: pointer;
        }
        .join-btn:hover, .feedback-dismiss:hover { background: #23235b; }
        .class-list-card { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        th, td { padding: 13px 14px; text-align: left; }
        th { color: #fff; background: #23235b; font-size: .9rem; }
        th:first-child { border-radius: 10px 0 0 10px; }
        th:last-child { border-radius: 0 10px 10px 0; }
        td { color: #23235b; background: #f4f6fa; }
        .task-link {
            display: inline-block; padding: 8px 12px; border-radius: 9px;
            color: #3478ec; background: #eef4ff; font-weight: 650; text-decoration: none;
        }
        .task-link:hover { background: #dfeaff; }
        .leave-btn {
            margin: 4px; padding: 8px 11px; border: 1px solid #ffe0e0;
            border-radius: 9px; color: #d5474a; background: #fff5f5;
            font-weight: 650; cursor: pointer;
        }
        .no-data {
            padding: 32px 16px; border: 1px dashed #d9e3f3; border-radius: 14px;
            color: #77819a; background: #f9fbff; text-align: center;
        }
        .modal {
            position: fixed; inset: 0; z-index: 1000; display: none;
            align-items: center; justify-content: center; padding: 16px;
            background: rgba(26,36,68,.5);
        }
        .modal.active { display: flex; }
        .modal-content {
            position: relative; width: min(100%, 400px); max-height: calc(100vh - 32px);
            overflow-y: auto; padding: 34px 26px; border-radius: 22px;
            background: #fff; box-shadow: 0 24px 70px rgba(35,35,91,.22); text-align: center;
        }
        .modal-close {
            position: absolute; top: 10px; right: 15px; border: 0;
            color: #4f8cff; background: transparent; font-size: 1.8rem; cursor: pointer;
        }
        .profile-avatar {
            width: 100px; height: 100px; margin: 10px auto 14px; overflow: hidden;
            border: 4px solid #a6d1ff; border-radius: 50%; background: #4f8cff;
        }
        .profile-username { margin-bottom: 8px; color: #23235b; font-size: 1.2rem; font-weight: 700; }
        .profile-form { display: flex; flex-direction: column; align-items: center; gap: 10px; margin-top: 14px; }
        .profile-form input[type="file"] { max-width: 100%; }
        .profile-form button {
            padding: 11px 22px; border: 0; border-radius: 10px;
            color: #fff; background: #4f8cff; font-weight: 700; cursor: pointer;
        }
        .change-password { display: inline-block; margin-top: 14px; color: #3478ec; font-weight: 700; }
        .feedback-modal {
            position: fixed; inset: 0; z-index: 1100; display: flex;
            align-items: center; justify-content: center; padding: 14px;
            background: rgba(26,36,68,.52);
        }
        .feedback-dialog {
            width: min(100%, 420px); padding: 28px 22px; border-radius: 22px;
            background: #fff; box-shadow: 0 24px 70px rgba(35,35,91,.22); text-align: center;
        }
        .feedback-icon {
            display: grid; width: 56px; height: 56px; margin: 0 auto 14px;
            place-items: center; border-radius: 50%; font-size: 1.6rem; font-weight: 800;
        }
        .feedback-icon.success { color: #16834a; background: #e2f6eb; }
        .feedback-icon.info { color: #2867c7; background: #e8f0ff; }
        .feedback-icon.error { color: #bd3030; background: #fdeaea; }
        .feedback-title { margin: 0 0 10px; color: #23235b; font-size: 1.3rem; }
        .feedback-message { margin: 0 0 20px; color: #596579; line-height: 1.5; }
        @media (max-width: 700px) {
            .dashboard-container { flex-direction: column; gap: 10px; padding: 10px; }
            .sidebar {
                position: sticky; top: 8px; z-index: 20; flex: 0 0 auto;
                flex-direction: row; justify-content: flex-start; width: 100%;
                height: auto; min-height: 62px; padding: 8px 10px; border-radius: 18px;
            }
            .profile-mini { flex: 0 0 42px; width: 42px; height: 42px; margin: 0 8px 0 0; }
            .profile-name { width: auto; max-width: 75px; margin: 0 auto 0 0; font-size: .7rem; }
            .menu-link { flex: 0 0 40px; width: 40px; height: 40px; margin: 0 2px; }
            .logout-link { flex: 0 0 40px; width: 40px; height: 40px; margin: 0 0 0 auto; }
            .logout-link span { display: none; }
            .main-content { width: 100%; min-height: calc(100vh - 82px); padding: 20px 14px; border-radius: 22px; }
            .page-heading { flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
            .page-heading-copy { flex: 1 1 180px; }
            .class-count { min-width: 78px; padding: 9px; }
            .welcome-card { min-height: 120px; padding: 20px 16px; }
            .join-form { flex-direction: column; }
            .join-btn { width: 100%; }
            .class-list-card table { min-width: 520px; }
        }
        @media (max-width: 430px) {
            .profile-mini { flex-basis: 38px; width: 38px; height: 38px; margin-right: 4px; }
            .profile-name { display: none; }
            .menu-link, .logout-link { flex-basis: 38px; width: 38px; height: 38px; margin-left: 1px; margin-right: 1px; padding: 6px; }
            .main-content { padding: 18px 12px; }
            .page-title { font-size: 1.35rem; overflow-wrap: anywhere; }
            .welcome-card { gap: 12px; padding: 18px 14px; }
            .welcome-avatar { flex-basis: 48px; width: 48px; height: 48px; }
            .join-class-card, .class-list-card { padding: 16px 12px; }
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="profile-mini">
            <img src="<?= $escape($profilePic) ?>" alt="Foto profil"
                 onerror="this.onerror=null;this.src='assets/default-avatar.png';">
        </div>
        <div class="profile-name"><?= $escape($studentName) ?></div>

        <button type="button" class="menu-link" title="Profil" aria-label="Profil"
                onclick="openProfileModal()">
            <svg width="26" height="26" viewBox="0 0 32 32" fill="none" aria-hidden="true">
                <circle cx="16" cy="12" r="6" stroke="#fff" stroke-width="2"/>
                <path d="M6 26c0-4 4.477-7 10-7s10 3 10 7" stroke="#fff" stroke-width="2"/>
            </svg>
        </button>
        <a class="menu-link <?= $menu === 'dashboard' ? 'active' : '' ?>"
           href="student_dashboard.php?menu=dashboard" title="Dashboard" aria-label="Dashboard">
            <svg width="25" height="25" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M3 10.5 12 4l9 6.5M5 10v10h14V10" stroke="#fff" stroke-width="2"/>
            </svg>
        </a>
        <a class="menu-link <?= $menu === 'kelas' ? 'active' : '' ?>"
           href="student_dashboard.php?menu=kelas" title="Daftar kelas" aria-label="Daftar kelas">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <rect x="3" y="4" width="18" height="16" rx="2" stroke="#fff" stroke-width="2"/>
                <path d="M7 4v16" stroke="#fff" stroke-width="2"/>
            </svg>
        </a>
        <a href="logout.php" class="logout-link" title="Logout" aria-label="Logout">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M8 7 3 12l5 5M3 12h12" stroke="#fff" stroke-width="2"/>
                <rect x="15" y="6" width="6" height="12" rx="2" stroke="#fff" stroke-width="2"/>
            </svg>
            <span>Logout</span>
        </a>
    </aside>

    <main class="main-content">
        <header class="page-heading">
            <div class="page-heading-copy">
                <p class="page-eyebrow">Area siswa</p>
                <h1 class="page-title"><?= $menu === 'kelas' ? 'Kelas Saya' : 'Dashboard Belajar' ?></h1>
                <p class="page-description">
                    <?= $menu === 'kelas'
                        ? 'Lihat kelas yang Anda ikuti dan akses tugasnya.'
                        : 'Kelola kelas dan aktivitas belajar Anda dari sini.' ?>
                </p>
            </div>
            <div class="class-count">
                <span>Kelas diikuti</span>
                <strong><?= count($classes) ?></strong>
            </div>
        </header>

        <?php if ($menu === 'dashboard'): ?>
            <section class="welcome-card">
                <div class="welcome-avatar">
                    <img src="<?= $escape($profilePic) ?>" alt="Foto profil"
                         onerror="this.onerror=null;this.src='assets/default-avatar.png';">
                </div>
                <div>
                    <div class="welcome-text">Selamat datang, <?= $escape($studentName) ?>!</div>
                    <div>Semoga hari Anda produktif. Jangan lupa mengumpulkan tugas.</div>
                </div>
            </section>
            <section class="dashboard-section">
                <h2 class="section-title">Gabung Kelas</h2>
                <div class="join-class-card">
                    <form class="join-form" action="join_class.php" method="post">
                        <input class="join-input" type="text" name="class_code"
                               placeholder="Masukkan kode kelas" autocomplete="off" required>
                        <button class="join-btn" type="submit">Gabung Kelas</button>
                    </form>
                </div>
            </section>
        <?php elseif ($menu === 'kelas'): ?>
            <section class="dashboard-section">
                <h2 class="section-title">Daftar Kelas Anda</h2>
                <div class="class-list-card">
                    <?php if ($classes): ?>
                        <table>
                            <thead><tr><th>Nama Kelas</th><th>Kode Kelas</th><th>Aksi</th></tr></thead>
                            <tbody>
                            <?php foreach ($classes as $class): ?>
                                <tr>
                                    <td><?= $escape($class['class_name'] ?? '') ?></td>
                                    <td><?= $escape($class['class_code'] ?? '') ?></td>
                                    <td>
                                        <a class="task-link"
                                           href="upload_task.php?class_id=<?= (int) $class['id'] ?>">Lihat Tugas</a>
                                        <form action="leave_class.php" method="post" style="display:inline"
                                              onsubmit="return confirm('Yakin ingin keluar dari kelas ini?');">
                                            <input type="hidden" name="class_id" value="<?= (int) $class['id'] ?>">
                                            <button class="leave-btn" type="submit">Keluar Kelas</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="no-data">Anda belum bergabung di kelas manapun.</div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>
</div>

<?php if (is_array($feedback) && isset($feedback['message'])): ?>
<div class="feedback-modal" id="classJoinFeedback" role="alertdialog" aria-modal="true">
    <div class="feedback-dialog">
        <div class="feedback-icon <?= $escape($feedbackStatus) ?>">
            <?= $feedbackStatus === 'success' ? '&#10003;' : ($feedbackStatus === 'info' ? 'i' : '!') ?>
        </div>
        <h2 class="feedback-title"><?= $escape($feedbackTitle) ?></h2>
        <p class="feedback-message"><?= $escape($feedback['message']) ?></p>
        <button type="button" class="feedback-dismiss" id="closeClassJoinFeedback">Tutup</button>
    </div>
</div>
<?php endif; ?>

<div class="modal" id="profileModal" role="dialog" aria-modal="true">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeProfileModal()" aria-label="Tutup">&times;</button>
        <?php if ($profileMessage !== null): ?>
            <p><?= $escape(is_string($profileMessage) ? $profileMessage : '') ?></p>
        <?php endif; ?>
        <div class="profile-avatar">
            <img src="<?= $escape($profilePic) ?>" alt="Foto profil"
                 onerror="this.onerror=null;this.src='assets/default-avatar.png';">
        </div>
        <div class="profile-username"><?= $escape($studentName) ?></div>
        <form class="profile-form" method="post" enctype="multipart/form-data"
              action="update_profile.php" id="profileForm">
            <label for="profile_pic">Ganti foto profil</label>
            <input type="file" name="profile_pic" id="profile_pic" accept="image/*" required>
            <button type="button" id="uploadBtn">Pilih foto</button>
        </form>
        <a href="change_password.php" class="change-password">Ganti Password</a>
    </div>
</div>

<script>
function openProfileModal() {
    document.getElementById('profileModal').classList.add('active');
}
function closeProfileModal() {
    document.getElementById('profileModal').classList.remove('active');
}
document.addEventListener('DOMContentLoaded', function () {
    const fileInput = document.getElementById('profile_pic');
    const profileForm = document.getElementById('profileForm');
    const uploadButton = document.getElementById('uploadBtn');

    uploadButton.addEventListener('click', function () { fileInput.click(); });
    fileInput.addEventListener('change', function () {
        if (fileInput.files.length) profileForm.submit();
    });

    const feedback = document.getElementById('classJoinFeedback');
    const closeButton = document.getElementById('closeClassJoinFeedback');
    if (feedback && closeButton) {
        closeButton.focus();
        closeButton.addEventListener('click', function () { feedback.remove(); });
        feedback.addEventListener('click', function (event) {
            if (event.target === feedback) feedback.remove();
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') feedback.remove();
        });
    }

    <?php if ($profileMessage !== null): ?>
    openProfileModal();
    <?php endif; ?>
});
</script>
</body>
</html>