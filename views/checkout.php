<?php
/**
 * Halaman checkout pesanan.
 *
 * Merangkum produk dan layanan dari session, menghitung subtotal/total di sisi
 * tampilan, lalu menampilkan form pembayaran. Nomor order sementara dibuat dari
 * tanggal (TH-ymd) dan dikirim bersama total ke actions/payment.php. Pengguna
 * yang belum login diarahkan oleh cek_login(), dan keranjang kosong langsung
 * dikembalikan ke cart.php.
 */
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../components/landing/layout.php";

// Halaman ini wajib login; cek_login() akan redirect ke halaman login bila session user kosong.
cek_login();

// Pesan dari actions/payment.php bila hidden input pembayaran tidak valid.
$checkoutError = $_SESSION["checkout_error"] ?? "";
unset($_SESSION["checkout_error"]);

if (!isset($_SESSION['cart']))    { $_SESSION['cart'] = []; }
if (!isset($_SESSION['layanan'])) { $_SESSION['layanan'] = []; }

// Keranjang kosong tidak boleh masuk checkout, jadi dikembalikan ke halaman cart.
if (count($_SESSION['cart']) == 0 && count($_SESSION['layanan']) == 0) {
    header("Location: cart.php");
    exit;
}

$subtotal    = 0;
$daftar_item = [];

// ---------- Ambil data PRODUK ----------
// Daftar item diseragamkan (nama, deskripsi, gambar, qty, subtotal) agar bisa dirender di dua panel sekaligus.
foreach ($_SESSION['cart'] as $id => $jumlah) {
    $id = (int)$id;
    $query  = mysqli_query($conn, "SELECT * FROM products WHERE product_id = $id");
    $produk = mysqli_fetch_assoc($query);
    if (!$produk) { continue; }

    $sub       = $produk['price'] * $jumlah;
    $subtotal += $sub;

    $daftar_item[] = [
        'nama'      => $produk['product_name'],
        'deskripsi' => $produk['description'],
        'gambar'    => $produk['url'],
        'qty'       => $jumlah,
        'subtotal'  => $sub
    ];
}

// ---------- Ambil data LAYANAN ----------
// Layanan tidak punya kolom gambar, jadi 'gambar' diisi string kosong dan dirender sebagai ikon.
foreach ($_SESSION['layanan'] as $id => $jumlah) {
    $id = (int)$id;
    $query   = mysqli_query($conn, "SELECT * FROM services WHERE service_id = $id");
    $layanan = mysqli_fetch_assoc($query);
    if (!$layanan) { continue; }

    $sub       = $layanan['base_price'] * $jumlah;
    $subtotal += $sub;

    $daftar_item[] = [
        'nama'      => $layanan['service_name'],
        'deskripsi' => $layanan['description'],
        'gambar'    => '',
        'qty'       => $jumlah,
        'subtotal'  => $sub
    ];
}

$total    = $subtotal;
// Nomor order sementara untuk tampilan; penyimpanan pesanan sebenarnya dilakukan actions/payment.php.
$no_order = "TH-" . date("ymd");

$activeNav = 'cart';
atas("Checkout - Nexatech");
?>

