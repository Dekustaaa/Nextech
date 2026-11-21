<?php
/**
 * Halaman kelola booking teknisi (admin).
 *
 * Memuat layanan (dropdown), teknisi untuk pilihan awal, semua teknisi untuk
 * form update per baris, serta seluruh booking dengan join nama layanan dan
 * teknisi. Form kiri membuat booking baru ke actions/simpan_booking.php;
 * form di tiap baris mengubah teknisi/status lewat actions/update_booking.php.
 */
session_start();
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../repositories/services.php";
require_once __DIR__ . "/../../repositories/technicians.php";
require_once __DIR__ . "/../../repositories/dashboard.php";
require_once __DIR__ . "/../../repositories/auth.php";
require_once __DIR__ . "/../../components/admin/layout_admin.php";

// Halaman admin: buat booking dan atur teknisi/status tiap booking.
cek_admin();

// Layanan untuk dropdown form booking baru, teknisi tersedia untuk pilihan awal.
$layanan = ambil_services($conn);
$teknisi = ambil_teknisi($conn);

// Daftar teknisi lengkap dipakai pada select "atur" di setiap baris booking.
$query = mysqli_query($conn, "SELECT * FROM technicians ORDER BY technician_name ASC");
$semua_teknisi = [];
while ($row = mysqli_fetch_assoc($query)) {
    $semua_teknisi[] = $row;
}

$query = mysqli_query($conn, "SELECT b.booking_id, b.no_booking, b.customer_name, b.phone, b.address, b.tanggal, b.jam, b.status, s.service_name, t.technician_name
                              FROM bookings b
                              LEFT JOIN services s ON b.service_id = s.service_id
                              LEFT JOIN technicians t ON b.technician_id = t.technician_id
                              ORDER BY b.created_at DESC");
// LEFT JOIN menjaga booking tetap tampil walau layanan/teknisi sudah tidak ada.
$booking = [];
while ($row = mysqli_fetch_assoc($query)) {
    $booking[] = $row;
}

$pesan = $_SESSION["admin_pesan"] ?? "";
unset($_SESSION["admin_pesan"]);

admin_atas("Booking Teknisi", "Buat booking baru dan atur teknisi + statusnya.", "booking.php");
?>
<?php if ($pesan != "") { ?>
    <!-- Notifikasi hasil simpan/update booking. -->
    <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 text-sm rounded-2xl p-4">
        <?php echo htmlspecialchars($pesan); ?>
    </div>
<?php } ?>
<div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
    <form action="/PWL-SAS/actions/simpan_booking.php" method="POST" class="xl:col-span-4 bg-white rounded-2xl border border-slate-100 p-5 space-y-4">
        <!-- Form booking baru; teknisi opsional (0 = pilih otomatis). -->
        <h2 class="font-bold text-slate-900">Buat booking</h2>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Nama</label>
            <input type="text" name="nama" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Telepon</label>
            <input type="text" name="telepon" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Layanan</label>
            <select name="service_id" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
                <option value="">-- Pilih --</option>
                <?php // Layanan diambil dari tabel services lewat ambil_services(). ?>
                <?php foreach ($layanan as $s) { ?>
                    <option value="<?php echo $s["service_id"]; ?>"><?php echo htmlspecialchars($s["service_name"]); ?></option>
                <?php } ?>
            </select>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Teknisi</label>
            <select name="technician_id" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
                <option value="0">Pilih otomatis</option>
                <?php foreach ($teknisi as $t) { ?>
                    <option value="<?php echo $t["technician_id"]; ?>"><?php echo htmlspecialchars($t["technician_name"]); ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Tanggal</label>
                <!-- min=hari ini membatasi pilihan tanggal booking ke hari ini atau setelahnya. -->
                <input type="date" name="tanggal" required min="<?php echo date("Y-m-d"); ?>" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Jam</label>
                <input type="time" name="jam" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500">
            </div>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Alamat</label>
            <textarea name="alamat" rows="2" required class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500"></textarea>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Keluhan</label>
            <textarea name="keluhan" rows="2" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-brand-500"></textarea>
        </div>
        <button class="w-full bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold py-3 rounded-xl">Simpan booking</button>
    </form>
    <div class="xl:col-span-8 bg-white rounded-2xl border border-slate-100 p-5">
        <h2 class="font-bold text-slate-900 mb-4">Daftar booking (<?php echo count($booking); ?>)</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="text-left text-slate-500 border-b border-slate-100">
                        <th class="py-2 font-medium">Booking</th>
                        <th class="py-2 font-medium">Teknisi</th>
                        <th class="py-2 font-medium">Status</th>
                        <th class="py-2 font-medium">Atur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($booking) == 0) { ?>
                        <!-- Empty state bila belum ada booking sama sekali. -->
                        <tr><td colspan="4" class="py-8 text-center text-slate-400">Belum ada booking.</td></tr>
                    <?php } ?>
                    <?php foreach ($booking as $b) { ?>
                        <!-- Baris booking: info pelanggan/layanan, teknisi saat ini, badge status, dan form atur. -->
                        <tr class="border-b border-slate-50 last:border-0">
                            <td class="py-3">
                                <p class="font-bold text-slate-900">#<?php echo htmlspecialchars($b["no_booking"]); ?></p>
                                <p class="text-[11px] text-slate-500"><?php echo htmlspecialchars($b["customer_name"]); ?> - <?php echo htmlspecialchars($b["service_name"] ?? "-"); ?></p>
                                <p class="text-[11px] text-slate-500"><?php echo $b["tanggal"]; ?> <?php echo $b["jam"]; ?></p>
                            </td>
                            <td class="py-3 text-slate-600"><?php echo htmlspecialchars($b["technician_name"] ?? "Belum ada"); ?></td>
                            <td class="py-3">
                                <span class="text-[11px] font-semibold px-3 py-1.5 rounded-full <?php echo dash_warna_status($b["status"]); ?>">
                                    <?php echo htmlspecialchars($b["status"]); ?>
                                </span>
                            </td>
                            <td class="py-3">
                                <form action="/PWL-SAS/actions/update_booking.php" method="POST" class="flex gap-2 items-center">
                                    <!-- booking_id menandai baris; pilihan teknisi/status baru dikirim ke action. -->
                                    <input type="hidden" name="booking_id" value="<?php echo $b["booking_id"]; ?>">
                                    <select name="technician_id" class="border border-slate-200 rounded-lg px-2 py-1.5 text-[11px]">
                                        <option value="0">Teknisi</option>
                                        <?php foreach ($semua_teknisi as $t) { ?>
                                            <option value="<?php echo $t["technician_id"]; ?>"><?php echo htmlspecialchars($t["technician_name"]); ?></option>
                                        <?php } ?>
                                    </select>
                                    <select name="status" class="border border-slate-200 rounded-lg px-2 py-1.5 text-[11px]">
                                        <option value="">Status</option>
                                        <option value="Menunggu">Menunggu</option>
                                        <option value="Dikonfirmasi">Dikonfirmasi</option>
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
</div>
<?php admin_bawah(); ?>
