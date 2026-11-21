<?php
/**
 * Halaman login pengguna biasa.
 *
 * Menampilkan form masuk memakai layout landing (components/landing/layout.php).
 * Data yang dimuat hanya flash message "auth_pesan" dari session, yang diisi
 * oleh actions/login.php ketika kredensial salah. Form mengirim email, password,
 * dan penanda asal=user; actions/login.php yang memvalidasi lalu mengarahkan
 * pengguna ke halaman tujuan.
 */
session_start();
require_once __DIR__ . "/../../components/landing/layout.php";

// Flash message auth dibaca sekali, lalu dihapus agar tidak muncul lagi saat refresh.
$pesan = $_SESSION["auth_pesan"] ?? "";
unset($_SESSION["auth_pesan"]);

// Halaman auth tidak menyorot menu navigasi tertentu di layout landing.
$activeNav = '';
atas("Masuk - Nexatech");
?>
<div class="auth-shell">
    <div class="auth-card">
        <aside class="auth-visual">
            <div class="auth-visual-content">
                <span class="auth-visual-badge">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                    Nexatech Account
                </span>
                <h1 class="auth-visual-title">Satu akun untuk semua kebutuhan teknologi.</h1>
                <p class="auth-visual-copy">
                    Belanja produk, pantau pesanan, dan pesan teknisi dengan pengalaman yang lebih cepat dan aman.
                </p>

                <div class="auth-benefits">
                    <div class="auth-benefit">
                        <span class="auth-benefit-icon"><i data-lucide="shopping-bag" class="w-4 h-4"></i></span>
                        Belanja produk teknologi pilihan
                    </div>
                    <div class="auth-benefit">
                        <span class="auth-benefit-icon"><i data-lucide="wrench" class="w-4 h-4"></i></span>
                        Booking teknisi terverifikasi
                    </div>
                    <div class="auth-benefit">
                        <span class="auth-benefit-icon"><i data-lucide="package-check" class="w-4 h-4"></i></span>
                        Pantau pesanan dalam satu tempat
                    </div>
                </div>
            </div>

            <div class="auth-visual-footer">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                Nexatech 2.0 &mdash; teknologi untuk ide besarmu.
            </div>
        </aside>

        <section class="auth-form">
            <div class="auth-form-inner">
                <div class="auth-brand">
                    <span class="brand-mark">
                        <i data-lucide="wrench" class="w-[19px] h-[19px]"></i>
                    </span>
                    <span class="brand-text">Nexatech<span>.</span></span>
                </div>

                <h2 class="auth-title">Selamat datang kembali</h2>
                <p class="auth-subtitle">Masuk untuk melanjutkan belanja dan mengelola pesanan Anda.</p>

                <?php // Pesan error dari actions/login.php di-escape agar aman ditampilkan sebagai HTML. ?>
                <?php if ($pesan != "") { ?>
                    <div class="th-alert th-alert-danger mb-4" role="alert">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                        <span><?php echo htmlspecialchars($pesan); ?></span>
                    </div>
                <?php } ?>

                <!-- Form login user: asal=user membedakan redirect tujuan dari alur login admin. -->
                <form action="/PWL-SAS/actions/login.php" method="POST" class="space-y-3.5">
                    <input type="hidden" name="asal" value="user">

                    <div>
                        <label for="email" class="th-label"><i data-lucide="mail"></i> Email</label>
                        <input type="email" id="email" name="email" required placeholder="nama@email.com"
                               class="th-input" autocomplete="email">
                    </div>

                    <div>
                        <label for="password" class="th-label"><i data-lucide="key-round"></i> Password</label>
                        <input type="password" id="password" name="password" required placeholder="Masukkan password"
                               class="th-input" autocomplete="current-password">
                    </div>

                    <button type="submit" class="th-btn th-btn-primary th-btn-full !mt-4">
                        <i data-lucide="log-in" class="w-4 h-4"></i>
                        Masuk
                    </button>
                </form>

                <p class="auth-switch">Belum punya akun? <a href="/PWL-SAS/views/auth/register.php">Daftar sekarang</a></p>
                <a href="/PWL-SAS/mainpage.php" class="auth-back">Kembali ke beranda</a>
            </div>
        </section>
    </div>
</div>
<?php bawah(); ?>