<div class="page-wrap page-wrap-narrow">

    <?php if ($checkoutError != "") { ?>
    <!-- Pesan validasi pembayaran ditampilkan sekali setelah redirect dari action. -->
    <div class="th-alert th-alert-danger mb-4" role="alert">
        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
        <span><?php echo htmlspecialchars($checkoutError); ?></span>
    </div>
    <?php } ?>

    <section class="page-hero reveal" aria-labelledby="checkout-title">
        <div class="page-hero-copy">
            <p class="page-eyebrow">Secure Checkout</p>
            <h1 id="checkout-title" class="page-title">Selesaikan pesanan Anda</h1>
            <p class="page-subtitle">Periksa ringkasan belanja, pilih metode pembayaran, lalu konfirmasi pesanan.</p>
            <div class="page-hero-actions">
                <span class="th-badge th-badge-blue"><i data-lucide="lock" class="w-3 h-3"></i> Pembayaran aman</span>
                <span class="th-badge th-badge-green"><i data-lucide="shield-check" class="w-3 h-3"></i> Garansi resmi</span>
            </div>
        </div>
        <span class="page-hero-icon" aria-hidden="true"><i data-lucide="credit-card"></i></span>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- ================= KIRI: RINGKASAN BELANJA ================= -->
        <section class="lg:col-span-7 th-card th-card-pad" aria-labelledby="items-title">
            <div class="th-card-head">
                <div>
                    <h2 id="items-title" class="th-card-title">Ringkasan belanja</h2>
                    <p class="th-card-sub">Produk dan layanan yang akan diproses.</p>
                </div>
                <span class="th-badge th-badge-blue"><?php echo count($daftar_item); ?> item</span>
            </div>

            <div class="divide-y divide-slate-100">
                <?php foreach ($daftar_item as $item) { ?>
                    <div class="flex items-center gap-3.5 py-3.5 first:pt-0 last:pb-0">
                        <?php if ($item['gambar'] != '') { ?>
                            <!-- Produk memakai thumbnail gambar asli. -->
                            <div class="cart-thumb !h-16 !w-16">
                                <img src="<?php echo htmlspecialchars($item['gambar']); ?>" alt="<?php echo htmlspecialchars($item['nama']); ?>">
                            </div>
                        <?php } else { ?>
                            <!-- Layanan tanpa gambar ditampilkan sebagai ikon, bukan img kosong. -->
                            <div class="cart-service-icon !h-16 !w-16">
                                <i data-lucide="wrench" class="w-5 h-5"></i>
                            </div>
                        <?php } ?>

                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-[12.5px] font-extrabold text-slate-900"><?php echo htmlspecialchars($item['nama']); ?></h3>
                            <p class="mt-0.5 truncate text-[10.5px] text-slate-500"><?php echo htmlspecialchars($item['deskripsi']); ?></p>
                            <span class="th-badge th-badge-slate mt-1.5">Qty <?php echo (int)$item['qty']; ?></span>
                        </div>

                        <p class="shrink-0 text-[12.5px] font-extrabold text-slate-900">
                            Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                        </p>
                    </div>
                <?php } ?>
            </div>

            <div class="mt-4 rounded-xl bg-slate-50 p-4">
                <div class="summary-list-row">
                    <span>Subtotal</span>
                    <strong>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></strong>
                </div>
                <div class="summary-list-row mt-2">
                    <span>Biaya layanan</span>
                    <strong class="text-emerald-600">Gratis</strong>
                </div>
                <div class="summary-total mt-3">
                    <span>Total</span>
                    <strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong>
                </div>
            </div>
        </section>

        <!-- ================= KANAN: FORM PEMBAYARAN ================= -->
        <!-- no_order dan total ikut dikirim sebagai hidden input; action tetap harus memvalidasi ulang. -->
        <form action="../actions/payment.php" method="POST"
              class="lg:col-span-5 th-card th-card-pad lg:sticky lg:top-24">
            <input type="hidden" name="no_order" value="<?php echo htmlspecialchars($no_order); ?>">
            <input type="hidden" name="total" value="<?php echo (int)$total; ?>">

            <div class="th-card-head">
                <div>
                    <h2 class="th-card-title">Ringkasan pesanan</h2>
                    <p class="th-card-sub">Order #<?php echo htmlspecialchars($no_order); ?></p>
                </div>
                <span class="th-badge th-badge-green"><i data-lucide="shield-check" class="w-3 h-3"></i> Aman</span>
            </div>

            <div class="space-y-2.5">
                <?php foreach ($daftar_item as $item) { ?>
                    <div class="flex items-center gap-2.5">
                        <?php if ($item['gambar'] != '') { ?>
                            <div class="cart-thumb !h-10 !w-10 !rounded-lg">
                                <img src="<?php echo htmlspecialchars($item['gambar']); ?>" alt="<?php echo htmlspecialchars($item['nama']); ?>">
                            </div>
                        <?php } else { ?>
                            <!-- Versi ringkas untuk daftar item di panel pembayaran. -->
                            <div class="cart-service-icon !h-10 !w-10 !rounded-lg">
                                <i data-lucide="wrench" class="w-4 h-4"></i>
                            </div>
                        <?php } ?>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-[11px] font-bold text-slate-900"><?php echo htmlspecialchars($item['nama']); ?></p>
                            <p class="text-[9.5px] text-slate-500">Qty <?php echo (int)$item['qty']; ?></p>
                        </div>

                        <p class="shrink-0 text-[10.5px] font-bold text-slate-900">
                            Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                        </p>
                    </div>
                <?php } ?>
            </div>

            <hr class="th-divider">

            <div class="summary-list">
                <div class="summary-list-row">
                    <span>Subtotal</span>
                    <strong>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></strong>
                </div>
            </div>

            <div class="mt-4">
                <!-- Metode hanya disimpan sebagai pilihan pengguna; tidak ada integrasi payment gateway di sini. -->
                <label for="metode" class="th-label">
                    <i data-lucide="wallet"></i>
                    Metode pembayaran
                </label>
                <select name="metode" id="metode" class="th-select">
                    <!-- value tetap Bahasa Indonesia agar validasi server tidak berubah saat UI diterjemahkan. -->
                    <option value="Kartu kredit">Kartu kredit</option>
                    <option value="Transfer bank">Transfer bank</option>
                    <option value="E-wallet">E-wallet</option>
                </select>
            </div>

            <div class="summary-total mt-4">
                <span>Total</span>
                <strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong>
            </div>

            <button type="submit" class="th-btn th-btn-primary th-btn-full mt-4">
                <i data-lucide="lock" class="w-4 h-4"></i>
                Bayar sekarang
            </button>

            <!-- Batal membayar: kembali ke keranjang tanpa menghapus isi session. -->
            <a href="cart.php" class="th-btn th-btn-outline th-btn-full mt-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Kembali ke keranjang
            </a>

            <div class="mt-4 flex items-start gap-2 rounded-xl bg-emerald-50 p-3 text-[10px] leading-relaxed text-slate-600">
                <i data-lucide="shield-check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600"></i>
                <span>Pembayaran aman dengan enkripsi SSL dan garansi produk resmi.</span>
            </div>
        </form>
    </div>
</div>
<?php
bawah();
mysqli_close($conn);
?>
