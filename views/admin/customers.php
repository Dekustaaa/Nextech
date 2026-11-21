<?php
/**
 * Halaman daftar pelanggan (admin).
 *
 * Mengambil pengguna ber-role user melalui ambil_pelanggan(): nama, alamat,
 * email, telepon, dan jumlah pesanan. Tabel menyediakan tombol "Lihat" menuju
 * customer_detail.php?id=... untuk melihat profil dan riwayat belanja.
 */
session_start();
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../repositories/customers.php";
require_once __DIR__ . "/../../repositories/auth.php";
require_once __DIR__ . "/../../components/admin/layout_admin.php";

// Daftar pelanggan hanya untuk admin.
cek_admin();

// Query pelanggan (role user) beserta jumlah pesanannya.
$pelanggan = ambil_pelanggan($conn);

admin_atas("Pelanggan", "Lihat profil dan aktivitas belanja tiap pelanggan.", "customers.php");
?>
<div class="bg-white rounded-2xl border border-slate-100 p-5">
    <h2 class="font-bold text-slate-900 mb-4">Daftar pelanggan (<?php echo count($pelanggan); ?>)</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="text-left text-slate-500 border-b border-slate-100">
                    <th class="py-2 font-medium">Pelanggan</th>
                    <th class="py-2 font-medium">Kontak</th>
                    <th class="py-2 font-medium">Pesanan</th>
                    <th class="py-2 font-medium">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($pelanggan) == 0) { ?>
                    <!-- Empty state bila belum ada pengguna ber-role user. -->
                    <tr><td colspan="4" class="py-8 text-center text-slate-400">Belum ada pelanggan.</td></tr>
                <?php } ?>
                <?php foreach ($pelanggan as $p) { ?>
                    <!-- Satu baris pelanggan: avatar inisial, alamat, kontak, jumlah pesanan, dan tombol detail. -->
                    <tr class="border-b border-slate-50 last:border-0">
                        <td class="py-4">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 bg-brand-600 text-white text-[11px] font-bold rounded-xl flex items-center justify-center shrink-0">
                                    <?php echo strtoupper(substr($p["full_name"], 0, 2)); ?>
                                </span>
                                <div>
                                    <p class="font-bold text-slate-900"><?php echo htmlspecialchars($p["full_name"]); ?></p>
                                    <p class="text-[11px] text-slate-500"><?php echo htmlspecialchars($p["address"] ?? "Belum ada alamat"); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="py-4">
                            <p class="text-slate-700"><?php echo htmlspecialchars($p["email"]); ?></p>
                            <p class="text-[11px] text-slate-500"><?php echo htmlspecialchars($p["phone_number"] ?? "-"); ?></p>
                        </td>
                        <td class="py-4">
                            <span class="bg-brand-50 text-brand-600 text-[11px] font-bold px-3 py-1 rounded-full"><?php echo $p["jml_pesanan"]; ?>x</span>
                        </td>
                        <td class="py-4">
                            <!-- Detail membuka halaman riwayat pesanan pelanggan ini. -->
                            <a href="customer_detail.php?id=<?php echo $p["user_id"]; ?>" class="bg-brand-600 text-white text-[11px] font-semibold px-3 py-1.5 rounded-lg">Lihat</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php admin_bawah(); ?>


