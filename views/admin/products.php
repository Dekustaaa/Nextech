<?php
/**
 * Halaman kelola produk (admin).
 *
 * Menampilkan tabel semua produk beserta kategori, harga, stok, dan rating
 * agregat. Form di kanan dipakai untuk menambah produk baru atau mengedit produk
 * yang dipilih lewat ?edit=, lalu dikirim ke actions/save_product.php. Hapus
 * produk diarahkan ke actions/delete_product.php dengan konfirmasi JavaScript.
 * Flash message "admin_pesan" menandakan operasi simpan/hapus berhasil.
 */
session_start();
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../repositories/products.php";
require_once __DIR__ . "/../../repositories/categories.php";
require_once __DIR__ . "/../../repositories/ratings.php";
require_once __DIR__ . "/../../repositories/auth.php";
require_once __DIR__ . "/../../components/admin/layout_admin.php";

// Hanya admin yang boleh mengelola katalog.
require_admin();

// Data awal: seluruh produk, kategori untuk dropdown, dan rating agregat per produk.
$products = get_products($conn);
$categories = get_categories($conn);
$rating_produk = get_all_ratings($conn);

$edit = null;
// Mode edit aktif bila URL membawa ?edit=<product_id>; form kanan akan terisi data produk tersebut.
if (isset($_GET["edit"])) {
    $edit = get_product_by_id($conn, $_GET["edit"]);
}

// Flash message dari actions/save_product.php atau delete_product.php.
$pesan = $_SESSION["admin_pesan"] ?? "";
unset($_SESSION["admin_pesan"]);

admin_start("Produk", "Tambah, ubah, dan hapus produk. Kategori diambil dari halaman kategori.", "products.php");
?>
<?php if ($pesan != "") { ?>
    <!-- Notifikasi hasil simpan/hapus, sudah di-escape sebelum ditampilkan. -->
    <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm rounded-2xl p-4">
        <?php echo htmlspecialchars($pesan); ?>
    </div>
<?php } ?>
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
    <div class="xl:col-span-8 bg-white rounded-2xl border border-slate-100 p-5">
        <h2 class="font-bold text-slate-900 mb-4">Daftar produk (<?php echo count($products); ?>)</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left text-slate-500 border-b border-slate-100">
                        <th class="py-2 font-medium">Produk</th>
                        <th class="py-2 font-medium">Kategori</th>
                        <th class="py-2 font-medium">Harga</th>
                        <th class="py-2 font-medium">Stok</th>
                        <th class="py-2 font-medium">Rating</th>
                        <th class="py-2 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) == 0) { ?>
                        <!-- Empty state bila katalog masih kosong. -->
                        <tr><td colspan="6" class="py-8 text-center text-slate-400">Belum ada produk.</td></tr>
                    <?php } ?>
                    <?php foreach ($products as $p) { ?>
                        <!-- Satu baris produk; kolom aksi menyediakan edit dan hapus. -->
                        <tr class="border-b border-slate-50 last:border-0">
                            <td class="py-3">
                                <p class="font-bold text-slate-900"><?php echo htmlspecialchars($p["product_name"]); ?></p>
                                <p class="text-[11px] text-slate-500">Rp <?php echo number_format($p["price"], 0, ",", "."); ?></p>
                            </td>
                            <td class="py-3 text-slate-600"><?php echo htmlspecialchars($p["category_name"] ?? "-"); ?></td>
                            <td class="py-3 font-bold text-slate-900">Rp <?php echo number_format($p["price"], 0, ",", "."); ?></td>
                            <td class="py-3 text-slate-600"><?php echo (int)$p["stock"]; ?></td>
                            <td class="py-3">
                                <?php
                                // Rating diambil dari cache $rating_produk; jika tidak ada, tampilkan tanda "-".
                                $r = $rating_produk[$p["product_id"]] ?? null;
                                if ($r) {
                                    echo '<span class="text-amber-500 font-semibold">&#9733; ' . number_format((float)$r["rata_rata"], 1, ",", ".") . '</span>';
                                    echo '<span class="text-slate-400"> (' . (int)$r["jumlah"] . ')</span>';
                                } else {
                                    echo '<span class="text-slate-400">-</span>';
                                }
                                ?>
                            </td>
                            <td class="py-3">
                                <div class="flex gap-2">
                                    <!-- Edit memuat ulang halaman dengan ?edit=; hapus meminta konfirmasi lebih dulu. -->
                                    <a href="products.php?edit=<?php echo $p["product_id"]; ?>" class="bg-brand-50 text-brand-600 text-[11px] font-semibold px-3 py-1.5 rounded-lg">Edit</a>
                                    <a href="/PWL-SAS/actions/delete_product.php?id=<?php echo $p["product_id"]; ?>" onclick="return confirm('Hapus produk ini?')" class="bg-red-50 text-red-600 text-[11px] font-semibold px-3 py-1.5 rounded-lg">Hapus</a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <form action="/PWL-SAS/actions/save_product.php" method="POST" class="xl:col-span-4 bg-white rounded-2xl border border-slate-100 p-5 space-y-4">
        <!-- Judul dan isi form mengikuti mode: tambah baru atau edit produk yang dipilih. -->
        <h2 class="font-bold text-slate-900"><?php echo $edit ? "Edit produk" : "Tambah produk"; ?></h2>
        <?php if ($edit) { ?>
            <!-- product_id hanya dikirim saat edit agar action tahu baris mana yang di-update. -->
            <input type="hidden" name="product_id" value="<?php echo $edit["product_id"]; ?>">
        <?php } ?>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Nama produk</label>
            <input type="text" name="product_name" required value="<?php echo htmlspecialchars($edit["product_name"] ?? ""); ?>"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Kategori</label>
            <select name="category_id" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
                <option value="">-- Pilih kategori --</option>
                <?php // Kategori yang sedang dipakai produk otomatis selected saat mode edit. ?>
                <?php foreach ($categories as $c) { ?>
                    <option value="<?php echo $c["category_id"]; ?>" <?php if (($edit["category_id"] ?? 0) == $c["category_id"]) { echo "selected"; } ?>>
                        <?php echo htmlspecialchars($c["category_name"]); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Harga</label>
                <input type="number" name="price" min="0" value="<?php echo htmlspecialchars($edit["price"] ?? "0"); ?>"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Stok</label>
                <input type="number" name="stock" min="0" value="<?php echo htmlspecialchars($edit["stock"] ?? "0"); ?>"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
            </div>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">URL gambar</label>
            <input type="text" name="url" value="<?php echo htmlspecialchars($edit["url"] ?? ""); ?>"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Badge</label>
            <input type="text" name="badge" value="<?php echo htmlspecialchars($edit["badge"] ?? ""); ?>"
                   class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Deskripsi</label>
            <textarea name="description" rows="3" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500"><?php echo htmlspecialchars($edit["description"] ?? ""); ?></textarea>
        </div>
        <button class="w-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold py-3 rounded-xl">
            <?php echo $edit ? "Simpan perubahan" : "Tambah produk"; ?>
        </button>
        <?php if ($edit) { ?>
            <!-- Batal mengembalikan form ke mode tambah tanpa menyimpan apa pun. -->
            <a href="products.php" class="block text-center border border-slate-300 text-slate-700 text-sm font-medium py-3 rounded-xl">Batal</a>
        <?php } ?>
    </form>
</div>
<?php admin_end(); ?>


