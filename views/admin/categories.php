<?php
/**
 * Halaman kelola kategori (admin).
 *
 * Menampilkan kartu semua kategori beserta ikon Lucide dan deskripsinya. Form di
 * kanan menambah kategori baru atau mengedit kategori melalui ?edit=, lalu
 * menyimpannya ke actions/save_category.php. Penghapusan memakai
 * actions/delete_category.php dengan konfirmasi. Kategori yang dibuat di sini
 * langsung tersedia sebagai pilihan pada form produk.
 */
session_start();
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../repositories/categories.php";
require_once __DIR__ . "/../../repositories/auth.php";
require_once __DIR__ . "/../../components/admin/layout_admin.php";

// Halaman ini hanya untuk admin.
require_admin();

// Kategori dipakai untuk daftar kartu sekaligus dropdown/relasi pada produk.
$categories = get_categories($conn);

$edit = null;
// ?edit=<category_id> mengisi form kanan dengan data kategori yang akan diubah.
if (isset($_GET["edit"])) {
    $edit = get_category_by_id($conn, $_GET["edit"]);
}

$pesan = $_SESSION["admin_pesan"] ?? "";
unset($_SESSION["admin_pesan"]);

admin_start("Kategori", "Tambah kategori baru, kategori langsung bisa dipilih di form produk.", "categories.php");
?>
<?php if ($pesan != "") { ?>
    <!-- Konfirmasi hasil simpan/hapus kategori dari action. -->
    <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm rounded-2xl p-4">
        <?php echo htmlspecialchars($pesan); ?>
    </div>
<?php } ?>
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
    <div class="xl:col-span-8 bg-white rounded-2xl border border-slate-100 p-5">
        <h2 class="font-bold text-slate-900 mb-4">Daftar kategori (<?php echo count($categories); ?>)</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php if (count($categories) == 0) { ?>
                <!-- Empty state bila belum ada kategori sama sekali. -->
                <p class="text-slate-400 text-sm">Belum ada kategori.</p>
            <?php } ?>
            <?php foreach ($categories as $c) { ?>
                <!-- Kartu kategori: ikon Lucide, nama, deskripsi, lalu aksi edit/hapus. -->
                <div class="border border-slate-100 rounded-2xl p-4 flex gap-3 items-start">
                    <div class="w-10 h-10 bg-brand-50 text-brand-600 rounded-xl flex items-center justify-center shrink-0">
                        <?php // Jika kolom image kosong, pakai ikon "tag" sebagai fallback. ?>
                        <i data-lucide="<?php echo htmlspecialchars($c["image"] ?: "tag"); ?>" class="w-4 h-4"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-sm text-slate-900"><?php echo htmlspecialchars($c["category_name"]); ?></p>
                        <p class="text-[11px] text-slate-500 line-clamp-2"><?php echo htmlspecialchars($c["cat_description"] ?? "-"); ?></p>
                        <div class="flex gap-2 mt-3">
                            <!-- Edit membuka form dengan data kategori; hapus meminta konfirmasi dulu. -->
                            <a href="categories.php?edit=<?php echo $c["category_id"]; ?>" class="bg-brand-50 text-brand-600 text-[11px] font-semibold px-3 py-1.5 rounded-lg">Edit</a>
                            <a href="/PWL-SAS/actions/delete_category.php?id=<?php echo $c["category_id"]; ?>" onclick="return confirm('Hapus kategori ini?')" class="bg-red-50 text-red-600 text-[11px] font-semibold px-3 py-1.5 rounded-lg">Hapus</a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <form action="/PWL-SAS/actions/save_category.php" method="POST" class="xl:col-span-4 bg-white rounded-2xl border border-slate-100 p-5 space-y-4">
        <!-- Form berfungsi ganda: tambah kategori baru atau simpan perubahan saat $edit terisi. -->
        <h2 class="font-bold text-slate-900"><?php echo $edit ? "Edit kategori" : "Tambah kategori"; ?></h2>
        <?php if ($edit) { ?>
            <!-- category_id hanya diperlukan saat update. -->
            <input type="hidden" name="category_id" value="<?php echo $edit["category_id"]; ?>">
        <?php } ?>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Nama kategori</label>
            <input type="text" name="category_name" required value="<?php echo htmlspecialchars($edit["category_name"] ?? ""); ?>"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Icon (nama lucide)</label>
            <input type="text" name="image" placeholder="box" value="<?php echo htmlspecialchars($edit["image"] ?? ""); ?>"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Deskripsi</label>
            <textarea name="cat_description" rows="3" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500"><?php echo htmlspecialchars($edit["cat_description"] ?? ""); ?></textarea>
        </div>
        <button class="w-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold py-3 rounded-xl">
            <?php echo $edit ? "Simpan perubahan" : "Tambah kategori"; ?>
        </button>
        <?php if ($edit) { ?>
            <!-- Batal membuang mode edit dan kembali ke form tambah. -->
            <a href="categories.php" class="block text-center border border-slate-300 text-slate-700 text-sm font-medium py-3 rounded-xl">Batal</a>
        <?php } ?>
    </form>
</div>
<?php admin_end(); ?>


