<?php
/**
 * Halaman daftar pesanan (admin).
 *
 * Memuat seluruh pesanan lewat get_orders() lalu menampilkan status bayar dan
 * status paket masing-masing. Setiap baris memiliki form kecil untuk mengubah
 * kedua status tersebut; form dikirim ke actions/order_status.php. Warna badge
 * status ditentukan oleh get_status_color() dari repositories/dashboard.php.
 */
session_start();
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../repositories/orders.php";
require_once __DIR__ . "/../../repositories/auth.php";
require_once __DIR__ . "/../../components/admin/layout_admin.php";
require_once __DIR__ . "/../../repositories/dashboard.php";

// Hanya admin yang dapat melihat dan mengubah status pesanan.
require_admin();

// Ambil seluruh pesanan beserta data pelanggannya.
$pesanan = get_orders($conn);

// Pesan hasil update status dari actions/order_status.php.
$pesan = $_SESSION["admin_pesan"] ?? "";
unset($_SESSION["admin_pesan"]);

admin_start("Pesanan", "Lihat status bayar dan status paket, ubah kalau ada yang lunas atau dikirim.", "orders.php");
?>
<?php if ($pesan != "") { ?>
    <!-- Notifikasi setelah status pesanan diperbarui. -->
    <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm rounded-2xl p-4">
        <?php echo htmlspecialchars($pesan); ?>
    </div>
<?php } ?>
<div class="bg-white rounded-2xl border border-slate-100 p-5">
    <h2 class="font-bold text-slate-900 mb-4">Daftar pesanan (<?php echo count($pesanan); ?>)</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="text-left text-slate-500 border-b border-slate-100">
                    <th class="py-2 font-medium">Order</th>
                    <th class="py-2 font-medium">Pelanggan</th>
                    <th class="py-2 font-medium">Bayar</th>
                    <th class="py-2 font-medium">Paket</th>
                    <th class="py-2 font-medium">Total</th>
                    <th class="py-2 font-medium">Ubah</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($pesanan) == 0) { ?>
                    <!-- Empty state jika belum ada transaksi sama sekali. -->
                    <tr><td colspan="6" class="py-8 text-center text-slate-400">Belum ada pesanan.</td></tr>
                <?php } ?>
                <?php foreach ($pesanan as $o) { ?>
                    <!-- Satu baris pesanan: data order, dua badge status, total, dan form update cepat. -->
                    <tr class="border-b border-slate-50 last:border-0">
                        <td class="py-4">
                            <p class="font-bold text-slate-900">#<?php echo $o["order_id"]; ?></p>
                            <p class="text-[11px] text-slate-500"><?php echo $o["order_date"]; ?></p>
                        </td>
                        <td class="py-4">
                            <p class="font-bold text-slate-900"><?php echo htmlspecialchars($o["full_name"]); ?></p>
                            <p class="text-[11px] text-slate-500"><?php echo htmlspecialchars($o["payment_method"] ?? "-"); ?></p>
                        </td>
                        <td class="py-4">
                            <span class="text-[11px] font-semibold px-3 py-1.5 rounded-full <?php echo get_status_color($o["payment_status"] ?? "Pending"); ?>">
                                <?php echo htmlspecialchars($o["payment_status"] ?? "Pending"); ?>
                            </span>
                        </td>
                        <td class="py-4">
                            <span class="text-[11px] font-semibold px-3 py-1.5 rounded-full <?php echo get_status_color($o["status"]); ?>">
                                <?php echo htmlspecialchars($o["status"]); ?>
                            </span>
                        </td>
                        <td class="py-4 font-bold text-slate-900">Rp <?php echo number_format($o["total_amount"], 0, ",", "."); ?></td>
                        <td class="py-4">
                            <form action="/PWL-SAS/actions/order_status.php" method="POST" class="flex gap-2 items-center">
                                <!-- order_id menentukan pesanan yang diubah; dua select mengirim status bayar dan status paket. -->
                                <input type="hidden" name="order_id" value="<?php echo $o["order_id"]; ?>">
                                <select name="payment_status" class="border border-slate-200 rounded-lg px-2 py-1.5 text-[11px]">
                                    <option value="">Bayar</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Lunas">Lunas</option>
                                    <option value="Gagal">Gagal</option>
                                </select>
                                <select name="status" class="border border-slate-200 rounded-lg px-2 py-1.5 text-[11px]">
                                    <option value="">Paket</option>
                                    <option value="Pending">Pending</option>
                                    <option value="Diproses">Diproses</option>
                                    <option value="Dikirim">Dikirim</option>
                                    <option value="Selesai">Selesai</option>
                                    <option value="Dibatalkan">Dibatalkan</option>
                                </select>
                                <button class="bg-brand-600 hover:bg-brand-700 text-white text-[11px] font-semibold px-3 py-1.5 rounded-lg">OK</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php admin_end(); ?>


