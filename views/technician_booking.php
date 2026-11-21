<?php
/**
 * Halaman booking teknisi untuk pengguna.
 *
 * Memuat daftar layanan (dropdown), teknisi berstatus "tersedia" yang diurutkan
 * dari rating tertinggi, serta 5 booking terbaru untuk panel aktivitas. Form di
 * kiri mengirim permintaan ke actions/booking.php; hasilnya dilaporkan lewat
 * flash message "pesan_booking" (pasangan status + teks). Fungsi time_ago()
 * dipakai untuk menampilkan umur aktivitas dalam bahasa Indonesia.
 */
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../components/landing/layout.php";

// Halaman ini hanya untuk pengguna login; tamu akan diarahkan ke form login oleh require_login().
require_login();

// ---------- Data layanan (untuk dropdown) ----------
// Diambil sekali di awal agar tidak query berulang saat merender <option>.
$layanan = [];
$q = mysqli_query($conn, "SELECT * FROM services");
while ($row = mysqli_fetch_assoc($q)) {
    $layanan[] = $row;
}

// ---------- Teknisi yang tersedia ----------
// Hanya status 'tersedia' yang ditampilkan, diurutkan dari rating tertinggi sebagai rekomendasi.
$teknisi = [];
$q = mysqli_query($conn, "SELECT * FROM technicians WHERE status = 'tersedia' ORDER BY rating DESC");
while ($row = mysqli_fetch_assoc($q)) {
    $teknisi[] = $row;
}

