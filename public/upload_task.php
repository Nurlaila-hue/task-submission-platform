<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header("Location: login.php");
    exit();
}
require_once '../src/config/database.php';
$db = (new Database())->getConnection();

$class_id = $_GET['class_id'] ?? null;
if (!$class_id) {
    die("Class ID tidak ditemukan.");
}

// Ambil tugas aktif pada kelas ini (misal: tugas terbaru)
$stmt = $db->prepare("SELECT * FROM tasks WHERE class_id = :class_id ORDER BY deadline DESC LIMIT 1");
$stmt->bindParam(':class_id', $class_id);
$stmt->execute();
$task = $stmt->fetch(PDO::FETCH_ASSOC);

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $task) {
    date_default_timezone_set('Asia/Jakarta');
    $now = date('Y-m-d H:i:s');
    if ($now > $task['deadline']) {
        $message = '<div class="error-msg">Maaf, deadline pengumpulan tugas sudah lewat.</div>';
    } else {
        $student_id = $_SESSION['user_id'];
        $note = $_POST['notes'] ?? '';
        $file = $_FILES['file'] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $message = '<div class="error-msg">Silakan pilih file tugas yang akan diupload.</div>';
        } else {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $filename = date('YmdHis') . '_' . basename($file['name']);
            $targetPath = $uploadDir . $filename;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $stmt = $db->prepare("INSERT INTO submissions (task_id, student_id, file_path, note, submitted_at) VALUES (:task_id, :student_id, :file_path, :note, NOW())");
                $stmt->bindParam(':task_id', $task['id']);
                $stmt->bindParam(':student_id', $student_id);
                $stmt->bindParam(':file_path', $targetPath);
                $stmt->bindParam(':note', $note);
                $stmt->execute();
                $message = '<div class="success-msg">Tugas berhasil dikumpulkan!</div>';
            } else {
                $message = '<div class="error-msg">Gagal upload file. Silakan coba lagi.</div>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Upload Tugas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        html, body {
            height: 100%;
        }
        body {
            min-height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(120deg, #4f8cff 0%, #a6d1ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .main-content {
            min-height: 100vh;
            width: 100vw;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 48px 0;
            background: transparent;
        }
        .upload-container, .upload-container * {
            box-sizing: border-box;
        }
        .upload-container {
            max-width: 400px;
            width: 100%;
            background: #fff;
            border-radius: 32px;
            box-shadow: 0 8px 32px #4f8cff33, 0 1.5px 8px #a6d1ff33;
            padding: 36px 30px 30px 30px;
            margin-top: 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: fadeIn 0.5s;
            position: relative;
            overflow: hidden;
        }
        .upload-container::before {
            content: "";
            position: absolute;
            top: -60px; right: -60px;
            width: 140px; height: 140px;
            background: linear-gradient(135deg, #4f8cff 60%, #a6d1ff 100%);
            opacity: 0.13;
            border-radius: 50%;
            z-index: 0;
        }
        .upload-title {
            font-size: 2.1rem;
            font-weight: bold;
            color: #23235b;
            text-align: center;
            margin-bottom: 18px;
            letter-spacing: 1px;
            z-index: 1;
            position: relative;
        }
        .upload-info {
            background: linear-gradient(90deg, #eaf3ff 60%, #f4f6fa 100%);
            border-radius: 18px;
            padding: 18px 20px;
            margin-bottom: 22px;
            color: #23235b;
            font-size: 1.08rem;
            box-shadow: 0 2px 8px #4f8cff11;
            width: 100%;
            z-index: 1;
            position: relative;
        }
        .upload-info b {
            font-size: 1.13rem;
            color: #4f8cff;
        }
        .upload-label {
            font-weight: 600;
            color: #23235b;
            margin-bottom: 7px;
            font-size: 1.07rem;
            display: block;
            z-index: 1;
            position: relative;
        }
        .custom-file-input {
            position: relative;
            width: 100%;
            margin-bottom: 18px;
            z-index: 1;
        }
        .custom-file-input input[type="file"] {
            opacity: 0;
            width: 100%;
            height: 48px;
            position: absolute;
            left: 0;
            top: 0;
            cursor: pointer;
        }
        .custom-file-label {
            display: flex;
            align-items: center;
            background: #f4f6fa;
            border: 2px solid #bcd;
            border-radius: 16px;
            padding: 18px 16px;
            font-size: 1.08rem;
            color: #4f8cff;
            width: 100%;
            min-width: 0;
            max-width: 100%;
            min-height: 48px;
            transition: border 0.2s, background 0.2s;
            gap: 10px;
            cursor: pointer;
            box-shadow: 0 1px 6px #4f8cff11;
        }
        .custom-file-label.selected {
            border: 2px solid #4f8cff;
            background: #eaf3ff;
        }
        .custom-file-label .icon {
            margin-right: 10px;
            color: #4f8cff;
            font-size: 1.3rem;
        }
        .upload-note {
            width: 100%;
            min-width: 0;
            max-width: 100%;
            min-height: 48px;
            border-radius: 12px;
            border: 1.5px solid #bcd;
            background: #f4f6fa;
            font-size: 1.03rem;
            padding: 10px 14px;
            margin-bottom: 18px;
            resize: vertical;
            transition: border 0.2s;
        }
        .upload-note:focus {
            border: 1.5px solid #4f8cff;
            outline: none;
        }
        .upload-btn {
            width: 100%;
            padding: 15px 0;
            border-radius: 16px;
            border: none;
            background: linear-gradient(90deg, #4f8cff 60%, #a6d1ff 100%);
            color: #fff;
            font-size: 1.18rem;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 2px 8px #4f8cff22;
            transition: background 0.2s, box-shadow 0.2s;
            margin-top: 10px;
            letter-spacing: 1px;
        }
        .upload-btn:hover {
            background: linear-gradient(90deg, #23235b 60%, #4f8cff 100%);
            box-shadow: 0 4px 16px #4f8cff33;
        }
        .success-msg {
            background: linear-gradient(90deg, #d1e7dd 60%, #b2f2dd 100%);
            color: #0f5132;
            border-radius: 12px;
            padding: 14px 0;
            margin-bottom: 18px;
            font-size: 1.13rem;
            font-weight: bold;
            text-align: center;
            width: 100%;
            box-shadow: 0 2px 8px #b2f2dd44;
            z-index: 2;
            position: relative;
        }
        .error-msg {
            background: linear-gradient(90deg, #f8d7da 60%, #fbeeea 100%);
            color: #842029;
            border-radius: 12px;
            padding: 14px 0;
            margin-bottom: 18px;
            font-size: 1.13rem;
            font-weight: bold;
            text-align: center;
            width: 100%;
            box-shadow: 0 2px 8px #f8d7da44;
            z-index: 2;
            position: relative;
        }
        .back-link {
            display: inline-block;
            margin-top: 12px;
            color: #4f8cff;
            font-size: 1.13rem;
            text-decoration: none;
            transition: color 0.2s;
            font-weight: 500;
            z-index: 1;
            position: relative;
        }
        .back-link:hover {
            color: #23235b;
            text-decoration: underline;
        }
        /* Decorative SVG */
        .decor-svg {
            position: absolute;
            left: -60px;
            bottom: -60px;
            width: 120px;
            opacity: 0.12;
            z-index: 0;
        }
        @media (max-width: 600px) {
            .upload-container {
                padding: 16px 4vw;
                border-radius: 18px;
            }
            .upload-title {
                font-size: 1.3rem;
            }
            .upload-info {
                padding: 10px 6px;
                border-radius: 10px;
            }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(40px);}
            to { opacity: 1; transform: translateY(0);}
        }
    </style>
</head>
<body>
<div class="main-content">
    <div class="upload-container">
        <svg class="decor-svg" viewBox="0 0 120 120" fill="none">
            <circle cx="60" cy="60" r="60" fill="#4f8cff"/>
            <circle cx="60" cy="60" r="40" fill="#a6d1ff"/>
        </svg>
        <div class="upload-title">Upload Tugas</div>
        <?php echo $message; ?>
        <?php if ($task): ?>
            <div class="upload-info">
                <b><?php echo htmlspecialchars($task['task_title']); ?></b><br>
                <span style="color:#23235b;"><b>Deadline:</b> <?php echo htmlspecialchars($task['deadline']); ?></span><br>
                <?php if (!empty($task['notes'])): ?>
                    <div style="margin-top:8px;"><?php echo nl2br(htmlspecialchars($task['notes'])); ?></div>
                <?php endif; ?>
            </div>
            <form method="post" enctype="multipart/form-data" style="width:100%;z-index:2;position:relative;">
                <label class="upload-label" for="file">File Tugas</label>
                <div class="custom-file-input">
                    <label class="custom-file-label" id="fileLabel">
                        <span class="icon">
                            <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M12 16V4m0 0l-4 4m4-4l4 4" stroke="#4f8cff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><rect x="4" y="16" width="16" height="4" rx="2" fill="#e3f0ff"/></svg>
                        </span>
                        <span id="fileName">Pilih file tugas...</span>
                    </label>
                    <input type="file" name="file" id="file" required>
                </div>
                <label class="upload-label" for="notes">Catatan (opsional)</label>
                <textarea class="upload-note" name="notes" id="notes" placeholder="Catatan untuk dosen (opsional)"></textarea>
                <button type="submit" class="upload-btn">Upload</button>
            </form>
        <?php else: ?>
            <div class="error-msg">Belum ada tugas untuk kelas ini.</div>
        <?php endif; ?>
        <a href="student_dashboard.php?menu=kelas" class="back-link">&#8592; Kembali ke Daftar Kelas</a>
    </div>
</div>
<script>
    // Custom file input label
    const fileInput = document.getElementById('file');
    const fileLabel = document.getElementById('fileLabel');
    const fileName = document.getElementById('fileName');
    if(fileInput && fileLabel && fileName) {
        fileInput.addEventListener('change', function() {
            if(this.files && this.files.length > 0) {
                fileName.textContent = this.files[0].name;
                fileLabel.classList.add('selected');
            } else {
                fileName.textContent = "Pilih file tugas...";
                fileLabel.classList.remove('selected');
            }
        }); 
    }
</script>
</body>
</html>