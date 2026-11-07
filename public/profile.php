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
$profile_pic = (!empty($user['profile_pic'])) ? $user['profile_pic'] : 'assets/default-avatar.png';

// Handle upload foto profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            // Pastikan folder uploads ada
            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }
            $newName = 'profile_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
            $target = 'uploads/' . $newName;
            if (move_uploaded_file($file['tmp_name'], $target)) {
                $stmt = $db->prepare("UPDATE users SET profile_pic = :pic WHERE id = :id");
                $stmt->bindParam(':pic', $target);
                $stmt->bindParam(':id', $_SESSION['user_id']);
                $stmt->execute();
                $_SESSION['profile_message'] = "<div class='success-msg'>Foto profil berhasil diubah.</div>";
                header("Location: profile.php");
                exit();
            } else {
                $_SESSION['profile_message'] = "<div class='error-msg'>Gagal mengupload file.</div>";
                header("Location: profile.php");
                exit();
            }
        } else {
            $_SESSION['profile_message'] = "<div class='error-msg'>Format file tidak didukung.</div>";
            header("Location: profile.php");
            exit();
        }
    } else {
        $_SESSION['profile_message'] = "<div class='error-msg'>Pilih file gambar terlebih dahulu.</div>";
        header("Location: profile.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Dosen</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(120deg, #4f8cff 0%, #a6d1ff 100%);
        }
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .profile-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .profile-card {
            background: #fff;
            border-radius: 48px;
            box-shadow: 0 8px 32px #4f8cff22;
            padding: 48px 56px 40px 56px;
            max-width: 420px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .profile-back {
            align-self: flex-start;
            margin-bottom: 18px;
            font-size: 1.2rem;
            color: #4f8cff;
            text-decoration: none;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 5px solid #a6d1ff;
            background: #fff;
            margin-bottom: 18px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 12px #4f8cff22;
        }
        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-name {
            font-size: 2rem;
            font-weight: bold;
            color: #23235b;
            margin-bottom: 10px;
            text-align: center;
        }
        .profile-label {
            color: #23235b;
            font-size: 1.08rem;
            margin-bottom: 10px;
            text-align: center;
        }
        .profile-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 18px;
            width: 100%;
        }
        .upload-btn {
            background: #4f8cff;
            color: #fff;
            border: none;
            border-radius: 14px;
            padding: 14px 36px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 8px;
            transition: background 0.2s;
        }
        .upload-btn:hover {
            background: #23235b;
        }
        .change-pass-link {
            color: #4f8cff;
            text-decoration: none;
            font-size: 1.08rem;
            font-weight: 500;
            margin-top: 18px;
            display: inline-block;
        }
        .change-pass-link:hover {
            text-decoration: underline;
            color: #23235b;
        }
        .success-msg {
            background: #d1e7dd;
            color: #0f5132;
            padding: 10px 18px;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 1rem;
            text-align: center;
        }
        .error-msg {
            background: #f8d7da;
            color: #842029;
            padding: 10px 18px;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 1rem;
            text-align: center;
        }
        input[type="file"] {
            display: none;
        }
        @media (max-width: 700px) {
            .profile-card {
                padding: 18px 6vw;
                border-radius: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar_dosen.php'; ?>
        <div class="profile-content">
            <div class="profile-card">
                <a href="lecturer_dashboard.php" class="profile-back">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                        <path d="M15 19l-7-7 7-7" stroke="#4f8cff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Dashboard
                </a>
                <div class="profile-avatar">
                    <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" onerror="this.onerror=null;this.src='assets/default-avatar.png';">
                </div>
                <div class="profile-name"><?php echo htmlspecialchars($lecturerName); ?></div>
                <?php
                if (isset($_SESSION['profile_message'])) {
                    echo $_SESSION['profile_message'];
                    unset($_SESSION['profile_message']);
                }
                ?>
                <div class="profile-label">Ganti Foto Profile</div>
                <form class="profile-form" action="" method="post" enctype="multipart/form-data">
                    <input type="file" id="profile_pic" name="profile_pic" accept="image/*" onchange="this.form.submit()">
                    <button type="button" class="upload-btn" onclick="document.getElementById('profile_pic').click();">Upload Foto</button>
                </form>
                <a href="change_password.php" class="change-pass-link">Ganti Password</a>
            </div>
        </div>
    </div>
</body>
</html>