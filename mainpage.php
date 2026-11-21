<?php
/**
 * mainpage.php — beranda toko Nexatech.
 *
 * Tugas halaman:
 * - Menampilkan hero, 6 kartu kategori + brand, dan daftar produk.
 * - Meneruskan pencarian produk lewat parameter ?q=.
 * - Memakai layout/header/footer bersama agar tampilan sama dengan halaman lain.
 *
 * Kenapa logika data dipisah ke repositories?
 * - Halaman tetap fokus pada tampilan, sedangkan query SQL dan aturan data
 *   berada di repositories/products.php, categories.php, brands.php, dan ratings.php.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Repositori = kumpulan fungsi query; layout = kerangka HTML publik.
require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/repositories/products.php";
require_once __DIR__ . "/repositories/categories.php";
require_once __DIR__ . "/repositories/brands.php";
require_once __DIR__ . "/repositories/ratings.php";
require_once __DIR__ . "/components/landing/layout.php";

$pesan = "";

// Aksi hapus dari halaman lama tetap dipertahankan; redirect mencegah submit ganda.
if (isset($_POST['hapus'])) {
    $id = (int)$_POST['id'];
    if ($id > 0 && hapus_produk($conn, $id)) {
        $pesan = "Data berhasil dihapus";
    } else {
        $pesan = "Gagal hapus";
    }
    header("Location: /PWL-SAS/mainpage.php");
    exit;
}

// Data utama beranda: produk (opsional difilter keyword), kategori+brand, dan rating.
$products = ambil_produk($conn);
$keyword = trim($_GET['q'] ?? '');
if ($keyword !== '') {
    $products = ambil_produk_by_keyword($conn, $keyword);
}
$categories = ambil_kategori_dengan_brand($conn);
$rating_produk = ambil_semua_rating($conn);

// Menandai menu Homepage aktif di header bersama.
$activeNav = 'home';
$pageTitle = "Nexatech - Solusi Teknologi & Layanan Teknisi";
atas($pageTitle);
?>
<div class="page-wrap">

    <?php if ($pesan != "") { ?>
        <div class="th-alert th-alert-success mb-4" role="status">
            <i data-lucide="check-circle-2" class="w-4 h-4 shrink-0"></i>
            <span><?php echo htmlspecialchars($pesan); ?></span>
        </div>
    <?php } ?>

    <!-- ================= HERO ================= -->
    <section class="home-hero reveal" aria-labelledby="home-title">
        <div class="home-hero-copy">
            <span class="home-badge">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                Nexatech 2.0
            </span>

            <h1 id="home-title" class="home-title">
                Solusi teknologi lengkap untuk <span>bisnis dan rumahan</span>
            </h1>

            <p class="home-copy">
                Beli produk teknologi, pesan jasa teknisi, dan kelola kebutuhan perangkat Anda
                dari satu tempat yang cepat, rapi, dan mudah dipahami.
            </p>

            <div class="home-actions">
                <a href="#produk" class="th-btn th-btn-primary">
                    Belanja sekarang
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
                <a href="/PWL-SAS/pages/technician/" class="th-btn th-btn-outline">
                    <i data-lucide="wrench" class="w-4 h-4"></i>
                    Pesan teknisi
                </a>
            </div>

            <div class="home-trust">
                <span class="home-trust-item">
                    <i data-lucide="shield-check"></i>
                    Garansi resmi
                </span>
                <span class="home-trust-item">
                    <i data-lucide="headphones"></i>
                    Layanan 24/7
                </span>
                <span class="home-trust-item">
                    <i data-lucide="badge-check"></i>
                    Teknisi terverifikasi
                </span>
            </div>
        </div>

        <div class="home-media" aria-hidden="true">
            <div class="home-media-frame">
                <img
                    src="https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=900&auto=format&fit=crop&q=80"
                    alt=""
                    loading="eager"
                >
            </div>

            <div class="home-float home-float-top">
                <span class="home-float-icon"><i data-lucide="star" class="w-4 h-4 fill-amber-400 text-amber-400"></i></span>
                <div>
                    <strong>4.9 / 5.0</strong>
                    <span>Rating pelanggan</span>
                </div>
            </div>

            <div class="home-float home-float-bottom">
                <span class="home-float-icon"><i data-lucide="user-round-check" class="w-4 h-4"></i></span>
                <div>
                    <strong>Teknisi siap membantu</strong>
                    <span>Online hari ini</span>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= KATEGORI ================= -->
    <section class="home-section" id="kategori" aria-labelledby="kategori-title">
        <div class="home-section-head">
            <div>
                <h2 id="kategori-title" class="th-section-title">Kategori produk &amp; layanan</h2>
                <p class="th-section-sub">Pilih kategori, lalu jelajahi brand yang tersedia di dalamnya.</p>
            </div>
            <a href="/PWL-SAS/pages/categories/index.php" class="th-btn th-btn-ghost th-btn-sm">
                Lihat semua kategori
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <!--
            Setiap kartu memakai data dari category_brands.
            Brand ditampilkan sebagai pill agar admin tidak perlu mengubah HTML
            ketika menambah/mengurangi brand pada suatu kategori.
        -->
        <div class="home-category-grid grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3.5">
            <?php foreach ($categories as $cat) { ?>
                <a href="/PWL-SAS/pages/categories/index.php?kategori=<?php echo (int)$cat['category_id']; ?>" class="category-card">
                    <div class="category-card-top">
                        <span class="category-icon">
                            <i data-lucide="<?php echo htmlspecialchars($cat['image'] ?: 'cpu'); ?>"></i>
                        </span>
                        <span class="category-count"><?php echo count($cat['brands']); ?> brand</span>
                    </div>
                    <div>
                        <h3 class="text-[12.5px] font-extrabold text-slate-900">
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </h3>
                        <p class="mt-1 text-[10.5px] leading-relaxed text-slate-500 line-clamp-2">
                            <?php echo htmlspecialchars($cat['cat_description']); ?>
                        </p>
                    </div>
                    <div class="category-brands">
                        <?php foreach ($cat['brands'] as $brand) { ?>
                            <span class="brand-pill"><?php echo htmlspecialchars($brand['brand_name']); ?></span>
                        <?php } ?>
                    </div>
                </a>
            <?php } ?>
        </div>
    </section>

    <!-- ================= PRODUK ================= -->
    <section class="home-section" id="produk" aria-labelledby="produk-title">
        <div class="home-section-head">
            <div>
                <h2 id="produk-title" class="th-section-title">
                    <?php echo $keyword !== '' ? 'Hasil pencarian' : 'Produk populer'; ?>
                </h2>
                <p class="th-section-sub">
                    <?php if ($keyword !== '') { ?>
                        <?php echo count($products); ?> hasil untuk "<span class="font-semibold text-slate-700"><?php echo htmlspecialchars($keyword); ?></span>"
                        <a href="/PWL-SAS/mainpage.php#produk" class="ml-1 font-semibold text-brand-600 hover:underline">Reset</a>
                    <?php } else { ?>
                        Rekomendasi perangkat untuk kerja, belajar, dan kreativitas.
                    <?php } ?>
                </p>
            </div>

            <form action="/PWL-SAS/mainpage.php#produk" method="GET" class="home-search" role="search">
                <span class="home-search-icon"><i data-lucide="search" class="w-4 h-4"></i></span>
                <input type="search" name="q" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Cari laptop, mouse, aksesori...">
                <button type="submit" class="th-btn th-btn-primary">Cari</button>
            </form>
        </div>

        <?php if (empty($products)) { ?>
            <div class="empty-state">
                <span class="empty-state-icon"><i data-lucide="search-x" class="w-5 h-5"></i></span>
                <h3 class="empty-state-title">Produk tidak ditemukan</h3>
                <p class="empty-state-copy">Coba kata kunci lain, misalnya "laptop" atau "mouse".</p>
                <a href="/PWL-SAS/mainpage.php#produk" class="th-btn th-btn-outline th-btn-sm mt-2">Lihat semua produk</a>
            </div>
        <?php } else { ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <?php foreach ($products as $row) { ?>
                    <?php
                    // Rating diambil sekali untuk semua produk agar tidak query di dalam loop.
                    $r = $rating_produk[$row['product_id']] ?? null;
                    $rata = $r ? round((float)$r['rata_rata'], 1) : 0;
                    $jumlah_rating = $r ? (int)$r['jumlah'] : 0;
                    ?>
                    <article class="product-card">
                        <div>
                            <div class="product-media">
                                <img src="<?php echo htmlspecialchars($row['url']); ?>"
                                     alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                                     loading="lazy">
                            </div>

                            <span class="product-category">
                                <i data-lucide="tag" class="w-3 h-3"></i>
                                <?php
                                $label_produk = trim(($row['brand_name'] ?? '') . ' · ' . ($row['category_name'] ?? ''), ' ·');
                                echo htmlspecialchars($label_produk !== '' ? $label_produk : 'Produk');
                                ?>
                            </span>

                            <h3 class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></h3>
                            <p class="product-desc"><?php echo htmlspecialchars($row['description']); ?></p>

                            <div class="mt-2 flex items-center gap-1">
                                <?php for ($i = 1; $i <= 5; $i++) { ?>
                                    <i data-lucide="star" class="w-3 h-3 <?php echo $i <= round($rata) ? 'text-amber-400 fill-amber-400' : 'text-slate-300'; ?>"></i>
                                <?php } ?>
                                <span class="ml-1 text-[10px] text-slate-500">
                                    <?php echo $jumlah_rating > 0 ? number_format($rata, 1, ',', '.') . ' (' . $jumlah_rating . ')' : 'Belum ada rating'; ?>
                                </span>
                            </div>
                        </div>

                        <div class="product-footer">
                            <div class="flex items-center justify-between gap-2">
                                <span class="product-price">Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></span>
                                <span class="th-badge th-badge-green">Stok tersedia</span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 mt-3">
                                <a href="/PWL-SAS/views/products/laptop.php?id=<?php echo (int)$row['product_id']; ?>" class="th-btn th-btn-outline th-btn-sm">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    Lihat
                                </a>
                                <a href="/PWL-SAS/actions/cart.php?aksi=tambah&jenis=produk&id=<?php echo (int)$row['product_id']; ?>" class="th-btn th-btn-primary th-btn-sm">
                                    <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                                    Keranjang
                                </a>
                            </div>
                        </div>
                    </article>
                <?php } ?>
            </div>
        <?php } ?>
    </section>

    <!-- ================= CTA TEKNISI ================= -->
    <section class="home-cta">
        <div class="home-cta-copy">
            <h2 class="home-cta-title">Perangkat bermasalah? Teknisi kami siap datang.</h2>
            <p class="home-cta-sub">
                Pilih teknisi, tentukan layanan dan jadwal kunjungan. Proses cepat, harga transparan,
                dan teknisi terverifikasi.
            </p>
        </div>
        <a href="/PWL-SAS/pages/technician/" class="th-btn th-btn-primary">
            Booking teknisi
            <i data-lucide="arrow-right" class="w-4 h-4"></i>
        </a>
    </section>
</div>
<?php
bawah();
mysqli_close($conn);
?>
