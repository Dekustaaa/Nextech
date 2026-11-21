<?php
/**
 * Halaman profil admin.
 *
 * Memuat akun admin yang sedang login dari session lalu menampilkan form untuk
 * mengganti nama, email, telepon, dan password. Form mengirim data ke
 * actions/save_profile.php, dan pesan "admin_pesan" tampil setelah berhasil.
 * Hanya dapat diakses setelah require_admin().
 */
session_start();
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../repositories/auth.php";
require_once __DIR__ . "/../../components/admin/layout_admin.php";

// Profil admin hanya bisa diakses setelah require_admin().
require_admin();

// Ambil data terbaru admin yang sedang login berdasarkan session user_id.
$admin = get_user_by_id($conn, $_SESSION["user_id"]);

// Flash message setelah actions/save_profile.php menyimpan perubahan.
$pesan = $_SESSION["admin_pesan"] ?? "";
unset($_SESSION["admin_pesan"]);

admin_start("Profil", "Ganti nama, email, telepon, dan password akun admin.", "profile.php");
?>
<?php if ($pesan != "") { ?>
    <!-- Konfirmasi perubahan profil admin. -->
    <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm rounded-2xl p-4">
        <?php echo htmlspecialchars($pesan); ?>
    </div>
<?php } ?>
<form action="/PWL-SAS/actions/save_profile.php" method="POST" class="max-w-xl bg-white rounded-2xl border border-slate-100 p-5 space-y-4">
    <!-- Form yang sama dipakai user dan admin; action membedakan lewat role di session. -->
    <div>
        <label class="block text-xs text-slate-500 mb-1">Nama lengkap</label>
        <input type="text" name="nama" required value="<?php echo htmlspecialchars($admin["full_name"]); ?>"
               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Email</label>
        <input type="email" name="email" required value="<?php echo htmlspecialchars($admin["email"]); ?>"
               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Telepon</label>
        <input type="text" name="telepon" value="<?php echo htmlspecialchars($admin["phone_number"] ?? ""); ?>"
               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">Password baru (kosongkan kalau tidak ganti)</label>
        <!-- Password dikosongkan secara default; action hanya memperbarui bila field diisi. -->
        <input type="password" name="password"
               class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
    </div>
    <button class="w-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold py-3 rounded-xl">Simpan</button>
</form>
<?php admin_end(); ?>


