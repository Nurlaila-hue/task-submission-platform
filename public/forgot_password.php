<?php
require_once '../src/controllers/AuthController.php';

$message = '';
$messageType = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';

    $authController = new AuthController();
    $userModel = $authController->getUserModel();
    $user = $userModel->findByUsernameOrEmail($username, $email);

    if (
        $user &&
        $user['username'] === $username &&
        $user['email'] === $email
    ) {
        if ($userModel->updatePassword($user['id'], $newPassword)) {
            $success = true;
            $message = 'Password berhasil diubah. Silakan login dengan password baru.';
            $messageType = 'success';
        } else {
            $message = 'Gagal mengubah password. Silakan coba lagi.';
            $messageType = 'error';
        }
    } else {
        $message = 'Username dan email tidak cocok.';
        $messageType = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password | Task Submission Platform</title>
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
                <p class="eyebrow">Pemulihan akun</p>
                <h1>Atur ulang password dan lanjutkan belajar.</h1>
                <p>Masukkan username dan email terdaftar untuk membuat password baru.</p>
            </div>

            <div class="auth-illustration" aria-hidden="true">
                <svg viewBox="0 0 250 210" fill="none">
                    <circle cx="125" cy="105" r="87" fill="#4F8CFF" fill-opacity=".12"/>
                    <rect x="64" y="75" width="112" height="78" rx="13" fill="white"/>
                    <path d="M86 75V61a34 34 0 0 1 68 0v14" stroke="#A6D1FF" stroke-width="11" stroke-linecap="round"/>
                    <rect x="86" y="99" width="68" height="38" rx="9" fill="#EEF4FF"/>
                    <circle cx="120" cy="117" r="7" fill="#4F8CFF"/>
                    <path d="M120 123v7" stroke="#4F8CFF" stroke-width="4" stroke-linecap="round"/>
                    <circle cx="178" cy="63" r="23" fill="#4F8CFF"/>
                    <path d="M178 51v12l8 5" stroke="white" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <rect x="54" y="161" width="142" height="8" rx="4" fill="#BDBDFC"/>
                </svg>
            </div>

            <div class="auth-visual-footer">
                <span class="auth-footer-dot"></span>
                Lindungi akun dengan password yang kuat
            </div>
        </aside>

        <section class="auth-panel">
            <div class="auth-content">
                <div class="auth-heading">
                    <p class="auth-kicker">Tidak bisa masuk?</p>
                    <h2>Atur ulang password</h2>
                    <p>Verifikasi akun Anda, lalu masukkan password baru.</p>
                </div>

                <?php if ($message !== ''): ?>
                    <div class="auth-message <?php echo htmlspecialchars($messageType, ENT_QUOTES, 'UTF-8'); ?>" role="alert">
                        <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <?php if (!$success): ?>
                    <form class="auth-form" method="POST" autocomplete="on">
                        <div class="auth-field">
                            <label for="username">Username</label>
                            <input
                                class="auth-input"
                                type="text"
                                id="username"
                                name="username"
                                placeholder="Masukkan username"
                                autocomplete="username"
                                required
                            >
                        </div>

                        <div class="auth-field">
                            <label for="email">Email terdaftar</label>
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
                            <label for="new_password">Password baru</label>
                            <div class="auth-input-wrap">
                                <input
                                    class="auth-input has-toggle"
                                    type="password"
                                    id="new_password"
                                    name="new_password"
                                    placeholder="Masukkan password baru"
                                    autocomplete="new-password"
                                    required
                                >
                                <button
                                    class="password-toggle"
                                    type="button"
                                    data-toggle-password="new_password"
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

                        <button class="auth-button" type="submit">Simpan Password Baru</button>
                    </form>
                <?php endif; ?>

                <a class="auth-back" href="login.php">← Kembali ke Login</a>
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
