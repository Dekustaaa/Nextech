<?php
/**
 * Halaman pendaftaran akun pengguna baru.
 *
 * Memakai layout landing dan hanya memuat flash message "auth_pesan" dari
 * session untuk menampilkan error validasi dari actions/register.php.
 * Form mengirim nama, email, dan password; proses pembuatan akun sekaligus
 * redirect setelah berhasil ditangani oleh action tersebut.
 */
session_start();
require_once __DIR__ . "/../../components/landing/layout.php";

// Pesan error pendaftaran hanya ditampilkan sekali setelah redirect dari action.
$pesan = $_SESSION["auth_pesan"] ?? "";
unset($_SESSION["auth_pesan"]);

// Halaman auth tidak menyorot menu navigasi tertentu di layout landing.
$activeNav = '';
atas("Daftar - Nexatech");
?>
<div class="auth-shell">
    <div class="auth-card">
        <aside class="auth-visual">
            <div class="auth-visual-content">
                <span class="auth-visual-badge">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                    Join Nexatech
                </span>
                <h1 class="auth-visual-title">Mulai perjalanan teknologi Anda bersama Nexatech.</h1>
                <p class="auth-visual-copy">
                    Buat akun gratis untuk berbelanja, menyimpan riwayat pesanan, dan memesan teknisi kapan saja.
                </p>

                <div class="auth-benefits">
                    <div class="auth-benefit">
                        <span class="auth-benefit-icon"><i data-lucide="gift" class="w-4 h-4"></i></span>
                        Promo dan penawaran spesial
                    </div>
                    <div class="auth-benefit">
                        <span class="auth-benefit-icon"><i data-lucide="history" class="w-4 h-4"></i></span>
                        Riwayat pesanan tersimpan
                    </div>
                    <div class="auth-benefit">
                        <span class="auth-benefit-icon"><i data-lucide="headphones" class="w-4 h-4"></i></span>
                        Bantuan pelanggan 24/7
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

                <h2 class="auth-title">Buat akun baru</h2>
                <p class="auth-subtitle">Daftar gratis dan mulai gunakan semua layanan Nexatech.</p>

                <?php // Pesan error validasi pendaftaran dari actions/register.php, ditampilkan sekali. ?>
                <?php if ($pesan != "") { ?>
                    <div class="th-alert th-alert-danger mb-4" role="alert">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                        <span><?php echo htmlspecialchars($pesan); ?></span>
                    </div>
                <?php } ?>

                <!-- Form pendaftaran: akun baru diproses dan langsung diarahkan oleh actions/register.php. -->
                <form action="/PWL-SAS/actions/register.php" method="POST" class="space-y-3.5">
                    <div>
                        <label for="nama" class="th-label"><i data-lucide="user-round"></i> Nama lengkap</label>
                        <input type="text" id="nama" name="nama" required placeholder="Nama kamu"
                               class="th-input" autocomplete="name">
                    </div>

                    <div>
                        <label for="email" class="th-label"><i data-lucide="mail"></i> Email</label>
                        <input type="email" id="email" name="email" required placeholder="nama@email.com"
                               class="th-input" autocomplete="email">
                    </div>

                    <div>
                        <label for="password" class="th-label"><i data-lucide="key-round"></i> Password</label>
                        <input type="password" id="password" name="password" required placeholder="Minimal 8 karakter"
                               class="th-input" autocomplete="new-password">
                    </div>

                    <button type="submit" class="th-btn th-btn-primary th-btn-full !mt-4">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        Daftar
                    </button>
                </form>

                <p class="auth-switch">Sudah punya akun? <a href="/PWL-SAS/views/auth/login.php">Masuk di sini</a></p>
                <a href="/PWL-SAS/mainpage.php" class="auth-back">Kembali ke beranda</a>
            </div>
        </section>
    </div>
</div>
<?php bawah(); ?>
