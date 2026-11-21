<?php
/**
 * login.php (admin) — halaman masuk khusus administrator.
 * Markup dan CSS-nya sengaja dipisah dari login user agar alur admin jelas:
 * form ini mengirim parameter asal=admin ke actions/login.php.
 */
session_start();

// Pesan gagal login disimpan sementara di session, lalu dihapus setelah dibaca.
$pesan = $_SESSION["auth_pesan"] ?? "";
unset($_SESSION["auth_pesan"]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Login Admin - Nexatech</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Aset admin dipakai bersama oleh layout dan halaman login. -->
    <script src="/PWL-SAS/assets/js/i18n.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/i18n.js'); ?>"></script>
    <script src="/PWL-SAS/assets/js/admin.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/admin.js'); ?>"></script>
    <link rel="stylesheet" href="/PWL-SAS/assets/css/admin.css?v=<?php echo filemtime(__DIR__ . '/../../assets/css/admin.css'); ?>">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="admin-login-page">
    <!-- Kontrol tampilan tetap di pojok kanan atas halaman login admin. -->
    <div class="admin-login-controls">
        <button type="button" class="admin-lang-toggle" data-lang-toggle aria-label="Ganti bahasa" title="Ganti bahasa">
            <i data-lucide="languages" class="w-[17px] h-[17px]"></i>
            <span class="admin-lang-label" data-lang-label data-i18n-skip>ID</span>
        </button>
        <button type="button" id="admin-theme-toggle" class="admin-theme-toggle" data-admin-theme-toggle aria-label="Aktifkan mode gelap" aria-pressed="false" title="Mode gelap">
            <i data-lucide="moon" class="admin-theme-icon admin-theme-icon-moon"></i>
            <i data-lucide="sun" class="admin-theme-icon admin-theme-icon-sun"></i>
        </button>
    </div>

    <div class="admin-login-card">
        <!-- Panel kiri: branding dan ringkasan manfaat admin panel. -->
        <aside class="admin-login-visual">
            <a href="/PWL-SAS/mainpage.php" class="admin-login-brand">
                <span class="admin-login-brand-mark">
                    <i data-lucide="wrench"></i>
                </span>
                <span class="admin-login-brand-text">Nexatech<span>.</span></span>
            </a>

            <div class="admin-login-visual-copy">
                <span class="admin-login-kicker">
                    <i data-lucide="shield-check"></i>
                    Admin Panel
                </span>
                <h1>Kelola toko dalam satu dashboard.</h1>
                <p>Pantau pesanan, produk, booking teknisi, dan pelanggan Nexatech dengan tampilan yang rapi dan terintegrasi.</p>
            </div>

            <div class="admin-login-points">
                <span class="admin-login-point">
                    <i data-lucide="check-circle-2"></i>
                    Produk & kategori terpusat
                </span>
                <span class="admin-login-point">
                    <i data-lucide="check-circle-2"></i>
                    Pesanan dan booking real-time
                </span>
                <span class="admin-login-point">
                    <i data-lucide="check-circle-2"></i>
                    Data pelanggan lebih terukur
                </span>
            </div>
        </aside>

        <!-- Panel kanan: form login; asal=admin dipakai actions/login.php
             untuk mengarahkan ke dashboard admin, bukan halaman user. -->
        <main class="admin-login-form">
            <!-- Sambutan singkat sebelum form; pesan error muncul tepat di bawah blok ini. -->
            <div class="admin-login-form-head">
                <p>Selamat datang kembali</p>
                <h2>Login Admin</h2>
                <span>Masuk dengan akun admin untuk melanjutkan ke dashboard.</span>
            </div>

            <?php // Pesan gagal login dari actions/login.php (sudah di-escape sebelum ditampilkan). ?>
            <?php if ($pesan != "") { ?>
                <div class="admin-login-alert" role="alert">
                    <?php echo htmlspecialchars($pesan); ?>
                </div>
            <?php } ?>

            <!-- Form admin: hidden asal=admin menentukan redirect ke dashboard admin setelah sukses. -->
            <form action="/PWL-SAS/actions/login.php" method="POST">
                <input type="hidden" name="asal" value="admin">

                <div class="admin-login-field">
                    <label for="admin-email">Email admin</label>
                    <div class="admin-login-input-wrap">
                        <i data-lucide="mail"></i>
                        <input id="admin-email" type="email" name="email" required autocomplete="email" placeholder="admin@nexatech.id">
                    </div>
                </div>

                <div class="admin-login-field">
                    <label for="admin-password">Password</label>
                    <div class="admin-login-input-wrap">
                        <i data-lucide="lock-keyhole"></i>
                        <input id="admin-password" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password">
                    </div>
                </div>

                <button type="submit" class="admin-login-submit">
                    <i data-lucide="log-in"></i>
                    Masuk dashboard
                </button>
            </form>

            <p class="admin-login-foot">
                Bukan admin?
                <a href="/PWL-SAS/views/auth/login.php">Login user</a>
            </p>
        </main>
    </div>

    <?php // Inisialisasi ikon dan preferensi tema ditangani assets/js/admin.js. ?>
</body>
</html>
