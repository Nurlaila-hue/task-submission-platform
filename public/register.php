<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../src/controllers/AuthController.php';

$message = '';
$messageType = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';

    if (!in_array($role, ['student', 'lecturer'], true)) {
        $message = 'Pilihan role tidak valid.';
        $messageType = 'error';
    } else {
        $authController = new AuthController();
        $result = $authController->register($username, $password, $role, $email);

        if ($result === true) {
            $success = true;
            $message = 'Registrasi berhasil! Silakan login dengan akun Anda.';
            $messageType = 'success';
        } elseif ($result === 'exists') {
            $message = 'Username atau email sudah terdaftar. Silakan login atau gunakan data lain.';
            $messageType = 'error';
        } else {
            $message = 'Registrasi gagal. Silakan periksa data dan coba lagi.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar | Task Submission Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <aside class="auth-visual">
            <a class="auth-brand" href="login.php">
                <span class="auth-brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M5 4.75h11.5A2.5 2.5 0 0 1 19 7.25v12H7.5A2.5 2.5 0 0 0 5 21.75v-17Z"
                              stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                        <path d="M5 18.5A2.5 2.5 0 0 1 7.5 16H19M8.5 8.5h7M8.5 11.5h7"
                              stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </span>
                Task Submission Platform
            </a>

            <div class="auth-visual-copy">
                <p class="eyebrow">Mulai perjalanan belajar</p>
                <h1>Buat akun dan bergabung dengan kelas Anda.</h1>
                <p>Daftar sebagai siswa atau dosen untuk mulai menggunakan platform pembelajaran.</p>
            </div>

            <div class="auth-illustration" aria-hidden="true">
                <svg viewBox="0 0 250 210" fill="none">
                    <circle cx="125" cy="105" r="87" fill="#4F8CFF" fill-opacity=".12"/>
                    <rect x="49" y="138" width="152" height="10" rx="5" fill="#BDBDFC"/>
                    <rect x="67" y="63" width="91" height="70" rx="11" fill="white"/>
                    <rect x="79" y="77" width="67" height="7" rx="3.5" fill="#4F8CFF"/>
                    <rect x="79" y="92" width="48" height="6" rx="3" fill="#BDBDFC"/>
                    <rect x="79" y="105" width="58" height="6" rx="3" fill="#BDBDFC"/>
                    <circle cx="170" cy="111" r="28" fill="#4F8CFF"/>
                    <path d="M170 97v28M156 111h28" stroke="white" stroke-width="5" stroke-linecap="round"/>
                    <rect x="83" y="44" width="42" height="14" rx="7" fill="#BDBDFC"/>
                    <circle cx="63" cy="51" r="10" fill="#4F8CFF"/>
                    <path d="M59 51h8M63 47v8" stroke="white" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>

            <div class="auth-visual-footer">
                <span class="auth-footer-dot"></span>
                Satu akun untuk aktivitas belajar Anda
            </div>
        </aside>

        <section class="auth-panel">
            <div class="auth-content">
                <div class="auth-heading">
                    <p class="auth-kicker">Buat akun baru</p>
                    <h2>Daftar sekarang</h2>
                    <p>Lengkapi informasi berikut untuk membuat akun.</p>
                </div>

                <?php if ($message !== ''): ?>
                    <div class="auth-message <?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?>" role="alert">
                        <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if (!$success): ?>
                    <form class="auth-form" method="POST" autocomplete="on">
                        <div class="auth-field">
                            <label for="email">Email</label>
                            <input
                                class="auth-input"
                                type="email"
                                id="email"
                                name="email"
                                placeholder="nama@email.com"
                                autocomplete="email"
                                required
                            >
                        </div>

                        <div class="auth-field">
                            <label for="username">Username</label>
                            <input
                                class="auth-input"
                                type="text"
                                id="username"
                                name="username"
                                placeholder="Buat username"
                                autocomplete="username"
                                required
                            >
                        </div>

                        <div class="auth-field">
                            <label for="password">Password</label>
                            <div class="auth-input-wrap">
                                <input
                                    class="auth-input has-toggle"
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Buat password"
                                    autocomplete="new-password"
                                    required
                                >
                                <button
                                    class="password-toggle"
                                    type="button"
                                    data-toggle-password="password"
                                    aria-label="Tampilkan password"
                                    aria-pressed="false"
                                >
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                        <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"
                                              stroke="currentColor" stroke-width="1.8"/>
                                        <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="auth-field">
                            <label for="role">Daftar sebagai</label>
                            <div class="auth-input-wrap select-wrap">
                                <select class="auth-input auth-select" id="role" name="role" required>
                                    <option value="student">Siswa</option>
                                    <option value="lecturer">Dosen</option>
                                </select>
                            </div>
                        </div>

                        <button class="auth-button" type="submit">Buat Akun</button>
                    </form>
                <?php endif; ?>

                <p class="auth-bottom">
                    Sudah punya akun?
                    <a href="login.php">Login</a>
                </p>
            </div>
        </section>
    </main>

    <script>
        document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = document.getElementById(button.dataset.togglePassword);
                var isVisible = input.type === 'text';

                input.type = isVisible ? 'password' : 'text';
                button.setAttribute('aria-pressed', String(!isVisible));
                button.setAttribute(
                    'aria-label',
                    isVisible ? 'Tampilkan password' : 'Sembunyikan password'
                );
            });
        });
    </script>
</body>
</html>
