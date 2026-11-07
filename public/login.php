<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../src/controllers/AuthController.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $authController = new AuthController();
    $result = $authController->login($username, $password);

    if (is_string($result)) {
        $message = $result;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4f8cff">
    <title>Login | Task Submission Platform</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <aside class="auth-visual">
            <a class="auth-brand" href="login.php">
                <span class="auth-brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path
                            d="M5 4.75h11.5A2.5 2.5 0 0 1 19 7.25v12H7.5A2.5 2.5 0 0 0 5 21.75v-17Z"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M5 18.5A2.5 2.5 0 0 1 7.5 16H19M8.5 8.5h7M8.5 11.5h7"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        />
                    </svg>
                </span>
                <span>Task Submission Platform</span>
            </a>

            <div class="auth-visual-copy">
                <p class="eyebrow">Belajar lebih teratur</p>
                <h1>Semua tugas dan kelas dalam satu tempat.</h1>
                <p>
                    Masuk untuk melanjutkan aktivitas belajar, mengakses kelas,
                    dan mengelola tugas dengan mudah.
                </p>
            </div>

            <div class="auth-illustration" aria-hidden="true">
                <svg viewBox="0 0 250 210" fill="none">
                    <circle cx="125" cy="105" r="87" fill="#4F8CFF" fill-opacity=".12"/>
                    <rect x="48" y="146" width="154" height="9" rx="4.5" fill="#BDBDFC"/>
                    <rect x="137" y="111" width="45" height="27" rx="7" fill="#4F8CFF"/>
                    <rect x="145" y="119" width="29" height="11" rx="3" fill="white"/>
                    <rect x="69" y="65" width="55" height="68" rx="10" fill="white"/>
                    <rect x="79" y="78" width="35" height="6" rx="3" fill="#4F8CFF"/>
                    <rect x="79" y="91" width="35" height="6" rx="3" fill="#BDBDFC"/>
                    <rect x="79" y="104" width="26" height="6" rx="3" fill="#BDBDFC"/>
                    <path d="M91 125v-22l9 9 9-9v22" fill="#4F8CFF"/>
                    <rect x="157" y="52" width="39" height="39" rx="10" fill="white"/>
                    <path
                        d="m166 71 8 8 14-16"
                        stroke="#4F8CFF"
                        stroke-width="4"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <circle cx="137" cy="50" r="20" fill="white"/>
                    <circle cx="137" cy="50" r="15" fill="#4F8CFF"/>
                    <path
                        d="M137 41v10l7 5"
                        stroke="white"
                        stroke-width="3"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>

            <div class="auth-visual-footer">
                <span class="auth-footer-dot"></span>
                <span>Platform pembelajaran untuk siswa dan dosen</span>
            </div>
        </aside>

        <section class="auth-panel">
            <div class="auth-content">
                <header class="auth-heading">
                    <p class="auth-kicker">Selamat datang kembali</p>
                    <h2>Login ke akun Anda</h2>
                    <p>Masukkan username dan password untuk melanjutkan.</p>
                </header>

                <?php if ($message !== ''): ?>
                    <div class="auth-message error" role="alert">
                        <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form class="auth-form" method="post" action="login.php" autocomplete="on">
                    <div class="auth-field">
                        <label for="username">Username</label>
                        <input
                            class="auth-input"
                            type="text"
                            id="username"
                            name="username"
                            placeholder="Masukkan username"
                            autocomplete="username"
                            autocapitalize="none"
                            spellcheck="false"
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
                                placeholder="Masukkan password"
                                autocomplete="current-password"
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
                                    <path
                                        d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    />
                                    <circle
                                        cx="12"
                                        cy="12"
                                        r="3"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="auth-options">
                        <a class="auth-link" href="forgot_password.php">Lupa password?</a>
                    </div>

                    <button class="auth-button" type="submit">Masuk</button>
                </form>

                <p class="auth-bottom">
                    Belum punya akun?
                    <a href="register.php">Daftar sekarang</a>
                </p>
            </div>
        </section>
    </main>

    <script>
        document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = document.getElementById(button.dataset.togglePassword);

                if (!input) {
                    return;
                }

                var showPassword = input.type === 'password';
                input.type = showPassword ? 'text' : 'password';
                button.setAttribute('aria-pressed', String(showPassword));
                button.setAttribute(
                    'aria-label',
                    showPassword ? 'Sembunyikan password' : 'Tampilkan password'
                );
            });
        });
    </script>
</body>
</html>