<?php
/**
 * Dashboard admin.
 *
 * Menggabungkan angka ringkasan dari repositories/dashboard.php: total pesanan,
 * pesanan hari ini, pendapatan bulan ini, teknisi tersedia, pelanggan, produk
 * terlaris, daftar pesanan terbaru, dan layanan populer. $kartu menyusun empat
 * kartu statistik agar markup di bawah cukup di-loop. Halaman hanya bisa dibuka
 * admin (require_admin) dan memakai layout admin.
 */
session_start();
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../repositories/dashboard.php";
require_once __DIR__ . "/../../repositories/auth.php";
require_once __DIR__ . "/../../components/admin/layout_admin.php";

// Semua halaman admin melewati require_admin(); user biasa akan ditolak/diarahkan.
require_admin();

// Kumpulkan seluruh metrik dashboard sekali di awal agar markup tinggal menampilkan.
$total_orders = total_orders($conn);
$todays_orders = todays_orders($conn);
$pendapatan = monthly_revenue($conn);
$teknisi_total = total_technicians($conn);
$teknisi_ada = available_technicians($conn);
$pelanggan = total_customers($conn);
$total_produk = total_products($conn);
$terlaris = top_products($conn);
$daftar_pesanan = latest_orders($conn);
$daftar_layanan = popular_services($conn);

// Susunan kartu: [judul, label badge, nilai utama, keterangan].
$kartu = [
    ["Total pesanan", "Semua", number_format($total_orders, 0, ",", "."), $todays_orders . " pesanan baru hari ini"],
    ["Pendapatan", "Bulan ini", format_compact($pendapatan), "Omzet bulan ini"],
    ["Teknisi aktif", "dari " . $teknisi_total, number_format($teknisi_ada, 0, ",", "."), "Siap menerima kunjungan"],
    ["Pelanggan", "Terdaftar", number_format($pelanggan, 0, ",", "."), "Pengguna dengan role user"]
];

admin_start("Dashboard Admin", "Kelola produk, pesanan, teknisi, dan operasional bisnis dari satu tempat.", "dashboard.php");
?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <!-- Pintasan tindakan cepat ke halaman admin yang paling sering dipakai. -->
    <a href="products.php" class="bg-white rounded-2xl border border-slate-100 p-4 flex items-center gap-3 hover:shadow-sm">
        <span class="w-10 h-10 bg-brand-50 text-brand-600 rounded-xl flex items-center justify-center">
            <i data-lucide="plus" class="w-4 h-4"></i>
        </span>
        <div>
            <p class="font-bold text-sm text-slate-900">Tambah produk</p>
            <p class="text-[11px] text-slate-500">Buat listing baru</p>
        </div>
    </a>
    <a href="booking.php" class="bg-white rounded-2xl border border-slate-100 p-4 flex items-center gap-3 hover:shadow-sm">
        <span class="w-10 h-10 bg-brand-50 text-brand-600 rounded-xl flex items-center justify-center">
            <i data-lucide="calendar" class="w-4 h-4"></i>
        </span>
        <div>
            <p class="font-bold text-sm text-slate-900">Jadwalkan teknisi</p>
            <p class="text-[11px] text-slate-500">Atur kunjungan</p>
        </div>
    </a>
    <a href="customers.php" class="bg-white rounded-2xl border border-slate-100 p-4 flex items-center gap-3 hover:shadow-sm">
        <span class="w-10 h-10 bg-brand-50 text-brand-600 rounded-xl flex items-center justify-center">
            <i data-lucide="users" class="w-4 h-4"></i>
        </span>
        <div>
            <p class="font-bold text-sm text-slate-900">Kelola pelanggan</p>
            <p class="text-[11px] text-slate-500">Lihat profil & aktivitas</p>
        </div>
    </a>
