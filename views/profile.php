<?php
/**
 * Halaman profil pengguna.
 *
 * Memuat data akun berdasarkan user_id di session lewat ambil_user_by_id(),
 * lalu menampilkannya pada kartu akun dan form edit. Form mengirim perubahan
 * nama, email, telepon, dan password opsional ke actions/save_profile.php;
 * flash message "user_pesan" dipakai untuk konfirmasi setelah penyimpanan.
 * Wajib login, dan akun yang sudah tidak ada akan diarahkan ke logout.
 */
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../components/landing/layout.php";

// Profil hanya untuk pengguna login; cek_login() mengarahkan ke form login bila perlu.
cek_login();

// Ambil data terbaru dari DB supaya form menampilkan nilai yang tersimpan, bukan hanya session.
$user = ambil_user_by_id($conn, $_SESSION["user_id"]);
// Akun hilang/terhapus: paksa logout agar session lama tidak terus dipakai.
if (!$user) {
    header("Location: /PWL-SAS/actions/logout.php");
    exit;
}

// Flash message dari actions/save_profile.php; dibaca sekali lalu dihapus.
$pesan = $_SESSION["user_pesan"] ?? "";
unset($_SESSION["user_pesan"]);

$activeNav = 'profile';
atas("Profil Saya - Nexatech");
?>
<div class="page-wrap page-wrap-narrow">

    <section class="page-hero reveal" aria-labelledby="profile-title">
        <div class="page-hero-copy">
            <p class="page-eyebrow">My Account</p>
            <h1 id="profile-title" class="page-title">Profil saya</h1>
            <p class="page-subtitle">Kelola informasi akun agar pesanan dan layanan teknisi berjalan lancar.</p>
        </div>
        <span class="page-hero-icon" aria-hidden="true"><i data-lucide="user-round"></i></span>
    </section>

    <?php // Konfirmasi sukses setelah data profil disimpan. ?>
    <?php if ($pesan != "") { ?>
        <div class="th-alert th-alert-success mb-4" role="status">
            <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
            <span><?php echo htmlspecialchars($pesan); ?></span>
        </div>
    <?php } ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- ================= KARTU AKUN ================= -->
        <aside class="lg:col-span-4 th-card th-card-pad">
            <div class="flex flex-col items-center text-center">
                <!-- Inisial dua huruf dari nama pengguna sebagai avatar sederhana. -->
                <span class="flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 text-2xl font-extrabold text-white shadow-lg shadow-brand-600/20">
                    <?php echo htmlspecialchars(strtoupper(substr($user["full_name"], 0, 2))); ?>
                </span>
                <h2 class="mt-3 text-[15px] font-extrabold text-slate-900"><?php echo htmlspecialchars($user["full_name"]); ?></h2>
                <p class="mt-0.5 text-[11px] text-slate-500"><?php echo htmlspecialchars($user["email"]); ?></p>
                <span class="th-badge th-badge-blue mt-2">
                    <i data-lucide="badge-check" class="w-3 h-3"></i>
                    Akun aktif
                </span>
            </div>

            <hr class="th-divider">

            <div class="space-y-2">
                <div class="flex items-center gap-2.5 rounded-xl bg-slate-50 p-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-brand-600 shadow-sm">
                        <i data-lucide="mail" class="w-4 h-4"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[9.5px] font-medium uppercase tracking-wide text-slate-400">Email</p>
                        <p class="truncate text-[11px] font-bold text-slate-800"><?php echo htmlspecialchars($user["email"]); ?></p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 rounded-xl bg-slate-50 p-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-brand-600 shadow-sm">
                        <i data-lucide="phone" class="w-4 h-4"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[9.5px] font-medium uppercase tracking-wide text-slate-400">Telepon</p>
                        <p class="truncate text-[11px] font-bold text-slate-800">
                            <?php echo htmlspecialchars($user["phone_number"] ?: "Belum diisi"); ?>
                        </p>
                    </div>
                </div>
            </div>

            <hr class="th-divider">

            <div class="space-y-1">
                <a href="/PWL-SAS/views/cart.php" class="dropdown-link">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                    Keranjang saya
                    <i data-lucide="chevron-right" class="ml-auto w-3.5 h-3.5"></i>
                </a>
                <a href="/PWL-SAS/pages/technician/" class="dropdown-link">
                    <i data-lucide="calendar-days" class="w-4 h-4"></i>
                    Booking teknisi
                    <i data-lucide="chevron-right" class="ml-auto w-3.5 h-3.5"></i>
                </a>
                <a href="/PWL-SAS/actions/logout.php" class="dropdown-link dropdown-link-danger">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    Keluar dari akun
                </a>
            </div>
        </aside>

        <!-- ================= FORM PROFIL ================= -->
        <section class="lg:col-span-8 th-card th-card-pad">
            <div class="th-card-head">
                <div>
                    <h2 class="th-card-title">Informasi akun</h2>
                    <p class="th-card-sub">Perbarui data diri dan keamanan akun Anda.</p>
                </div>
                <span class="th-badge th-badge-slate"><i data-lucide="lock" class="w-3 h-3"></i> Privat</span>
            </div>

            <form action="/PWL-SAS/actions/save_profile.php" method="POST" class="th-form-grid">
                <!-- Nilai awal tiap input diambil dari $user agar pengguna hanya mengubah yang perlu. -->
                <div class="th-field-full">
                    <label for="nama" class="th-label"><i data-lucide="user-round"></i> Nama lengkap</label>
                    <input type="text" id="nama" name="nama" required
                           value="<?php echo htmlspecialchars($user["full_name"]); ?>"
                           class="th-input" placeholder="Nama lengkap">
                </div>

                <div>
                    <label for="email" class="th-label"><i data-lucide="mail"></i> Email</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($user["email"]); ?>"
                           class="th-input" placeholder="nama@email.com">
                </div>

                <div>
                    <label for="telepon" class="th-label"><i data-lucide="phone"></i> Telepon</label>
                    <input type="text" id="telepon" name="telepon"
                           value="<?php echo htmlspecialchars($user["phone_number"] ?? ""); ?>"
                           class="th-input" placeholder="08xxxxxxxxxx">
                </div>

                <div class="th-field-full">
                    <label for="password" class="th-label"><i data-lucide="key-round"></i> Password baru</label>
                    <!-- Field password sengaja kosong: hanya diproses action bila diisi. -->
                    <input type="password" id="password" name="password"
                           class="th-input" placeholder="Kosongkan kalau tidak ingin mengganti password"
                           autocomplete="new-password">
                    <p class="mt-1.5 text-[10px] text-slate-400">Gunakan minimal 8 karakter agar akun lebih aman.</p>
                </div>

                <div class="th-field-full flex flex-col gap-2 pt-1 sm:flex-row">
                    <button type="submit" class="th-btn th-btn-primary sm:min-w-44">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Simpan perubahan
                    </button>
                    <a href="/PWL-SAS/mainpage.php" class="th-btn th-btn-outline">Kembali ke beranda</a>
                </div>
            </form>

            <div class="mt-5 flex items-start gap-2 rounded-xl border border-brand-100 bg-brand-50/60 p-3 text-[10.5px] leading-relaxed text-slate-600">
                <i data-lucide="shield-check" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600"></i>
                <span>Data akun Anda hanya digunakan untuk memproses pesanan dan layanan teknisi.</span>
            </div>
        </section>
    </div>
</div>
<?php
bawah();
mysqli_close($conn);
?>