// ---------- Aktivitas terbaru (5 booking terakhir) ----------
// LEFT JOIN dipakai supaya booking tanpa teknisi tetap muncul (nama teknisi jadi null).
$aktivitas = [];
$q = mysqli_query($conn, "SELECT b.no_booking, b.status, b.created_at, s.service_name, t.technician_name
                          FROM bookings b
                          LEFT JOIN services s    ON b.service_id    = s.service_id
                          LEFT JOIN technicians t ON b.technician_id = t.technician_id
                          ORDER BY b.created_at DESC
                          LIMIT 5");
while ($row = mysqli_fetch_assoc($q)) {
    $aktivitas[] = $row;
}

// ---------- Fungsi: "5 menit lalu" ----------
// Mengubah timestamp DB menjadi teks relatif untuk kolom aktivitas.
function time_ago($waktu) {
    $selisih = time() - strtotime($waktu);

    if ($selisih < 60)       { return "Baru saja"; }
    if ($selisih < 3600)     { return floor($selisih / 60) . " menit lalu"; }
    if ($selisih < 86400)    { return floor($selisih / 3600) . " jam lalu"; }
    return floor($selisih / 86400) . " hari lalu";
}

// ---------- Badge status ----------
// Pemetaan status booking ke class badge; status tak dikenal memakai fallback di markup.
$badge_status = [
    'Menunggu'     => 'th-badge-amber',
    'Dikonfirmasi' => 'th-badge-blue',
    'Selesai'      => 'th-badge-green',
    'Dibatalkan'   => 'th-badge-slate'
];

// ---------- Pesan sukses / gagal (sekali tampil) ----------
$pesan = $_SESSION['pesan_booking'] ?? null;
unset($_SESSION['pesan_booking']);

$activeNav = 'booking';
page_start("Booking Teknisi - Nexatech");
?>

<div class="page-wrap page-wrap-narrow">

    <section class="page-hero reveal" aria-labelledby="booking-title">
        <div class="page-hero-copy">
            <p class="page-eyebrow">Service Request</p>
            <h1 id="booking-title" class="page-title">Booking teknisi</h1>
            <p class="page-subtitle">Buat permintaan layanan, pilih teknisi, dan pantau aktivitas pesanan Anda.</p>
            <div class="page-hero-actions">
                <a href="/PWL-SAS/pages/technician/" class="th-btn th-btn-primary th-btn-sm">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                    Coba booking terbaru
                </a>
            </div>
        </div>
        <span class="page-hero-icon" aria-hidden="true"><i data-lucide="calendar-days"></i></span>
    </section>

    <!-- ================= PESAN ================= -->
    <?php // $pesan berisi [jenis, teks]; jenis menentukan warna alert dan ikon yang dipakai. ?>
    <?php if ($pesan) { ?>
        <div class="th-alert <?php echo $pesan[0] == 'sukses' ? 'th-alert-success' : 'th-alert-danger'; ?> mb-4" role="status">
            <i data-lucide="<?php echo $pesan[0] == 'sukses' ? 'check-circle-2' : 'alert-circle'; ?>" class="w-4 h-4 shrink-0"></i>
            <span><?php echo htmlspecialchars($pesan[1]); ?></span>
        </div>
    <?php } ?>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-12 lg:items-start">

        <!-- ================= KIRI: FORM PERMINTAAN BARU ================= -->
        <!-- Form mengirim data kunjungan ke actions/booking.php untuk divalidasi dan disimpan. -->
        <form action="../actions/booking.php" method="POST" class="lg:col-span-7 th-card th-card-pad">
            <div class="th-card-head">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h2 class="th-card-title">Buat permintaan baru</h2>
                        <p class="th-card-sub">Isi detail kunjungan teknisi.</p>
                    </div>
                </div>
            </div>

            <div class="th-form-grid">
                <div>
                    <label for="nama" class="th-label"><i data-lucide="user-round"></i> Nama lengkap</label>
                    <input type="text" id="nama" name="nama" required class="th-input" placeholder="Nama lengkap">
                </div>
                <div>
                    <label for="telepon" class="th-label"><i data-lucide="phone"></i> No. WhatsApp / telepon</label>
                    <input type="text" id="telepon" name="telepon" required placeholder="08xxxxxxxxxx" class="th-input">
                </div>

                <div>
                    <label for="service_id" class="th-label"><i data-lucide="wrench"></i> Jenis layanan</label>
                    <select name="service_id" id="service_id" required class="th-select">
                        <option value="">-- Pilih layanan --</option>
                        <?php // Opsi layanan menampilkan harga dasar sebagai acuan sebelum memesan. ?>
                        <?php foreach ($layanan as $s) { ?>
                            <option value="<?php echo (int)$s['service_id']; ?>">
                                <?php echo htmlspecialchars($s['service_name']); ?> (mulai Rp <?php echo number_format($s['base_price'], 0, ',', '.'); ?>)
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div>
                    <label for="technician_id" class="th-label"><i data-lucide="user-round-check"></i> Teknisi</label>
                    <select name="technician_id" id="technician_id" class="th-select">
                        <option value="0">Pilih otomatis</option>
                        <?php // Nilai 0 berarti biarkan sistem/admin memilih teknisi yang sesuai. ?>
                        <?php foreach ($teknisi as $t) { ?>
                            <option value="<?php echo (int)$t['technician_id']; ?>">
                                <?php echo htmlspecialchars($t['technician_name']); ?> - <?php echo htmlspecialchars($t['specialty']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div>
                    <label for="tanggal" class="th-label"><i data-lucide="calendar"></i> Tanggal kunjungan</label>
                    <!-- min=hari ini mencegah pengguna memilih tanggal yang sudah lewat. -->
                    <input type="date" id="tanggal" name="tanggal" required min="<?php echo date('Y-m-d'); ?>" class="th-input">
                </div>
                <div>
                    <label for="jam" class="th-label"><i data-lucide="clock"></i> Jam</label>
                    <input type="time" id="jam" name="jam" required class="th-input">
                </div>

                <div class="th-field-full">
                    <label for="alamat" class="th-label"><i data-lucide="map-pin"></i> Alamat lengkap</label>
                    <textarea name="alamat" id="alamat" required class="th-textarea" placeholder="Alamat lengkap kunjungan"></textarea>
                </div>

                <div class="th-field-full">
                    <label for="keluhan" class="th-label"><i data-lucide="message-square"></i> Keluhan / catatan</label>
                    <textarea name="keluhan" id="keluhan" class="th-textarea" placeholder="Contoh: Laptop mati total setelah terjatuh"></textarea>
                </div>

                <div class="th-field-full">
                    <button type="submit" class="th-btn th-btn-primary th-btn-full">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        Kirim permintaan
                    </button>
                </div>
            </div>
        </form>

        <!-- ================= KANAN ================= -->
        <aside class="lg:col-span-5 space-y-4">

            <!-- Teknisi yang tersedia -->
            <section class="th-card th-card-pad">
                <div class="th-card-head">
                    <div>
                        <h2 class="th-card-title">Teknisi tersedia</h2>
                        <p class="th-card-sub">Siap membantu hari ini.</p>
                    </div>
                    <span class="th-badge th-badge-green"><?php echo count($teknisi); ?> online</span>
                </div>

                <?php if (count($teknisi) == 0) { ?>
                    <!-- Empty state teknisi; form tetap bisa dikirim dengan pilihan otomatis. -->
                    <p class="text-[11px] leading-relaxed text-slate-500">Semua teknisi sedang bertugas. Permintaan Anda tetap bisa dikirim.</p>
                <?php } ?>

                <div class="space-y-3">
                    <?php foreach ($teknisi as $t) { ?>
                        <div class="flex items-center gap-3 rounded-xl border border-slate-100 p-2.5">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 text-[10px] font-bold text-white">
                                <?php echo htmlspecialchars(strtoupper(substr($t['technician_name'], 0, 2))); ?>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[11.5px] font-extrabold text-slate-900"><?php echo htmlspecialchars($t['technician_name']); ?></p>
                                <p class="truncate text-[10px] text-slate-500"><?php echo htmlspecialchars($t['specialty']); ?></p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p class="text-[10.5px] font-bold text-slate-900">&#9733; <?php echo htmlspecialchars($t['rating']); ?></p>
                                <p class="text-[9px] font-semibold text-emerald-600">Tersedia</p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </section>

            <!-- Aktivitas terbaru -->
            <section class="th-card th-card-pad">
                <div class="th-card-head">
                    <div>
                        <h2 class="th-card-title">Aktivitas terbaru</h2>
                        <p class="th-card-sub">5 permintaan terakhir.</p>
                    </div>
                </div>

                <?php if (count($aktivitas) == 0) { ?>
                    <p class="text-[11px] text-slate-500">Belum ada aktivitas.</p>
                <?php } ?>

                <div class="space-y-3.5">
                    <?php foreach ($aktivitas as $a) { ?>
                        <!-- Satu baris aktivitas: layanan, nomor booking, teknisi, waktu relatif, dan badge status. -->
                        <div class="flex gap-2.5">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                <i data-lucide="wrench" class="w-3.5 h-3.5"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-[11px] font-bold text-slate-900"><?php echo htmlspecialchars($a['service_name']); ?></p>
                                <p class="truncate text-[9.5px] text-slate-500">
                                    #<?php echo htmlspecialchars($a['no_booking']); ?>
                                    &bull; <?php echo htmlspecialchars($a['technician_name'] ?: 'Belum ada teknisi'); ?>
                                </p>
                                <p class="mt-0.5 text-[9px] text-slate-400"><?php echo time_ago($a['created_at']); ?></p>
                            </div>
                            <span class="th-badge <?php echo $badge_status[$a['status']] ?? 'th-badge-slate'; ?> self-start">
                                <?php echo htmlspecialchars($a['status']); ?>
                            </span>
                        </div>
                    <?php } ?>
                </div>
            </section>
        </aside>
    </div>
</div>
<?php page_end(); ?>
