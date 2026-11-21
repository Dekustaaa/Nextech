<?php
/**
 * Halaman detail pelanggan (admin).
 *
 * Membaca ?id= lalu memuat satu pelanggan dan seluruh riwayat pesanannya.
 * Menampilkan data kontak serta tabel order beserta status bayar/paket. Jika ID
 * tidak valid atau pelanggan tidak ditemukan, admin dikembalikan ke daftar
 * pelanggan agar tidak menampilkan halaman kosong.
 */
session_start();
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../repositories/customers.php";
require_once __DIR__ . "/../../repositories/auth.php";
require_once __DIR__ . "/../../components/admin/layout_admin.php";

// Hanya admin yang boleh membuka detail pelanggan.
require_admin();

// Ambil ID dari URL dan cast ke int; 0 berarti tidak ada pelanggan yang dipilih.
$id = (int)($_GET["id"] ?? 0);
$p = get_customer_by_id($conn, $id);

// Pelanggan tidak ditemukan: kembali ke daftar daripada menampilkan data kosong.
if (!$p) {
    header("Location: /PWL-SAS/views/admin/customers.php");
    exit;
}

// Riwayat transaksi pelanggan untuk tabel di kolom kanan.
$riwayat = get_customer_orders($conn, $id);

admin_start("Profil Pelanggan", "Detail profil dan riwayat belanja " . $p["full_name"] . ".", "customers.php");
?>
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
    <div class="xl:col-span-4 bg-white rounded-2xl border border-slate-100 p-5 space-y-3">
        <div class="flex items-center gap-3">
            <!-- Avatar inisial dua huruf dihitung dari nama pelanggan. -->
            <span class="w-12 h-12 bg-brand-600 text-white font-bold rounded-2xl flex items-center justify-center">
                <?php echo strtoupper(substr($p["full_name"], 0, 2)); ?>
            </span>
            <div>
                <p class="font-bold text-slate-900"><?php echo htmlspecialchars($p["full_name"]); ?></p>
                <p class="text-[11px] text-slate-500">Sejak <?php echo $p["created_at"]; ?></p>
            </div>
        </div>
        <div class="flex justify-between text-xs text-slate-500 border-t border-slate-100 pt-3">
            <span>Email</span>
            <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($p["email"]); ?></span>
        </div>
        <div class="flex justify-between text-xs text-slate-500">
            <span>Telepon</span>
            <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($p["phone_number"] ?? "-"); ?></span>
        </div>
        <div class="flex justify-between text-xs text-slate-500">
            <span>Tanggal lahir</span>
            <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($p["date_of_birth"] ?? "-"); ?></span>
        </div>
        <div class="text-xs text-slate-500">
            <p class="mb-1">Alamat</p>
            <p class="font-semibold text-slate-900"><?php echo htmlspecialchars($p["address"] ?? "Belum diisi"); ?></p>
        </div>
        <a href="customers.php" class="block text-center border border-slate-300 text-slate-700 text-sm font-medium py-3 rounded-xl">Kembali</a>
    </div>
    <div class="xl:col-span-8 bg-white rounded-2xl border border-slate-100 p-5">
        <h2 class="font-bold text-slate-900 mb-4">Riwayat pesanan (<?php echo count($riwayat); ?>)</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left text-slate-500 border-b border-slate-100">
                        <th class="py-2 font-medium">Order</th>
                        <th class="py-2 font-medium">Tanggal</th>
                        <th class="py-2 font-medium">Bayar</th>
                        <th class="py-2 font-medium">Status</th>
                        <th class="py-2 font-medium">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($riwayat) == 0) { ?>
                        <!-- Empty state bila pelanggan belum pernah menyelesaikan pesanan. -->
                        <tr><td colspan="5" class="py-8 text-center text-slate-400">Belum pernah belanja.</td></tr>
                    <?php } ?>
                    <?php foreach ($riwayat as $r) { ?>
                        <!-- Baris riwayat: nomor order, tanggal, status bayar/paket, dan total. -->
                        <tr class="border-b border-slate-50 last:border-0">
                            <td class="py-3 font-bold text-slate-900">#<?php echo $r["order_id"]; ?></td>
                            <td class="py-3 text-slate-600"><?php echo $r["order_date"]; ?></td>
                            <td class="py-3 text-slate-600"><?php echo htmlspecialchars($r["payment_status"] ?? "-"); ?></td>
                            <td class="py-3 text-slate-600"><?php echo htmlspecialchars($r["status"]); ?></td>
                            <td class="py-3 font-bold text-slate-900">Rp <?php echo number_format($r["total_amount"], 0, ",", "."); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php admin_end(); ?>