</div>
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
    <div class="xl:col-span-8 grid grid-cols-2 md:grid-cols-4 gap-4">
        <?php // Empat kartu statistik dirender dari array $kartu di atas. ?>
        <?php foreach ($kartu as $k) { ?>
            <div class="bg-white rounded-2xl border border-slate-100 p-4 space-y-2">
                <div class="flex justify-between items-start gap-1">
                    <p class="text-xs text-slate-500"><?php echo $k[0]; ?></p>
                    <span class="bg-brand-50 text-brand-600 text-[10px] font-semibold px-2 py-0.5 rounded-full whitespace-nowrap">
                        <?php echo $k[1]; ?>
                    </span>
                </div>
                <p class="text-2xl font-extrabold text-slate-900"><?php echo $k[2]; ?></p>
                <p class="text-[11px] text-slate-500"><?php echo $k[3]; ?></p>
            </div>
        <?php } ?>
    </div>
    <div class="xl:col-span-4 bg-white rounded-2xl border border-slate-100 p-5 space-y-3">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-slate-900">Ringkasan</h2>
            <span class="bg-brand-50 text-brand-600 text-[10px] font-semibold px-3 py-1 rounded-full">Hari ini</span>
        </div>
        <div class="flex justify-between text-xs text-slate-500">
            <span>Pesanan baru</span>
            <span class="font-bold text-slate-900"><?php echo $todays_orders; ?></span>
        </div>
        <div class="flex justify-between text-xs text-slate-500">
            <span>Teknisi tersedia</span>
            <span class="font-bold text-slate-900"><?php echo $teknisi_ada; ?></span>
        </div>
        <div class="flex justify-between text-xs text-slate-500">
            <span>Produk terlaris</span>
            <span class="font-bold text-slate-900">
                <?php echo $terlaris ? htmlspecialchars($terlaris["product_name"]) : "-"; ?>
            </span>
        </div>
        <div class="flex justify-between text-xs text-slate-500">
            <span>Total produk</span>
            <span class="font-bold text-slate-900"><?php echo $total_produk; ?></span>
        </div>
    </div>
</div>
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
    <div class="xl:col-span-8 bg-white rounded-2xl border border-slate-100 p-5">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="font-bold text-slate-900">Pesanan terbaru</h2>
                <p class="text-xs text-slate-500"><?php echo count($daftar_pesanan); ?> pesanan yang memerlukan tindak lanjut</p>
            </div>
            <a href="orders.php" class="bg-brand-50 text-brand-600 text-[11px] font-semibold px-3 py-1.5 rounded-full">Lihat semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left text-slate-500 border-b border-slate-100">
                        <th class="py-2 font-medium">Pesanan</th>
                        <th class="py-2 font-medium">Pelanggan</th>
                        <th class="py-2 font-medium">Status</th>
                        <th class="py-2 font-medium">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($daftar_pesanan) == 0) { ?>
                        <!-- Empty state tabel pesanan terbaru. -->
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-400">Belum ada pesanan.</td>
                        </tr>
                    <?php } ?>
                    <?php foreach ($daftar_pesanan as $p) { ?>
                        <!-- Satu baris pesanan: kode, pelanggan + item, badge status berwarna, total. -->
                        <tr class="border-b border-slate-50 last:border-0">
                            <td class="py-4 font-bold text-slate-900">#<?php echo htmlspecialchars($p["kode"]); ?></td>
                            <td class="py-4">
                                <p class="font-bold text-slate-900"><?php echo htmlspecialchars($p["pelanggan"]); ?></p>
                                <p class="text-[11px] text-slate-500"><?php echo htmlspecialchars($p["item"]); ?></p>
                            </td>
                            <td class="py-4">
                                <span class="text-[11px] font-semibold px-3 py-1.5 rounded-full <?php echo get_status_color($p["status"]); ?>">
                                    <?php echo htmlspecialchars($p["status"]); ?>
                                </span>
                            </td>
                            <td class="py-4 font-bold text-slate-900"><?php echo format_rupiah($p["total"]); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="xl:col-span-4 bg-white rounded-2xl border border-slate-100 p-5">
        <div class="flex justify-between items-center mb-4">
            <h2 class="font-bold text-slate-900">Layanan populer</h2>
            <span class="bg-brand-50 text-brand-600 text-[10px] font-semibold px-3 py-1 rounded-full">
                <?php echo count($daftar_layanan); ?> layanan
            </span>
        </div>
        <div class="space-y-3">
            <?php foreach ($daftar_layanan as $l) { ?>
                <!-- Layanan populer: nama, harga dasar, dan jumlah pemesanan. -->
                <div class="bg-slate-50 rounded-xl p-3 flex items-center gap-3">
                    <span class="w-9 h-9 bg-brand-50 text-brand-600 rounded-xl flex items-center justify-center shrink-0">
                        <i data-lucide="wrench" class="w-4 h-4"></i>
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-xs text-slate-900 truncate"><?php echo htmlspecialchars($l["service_name"]); ?></p>
                        <p class="text-[11px] text-slate-500">Mulai <?php echo format_rupiah($l["base_price"]); ?></p>
                    </div>
                    <span class="bg-brand-50 text-brand-600 text-[11px] font-bold px-3 py-1 rounded-full">
                        <?php echo $l["jumlah"]; ?>
                    </span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php admin_end(); ?>


