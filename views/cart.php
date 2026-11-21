<?php
/**
 * Halaman keranjang belanja.
 *
 * Menampilkan isi $_SESSION['cart'] (produk) dan $_SESSION['layanan'] (jasa
 * teknisi), lalu mengambil detail tiap item dari tabel products/services untuk
 * menghitung subtotal. Halaman ini bagian dari alur belanja: pengguna dapat
 * mengubah jumlah lewat actions/cart.php, menghapus item, menambah layanan,
 * lalu lanjut ke checkout.php bila total lebih dari nol. Wajib login.
 */
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../components/landing/layout.php";

cek_login();

// Isi keranjang disimpan di session; inisialisasi agar foreach di bawah aman saat pertama kali dibuka.
if (!isset($_SESSION['cart']))    { $_SESSION['cart'] = []; }
if (!isset($_SESSION['layanan'])) { $_SESSION['layanan'] = []; }

// Akumulator yang diisi saat me-loop item, lalu dipakai di panel ringkasan kanan.
$subtotal_produk  = 0;
$subtotal_layanan = 0;
$jumlah_produk    = 0;
$jumlah_layanan   = 0;

$activeNav = 'cart';
atas("Keranjang - Nexatech");
?>
<div class="page-wrap page-wrap-narrow">

    <section class="page-hero reveal" aria-labelledby="cart-title">
        <div class="page-hero-copy">
            <p class="page-eyebrow">Your Cart</p>
            <h1 id="cart-title" class="page-title">Keranjang belanja</h1>
            <p class="page-subtitle">Lengkapi pesanan produk dan layanan teknisi Anda sebelum melanjutkan ke checkout.</p>
            <div class="page-hero-actions">
                <a href="/PWL-SAS/mainpage.php#produk" class="th-btn th-btn-outline th-btn-sm">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    Lanjut belanja
                </a>
            </div>
        </div>
        <span class="page-hero-icon" aria-hidden="true"><i data-lucide="shopping-cart"></i></span>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">

        <!-- ================= KOLOM KIRI ================= -->
        <div class="lg:col-span-8 space-y-3.5">

            <?php
            // Loop produk di session; detail harga/nama diambil ulang dari DB agar data selalu terbaru.
            foreach ($_SESSION['cart'] as $id => $jumlah) {
                $id = (int)$id;
                $query  = mysqli_query($conn, "SELECT * FROM products WHERE product_id = $id");
                $produk = mysqli_fetch_assoc($query);
                if (!$produk) { continue; }

                $subtotal         = $produk['price'] * $jumlah;
                $subtotal_produk += $subtotal;
                $jumlah_produk++;
            ?>
                <article class="cart-item">
                    <div class="cart-thumb">
                        <img src="<?php echo htmlspecialchars($produk['url']); ?>" alt="<?php echo htmlspecialchars($produk['product_name']); ?>">
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="th-badge th-badge-blue">Produk</span>
                            <a href="../actions/cart.php?aksi=hapus&jenis=produk&id=<?php echo $id; ?>"
                               class="ml-auto inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600"
                               aria-label="Hapus <?php echo htmlspecialchars($produk['product_name']); ?>">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>

                        <h3 class="mt-1.5 text-[13px] font-extrabold text-slate-900"><?php echo htmlspecialchars($produk['product_name']); ?></h3>
                        <p class="mt-0.5 truncate text-[10.5px] text-slate-500"><?php echo htmlspecialchars($produk['description']); ?></p>

                        <!-- Tombol jumlah mengubah session lewat actions/cart.php, lalu halaman dimuat ulang. -->
                        <div class="qty-control">
                            <a href="../actions/cart.php?aksi=kurang&jenis=produk&id=<?php echo $id; ?>" class="qty-btn" aria-label="Kurangi jumlah">&minus;</a>
                            <span class="min-w-4 text-center text-[11px] font-bold text-slate-900"><?php echo (int)$jumlah; ?></span>
                            <a href="../actions/cart.php?aksi=tambah&jenis=produk&id=<?php echo $id; ?>" class="qty-btn qty-plus" aria-label="Tambah jumlah">+</a>
                        </div>
                    </div>

                    <div class="shrink-0 text-right">
                        <p class="text-[9.5px] font-medium uppercase tracking-wide text-slate-400">Subtotal</p>
                        <p class="mt-1 text-[13px] font-extrabold text-slate-900">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></p>
                    </div>
                </article>
            <?php } ?>

            <?php
            // Sama seperti produk, layanan diambil dari tabel services berdasarkan ID di session.
            foreach ($_SESSION['layanan'] as $id => $jumlah) {
                $id = (int)$id;
                $query   = mysqli_query($conn, "SELECT * FROM services WHERE service_id = $id");
                $layanan = mysqli_fetch_assoc($query);
                if (!$layanan) { continue; }

                $subtotal          = $layanan['base_price'] * $jumlah;
                $subtotal_layanan += $subtotal;
                $jumlah_layanan++;
            ?>
                <article class="cart-item">
                    <div class="cart-service-icon">
                        <i data-lucide="wrench" class="w-6 h-6"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="th-badge th-badge-blue">Layanan</span>
                            <a href="../actions/cart.php?aksi=hapus&jenis=layanan&id=<?php echo $id; ?>"
                               class="ml-auto inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-100 text-slate-400 transition-colors hover:bg-red-50 hover:text-red-600"
                               aria-label="Hapus <?php echo htmlspecialchars($layanan['service_name']); ?>">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>

                        <h3 class="mt-1.5 text-[13px] font-extrabold text-slate-900"><?php echo htmlspecialchars($layanan['service_name']); ?></h3>
                        <p class="mt-0.5 text-[10.5px] leading-relaxed text-slate-500"><?php echo htmlspecialchars($layanan['description']); ?></p>

                        <!-- Tombol jumlah mengubah session lewat actions/cart.php, lalu halaman dimuat ulang. -->
                        <div class="qty-control">
                            <a href="../actions/cart.php?aksi=kurang&jenis=layanan&id=<?php echo $id; ?>" class="qty-btn" aria-label="Kurangi jumlah">&minus;</a>
                            <span class="min-w-4 text-center text-[11px] font-bold text-slate-900"><?php echo (int)$jumlah; ?></span>
                            <a href="../actions/cart.php?aksi=tambah&jenis=layanan&id=<?php echo $id; ?>" class="qty-btn qty-plus" aria-label="Tambah jumlah">+</a>
                        </div>
                    </div>

                    <div class="shrink-0 text-right">
                        <p class="text-[9.5px] font-medium uppercase tracking-wide text-slate-400">Subtotal</p>
                        <p class="mt-1 text-[13px] font-extrabold text-slate-900">Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></p>
                    </div>
                </article>
            <?php } ?>

            <?php // Empty state hanya muncul kalau tidak ada produk maupun layanan sama sekali. ?>
            <?php if ($jumlah_produk == 0 && $jumlah_layanan == 0) { ?>
                <div class="empty-state">
                    <span class="empty-state-icon"><i data-lucide="shopping-bag" class="w-5 h-5"></i></span>
                    <h3 class="empty-state-title">Keranjang masih kosong</h3>
                    <p class="empty-state-copy">Yuk, pilih produk atau layanan teknisi yang Anda butuhkan.</p>
                    <a href="../mainpage.php#produk" class="th-btn th-btn-primary th-btn-sm mt-2">
                        Mulai belanja
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            <?php } ?>

            <?php
            // Daftar seluruh layanan untuk tombol tambah cepat; hanya dirender jika tabel services berisi data.
            $daftar = mysqli_query($conn, "SELECT * FROM services");
            if (mysqli_num_rows($daftar) > 0) {
            ?>
                <div class="th-card th-card-pad">
                    <div class="flex items-center justify-between gap-3 flex-wrap">
                        <div>
                            <h2 class="text-[12.5px] font-extrabold text-slate-900">Tambah layanan teknisi</h2>
                            <p class="mt-0.5 text-[10.5px] text-slate-500">Layanan profesional langsung dari teknisi terverifikasi.</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <?php while ($s = mysqli_fetch_assoc($daftar)) { ?>
                                <a href="../actions/cart.php?aksi=tambah&jenis=layanan&id=<?php echo (int)$s['service_id']; ?>" class="th-btn th-btn-outline th-btn-sm">
                                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                    <?php echo htmlspecialchars($s['service_name']); ?>
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>

        <!-- ================= KOLOM KANAN ================= -->
        <aside class="lg:col-span-4 space-y-3.5 lg:sticky lg:top-24">
            <div class="th-card th-card-pad">
                <div class="th-card-head">
                    <div>
                        <h2 class="th-card-title">Ringkasan pesanan</h2>
                        <p class="th-card-sub"><?php echo $jumlah_produk; ?> produk &bull; <?php echo $jumlah_layanan; ?> layanan</p>
                    </div>
                    <span class="th-badge th-badge-blue"><?php echo $jumlah_produk + $jumlah_layanan; ?> item</span>
                </div>

                <div class="summary-list">
                    <div class="summary-list-row">
                        <span>Subtotal produk</span>
                        <strong>Rp <?php echo number_format($subtotal_produk, 0, ',', '.'); ?></strong>
                    </div>
                    <div class="summary-list-row">
                        <span>Subtotal layanan</span>
                        <strong>Rp <?php echo number_format($subtotal_layanan, 0, ',', '.'); ?></strong>
                    </div>
                    <div class="summary-list-row">
                        <span>Biaya layanan</span>
                        <strong class="text-emerald-600">Gratis</strong>
                    </div>
                </div>

                <?php $total = $subtotal_produk + $subtotal_layanan; ?>

                <!-- Total dipakai juga untuk menentukan apakah tombol checkout aktif. -->
                <div class="summary-total mt-3">
                    <span>Total</span>
                    <strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong>
                </div>

                <?php if ($total > 0) { ?>
                    <!-- Checkout hanya bisa dilanjutkan bila ada item berharga di keranjang. -->
                    <a href="checkout.php" class="th-btn th-btn-primary th-btn-full mt-4">
                        Lanjut ke checkout
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                <?php } else { ?>
                    <!-- Total nol: tampilkan tombol nonaktif supaya alur checkout tidak dilanjutkan. -->
                    <span class="th-btn th-btn-primary th-btn-full mt-4 is-disabled" aria-disabled="true">Lanjut ke checkout</span>
                <?php } ?>

                <a href="../mainpage.php#produk" class="th-btn th-btn-outline th-btn-full mt-2">Lanjut belanja</a>

                <div class="mt-4 flex items-start gap-2 rounded-xl bg-slate-50 p-3 text-[10px] leading-relaxed text-slate-500">
                    <i data-lucide="shield-check" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600"></i>
                    <span>Pembayaran aman, garansi resmi, dan jadwal teknisi bisa diubah.</span>
                </div>
            </div>

            <div class="th-card th-card-pad">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <i data-lucide="headphones" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="text-[12px] font-extrabold text-slate-900">Butuh bantuan?</h3>
                        <p class="mt-0.5 text-[10.5px] text-slate-500">Senin-Jumat, 09.00-18.00 WIB</p>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
<?php
bawah();
mysqli_close($conn);
?>
