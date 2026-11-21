<?php
/**
 * Halaman konfirmasi pembayaran berhasil.
 *
 * Menampilkan ringkasan pesanan terakhir dari $_SESSION['pesanan'] (nomor order,
 * tanggal, metode, dan total) yang disiapkan actions/payment.php. Halaman ini
 * adalah langkah terakhir alur checkout; jika session pesanan tidak ada,
 * pengguna dikembalikan ke beranda agar tidak melihat halaman kosong.
 */
session_start();
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../components/landing/layout.php";

require_login();

// Kalau tidak ada data pesanan, balik ke beranda
// Session ini diisi actions/payment.php setelah pembayaran diproses.
if (!isset($_SESSION['pesanan'])) {
    header("Location: ../mainpage.php");
    exit;
}

$pesanan = $_SESSION['pesanan'];

$activeNav = 'cart';
page_start("Pembayaran Berhasil - Nexatech");
?>

<div class="mx-auto w-full max-w-2xl px-4 py-8 sm:px-6">

    <div class="th-card overflow-hidden">
        <div class="relative overflow-hidden bg-gradient-to-br from-brand-50 via-sky-50 to-white px-6 pb-7 pt-8 text-center sm:px-10">
            <span class="pointer-events-none absolute -right-10 -top-14 h-44 w-44 rounded-full border-[26px] border-white/60"></span>
            <span class="pointer-events-none absolute -bottom-16 -left-12 h-40 w-40 rounded-full bg-white/50"></span>

            <div class="relative mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 ring-8 ring-emerald-50/60">
                <i data-lucide="check-circle-2" class="h-8 w-8"></i>
            </div>

            <p class="relative mt-4 text-[10px] font-extrabold uppercase tracking-[0.16em] text-emerald-600">Payment Successful</p>
            <h1 class="relative mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Pembayaran berhasil!</h1>
            <p class="relative mx-auto mt-2 max-w-md text-[12px] leading-relaxed text-slate-500">
                Terima kasih sudah berbelanja di Nexatech. Pesanan Anda sedang kami proses.
            </p>
        </div>

        <div class="px-6 pb-7 sm:px-10">
            <!-- Ringkasan diambil dari array pesanan di session, bukan query ulang ke database. -->
            <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4">
                <div class="summary-list">
                    <div class="summary-list-row">
                        <span>Nomor order</span>
                        <strong>#<?php echo htmlspecialchars($pesanan['no_order']); ?></strong>
                    </div>
                    <div class="summary-list-row">
                        <span>Tanggal</span>
                        <strong><?php echo htmlspecialchars($pesanan['tanggal']); ?></strong>
                    </div>
                    <div class="summary-list-row">
                        <span>Metode pembayaran</span>
                        <strong><?php echo htmlspecialchars($pesanan['metode']); ?></strong>
                    </div>
                </div>

                <div class="summary-total mt-3">
                    <span>Total dibayar</span>
                    <strong>Rp <?php echo number_format($pesanan['total'], 0, ',', '.'); ?></strong>
                </div>
            </div>

            <div class="mt-3 flex items-start gap-2 rounded-xl border border-brand-100 bg-brand-50/60 p-3 text-[10.5px] leading-relaxed text-slate-600">
                <i data-lucide="truck" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600"></i>
                <span>Estimasi pengiriman 2-3 hari kerja. Teknisi akan menghubungi Anda untuk jadwal layanan.</span>
            </div>

            <div class="mt-5 space-y-2">
                <a href="../mainpage.php" class="th-btn th-btn-primary th-btn-full">
                    <i data-lucide="house" class="w-4 h-4"></i>
                    Kembali ke beranda
                </a>
                <a href="#" onclick="window.print(); return false;" class="th-btn th-btn-outline th-btn-full">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    Cetak bukti pembayaran
                </a>
            </div>
        </div>
    </div>
</div>
<?php page_end(); ?>
