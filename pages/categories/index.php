<?php
/**
 * pages/categories/index.php — katalog kategori + brand Nexatech.
 *
 * Alur:
 * 1. Ambil semua kategori beserta brand-nya dari repository.
 * 2. Tentukan kategori aktif dari ?kategori=; jika kosong/invalid pakai kategori pertama.
 * 3. Tentukan brand aktif dari ?brand=; brand harus milik kategori tersebut.
 * 4. Produk difilter dengan kombinasi kategori + brand, lalu ditampilkan.
 *
 * Kenapa memakai query string, bukan JavaScript?
 * - Filter tetap bekerja tanpa JavaScript dan URL bisa dibagikan/di-bookmark.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../repositories/categories.php";
require_once __DIR__ . "/../../repositories/brands.php";
require_once __DIR__ . "/../../repositories/products.php";
require_once __DIR__ . "/../../repositories/ratings.php";
require_once __DIR__ . "/../../components/landing/layout.php";

// Satu query mengambil kategori + relasi brand agar tidak query per kartu.
$kategori_list = ambil_kategori_dengan_brand($conn);

// Validasi kategori dari URL; nilai yang tidak dikenal diabaikan.
$kategori_id = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$selected = null;
foreach ($kategori_list as $kategori) {
    if ((int)$kategori['category_id'] === $kategori_id) {
        $selected = $kategori;
        break;
    }
}
if ($selected === null) {
    $selected = $kategori_list[0] ?? null;
}

// Brand hanya valid jika benar-benar terhubung ke kategori yang sedang aktif.
$brand_slug = trim($_GET['brand'] ?? '');
$brand_aktif = null;
if ($selected !== null) {
    foreach ($selected['brands'] as $brand) {
        if ($brand['brand_slug'] === $brand_slug) {
            $brand_aktif = $brand;
            break;
        }
    }
}
if ($brand_slug !== '' && $brand_aktif === null) {
    $brand_slug = '';
}

// Dua daftar: semua produk kategori (untuk hitungan "Semua") dan hasil filter brand.
$semua_produk_kategori = $selected ? ambil_produk_by_kategori($conn, $selected['category_id']) : [];
$products = $selected ? ambil_produk_by_kategori($conn, $selected['category_id'], $brand_slug) : [];
$rating_produk = ambil_semua_rating($conn);

// Menandai menu All Product aktif di header bersama.
$activeNav = 'products';
$pageTitle = $selected
    ? $selected['category_name'] . " - Kategori & Brand | Nexatech"
    : "Kategori & Brand | Nexatech";
atas($pageTitle);
?>
<div class="page-wrap">

    <section class="page-hero reveal" aria-labelledby="katalog-title">
        <div class="page-hero-copy">
            <p class="page-eyebrow">Katalog Nexatech</p>
            <h1 id="katalog-title" class="page-title">
                <?php echo $selected ? 'Jelajahi ' . htmlspecialchars($selected['category_name']) : 'Jelajahi kategori'; ?>
            </h1>
            <p class="page-subtitle">
                <?php if ($selected) { ?>
                    <?php echo htmlspecialchars($selected['cat_description']); ?>
                    Pilih brand favoritmu untuk mempersempit pencarian.
                <?php } else { ?>
                    Temukan produk teknologi berdasarkan kategori dan brand yang tersedia.
                <?php } ?>
            </p>
            <div class="page-hero-actions">
                <span class="th-badge th-badge-blue">
                    <i data-lucide="layers-3" class="w-3 h-3"></i>
                    <?php echo count($kategori_list); ?> kategori
                </span>
                <?php if ($selected) { ?>
                    <span class="th-badge th-badge-slate">
                        <i data-lucide="award" class="w-3 h-3"></i>
                        <?php echo count($selected['brands']); ?> brand
                    </span>
                <?php } ?>
            </div>
        </div>

        <div class="page-hero-icon" aria-hidden="true">
            <i data-lucide="<?php echo htmlspecialchars($selected['image'] ?? 'layout-grid'); ?>"></i>
        </div>
    </section>

    <!-- ================= KATEGORI ================= -->
    <section class="home-section" id="kategori" aria-labelledby="pilih-kategori-title">
        <div class="home-section-head">
            <div>
                <h2 id="pilih-kategori-title" class="th-section-title">Pilih kategori</h2>
                <p class="th-section-sub">Semua kategori produk dan layanan Nexatech.</p>
            </div>
            <a href="/PWL-SAS/mainpage.php#produk" class="th-btn th-btn-ghost th-btn-sm">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                Kembali ke beranda
            </a>
        </div>

        <div class="catalog-categories">
            <?php foreach ($kategori_list as $kategori) { ?>
                <?php $aktif = $selected && (int)$selected['category_id'] === (int)$kategori['category_id']; ?>
                <a
                    href="/PWL-SAS/pages/categories/index.php?kategori=<?php echo (int)$kategori['category_id']; ?>#brand"
                    class="catalog-category<?php echo $aktif ? ' is-active' : ''; ?>"
                    <?php echo $aktif ? 'aria-current="page"' : ''; ?>
                >
                    <span class="catalog-category-icon">
                        <i data-lucide="<?php echo htmlspecialchars($kategori['image'] ?: 'cpu'); ?>"></i>
                    </span>
                    <span class="catalog-category-copy">
                        <strong><?php echo htmlspecialchars($kategori['category_name']); ?></strong>
                        <small><?php echo count($kategori['brands']); ?> brand tersedia</small>
                    </span>
                    <i data-lucide="chevron-right" class="catalog-category-arrow"></i>
                </a>
            <?php } ?>
        </div>
    </section>

    <?php if ($selected) { ?>
        <!-- ================= BRAND ================= -->
        <section class="home-section" id="brand" aria-labelledby="brand-title">
            <div class="home-section-head">
                <div>
                    <h2 id="brand-title" class="th-section-title">Brand <?php echo htmlspecialchars($selected['category_name']); ?></h2>
                    <p class="th-section-sub">Pilih brand untuk melihat produk yang tersedia.</p>
                </div>
                <?php if ($brand_aktif) { ?>
                    <a href="/PWL-SAS/pages/categories/index.php?kategori=<?php echo (int)$selected['category_id']; ?>#produk" class="th-btn th-btn-ghost th-btn-sm">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        Reset brand
                    </a>
                <?php } ?>
            </div>

            <!-- Kartu "Semua" + brand milik kategori aktif. -->
            <div class="brand-grid">
                <a href="/PWL-SAS/pages/categories/index.php?kategori=<?php echo (int)$selected['category_id']; ?>#produk"
                   class="brand-card<?php echo $brand_aktif === null ? ' is-active' : ''; ?>">
                    <span class="brand-mark brand-mark-all"><i data-lucide="layout-grid"></i></span>
                    <span class="brand-card-copy">
                        <strong>Semua</strong>
                        <small><?php echo count($semua_produk_kategori); ?> produk</small>
                    </span>
                    <span class="brand-check"><i data-lucide="check"></i></span>
                </a>

                <?php foreach ($selected['brands'] as $brand) { ?>
                    <?php $brand_aktif_ini = $brand_aktif && $brand_aktif['brand_id'] === $brand['brand_id']; ?>
                    <a href="/PWL-SAS/pages/categories/index.php?kategori=<?php echo (int)$selected['category_id']; ?>&amp;brand=<?php echo urlencode($brand['brand_slug']); ?>#produk"
                       class="brand-card<?php echo $brand_aktif_ini ? ' is-active' : ''; ?>">
                        <span class="brand-mark"><?php echo htmlspecialchars(strtoupper(substr($brand['brand_name'], 0, 2))); ?></span>
                        <span class="brand-card-copy">
                            <strong><?php echo htmlspecialchars($brand['brand_name']); ?></strong>
                            <small>Lihat produk</small>
                        </span>
                        <span class="brand-check"><i data-lucide="check"></i></span>
                    </a>
                <?php } ?>
            </div>
        </section>

        <!-- ================= PRODUK ================= -->
        <section class="home-section" id="produk" aria-labelledby="produk-kategori-title">
            <div class="home-section-head">
                <div>
                    <h2 id="produk-kategori-title" class="th-section-title">
                        <?php echo $brand_aktif ? 'Produk ' . htmlspecialchars($brand_aktif['brand_name']) : 'Semua produk ' . htmlspecialchars($selected['category_name']); ?>
                    </h2>
                    <p class="th-section-sub">
                        <?php echo count($products); ?> produk ditemukan
                        <?php if ($brand_aktif) { ?>
                            untuk brand <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($brand_aktif['brand_name']); ?></span>
                        <?php } ?>
                    </p>
                </div>
                <a href="/PWL-SAS/mainpage.php#produk" class="th-btn th-btn-outline th-btn-sm">
                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                    Semua produk
                </a>
            </div>

            <?php if (empty($products)) { ?>
                <div class="empty-state">
                    <span class="empty-state-icon"><i data-lucide="package-search" class="w-5 h-5"></i></span>
                    <h3 class="empty-state-title">Produk belum tersedia</h3>
                    <p class="empty-state-copy">
                        <?php if ($brand_aktif) { ?>
                            Belum ada produk <?php echo htmlspecialchars($brand_aktif['brand_name']); ?> di kategori <?php echo htmlspecialchars($selected['category_name']); ?>. Coba brand lain atau lihat semua produk kategori ini.
                        <?php } else { ?>
                            Produk untuk kategori <?php echo htmlspecialchars($selected['category_name']); ?> akan segera hadir.
                        <?php } ?>
                    </p>
                    <a href="/PWL-SAS/pages/categories/index.php?kategori=<?php echo (int)$selected['category_id']; ?>#produk" class="th-btn th-btn-outline th-btn-sm mt-2">
                        Lihat semua brand
                    </a>
                </div>
            <?php } else { ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <?php foreach ($products as $row) { ?>
                        <?php
                        $r = $rating_produk[$row['product_id']] ?? null;
                        $rata = $r ? round((float)$r['rata_rata'], 1) : 0;
                        $jumlah_rating = $r ? (int)$r['jumlah'] : 0;
                        $label_produk = trim(($row['brand_name'] ?? '') . ' · ' . ($row['category_name'] ?? ''), ' ·');
                        ?>
                        <article class="product-card">
                            <div>
                                <div class="product-media">
                                    <img src="<?php echo htmlspecialchars($row['url'] ?? ''); ?>"
                                         alt="<?php echo htmlspecialchars($row['product_name']); ?>"
                                         loading="lazy">
                                </div>

                                <span class="product-category">
                                    <i data-lucide="tag" class="w-3 h-3"></i>
                                    <?php echo htmlspecialchars($label_produk !== '' ? $label_produk : 'Produk'); ?>
                                </span>

                                <h3 class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></h3>
                                <p class="product-desc"><?php echo htmlspecialchars($row['description'] ?? ''); ?></p>

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
                                    <span class="product-price">Rp <?php echo number_format((int)$row['price'], 0, ',', '.'); ?></span>
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
    <?php } ?>
</div>
<?php
bawah();
mysqli_close($conn);
?>
