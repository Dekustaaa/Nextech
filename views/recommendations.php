<?php
/**
 * Halaman Nexsearch: rekomendasi produk berdasarkan budget.
 *
 * Jika parameter GET budget terisi, halaman mencari produk dengan harga di bawah
 * budget (opsional difilter kategori), mengambil produk termahal sebagai
 * "paling cocok", lalu menampilkan maksimal 6 alternatif lain. Bila tidak ada
 * yang masuk budget, produk termurah ditampilkan sebagai saran. Rating produk
 * diambil dari repositories/ratings.php. Form pencarian mengirim GET ke halaman
 * ini sendiri agar hasil bisa di-refresh/dibagikan lewat URL.
 */
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/ratings.php";
require_once __DIR__ . "/../components/landing/layout.php";

$budget   = 0;
$kategori = 0;
$terbaik  = null;
$lainnya  = [];
$termurah = null;
$sudah_cari = false;

// ---------- Kalau form sudah dikirim ----------
// Pencarian memakai GET agar hasil bisa di-bookmark atau dibagikan lewat URL.
if (isset($_GET['budget']) && $_GET['budget'] != '') {

    $sudah_cari = true;

    // Bersihkan karakter non-angka (mis. titik pemisah ribuan) sebelum dikonversi ke integer.
    $budget   = (int) preg_replace('/\D/', '', $_GET['budget']);
    $kategori = (int) ($_GET['kategori'] ?? 0);

    // Filter kategori dibangun kondisional; nilai sudah di-cast int sehingga aman disisipkan ke query.
    $where_kat = "";
    if ($kategori > 0) {
        $where_kat = "AND p.category_id = $kategori";
    }

    $query = mysqli_query($conn, "SELECT p.*, c.category_name, b.brand_name
                                  FROM products p
                                  LEFT JOIN categories c ON p.category_id = c.category_id
                                  LEFT JOIN brands b ON p.brand_id = b.brand_id
                                  WHERE p.price <= $budget $where_kat
                                  ORDER BY p.price DESC");

    // Kumpulkan hasil query ke array agar mudah diambil produk pertama dan sisanya.
    $hasil = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $hasil[] = $row;
    }

    if (count($hasil) > 0) {
        // Produk termahal yang masih masuk budget dianggap paling "worth it" untuk ditonjolkan.
        $terbaik = $hasil[0];
        $lainnya = array_slice($hasil, 1, 6);
    } else {
        // Tidak ada yang cocok: cari produk termurah (masih menghormati filter kategori) sebagai saran.
        $q2 = mysqli_query($conn, "SELECT p.*, c.category_name, b.brand_name
                                   FROM products p
                                   LEFT JOIN categories c ON p.category_id = c.category_id
                                   LEFT JOIN brands b ON p.brand_id = b.brand_id
                                   WHERE 1=1 $where_kat
                                   ORDER BY p.price ASC LIMIT 1");
        $termurah = mysqli_fetch_assoc($q2);
    }
}

// Daftar kategori untuk dropdown
$daftar_kategori = mysqli_query($conn, "SELECT * FROM categories");
// Rating dipakai untuk bintang pada kartu terbaik dan kartu alternatif.
$rating_produk = get_all_ratings($conn);

$activeNav = 'nexsearch';
page_start("Nexsearch - Cari Sesuai Budget - Nexatech");
?>

<div class="page-wrap page-wrap-narrow">

    <section class="page-hero reveal" aria-labelledby="reco-title">
        <div class="page-hero-copy">
            <p class="page-eyebrow">Nexsearch</p>
            <h1 id="reco-title" class="page-title">Cari produk sesuai budget</h1>
            <p class="page-subtitle">Masukkan budget dan kategori, kami carikan produk yang paling pas untuk Anda.</p>
        </div>
        <span class="page-hero-icon" aria-hidden="true"><i data-lucide="search"></i></span>
    </section>

    <!-- ================= FORM BUDGET ================= -->
    <!-- action kosong berarti submit ke halaman ini sendiri (GET), mempertahankan parameter di URL. -->
    <form action="" method="GET" class="th-card th-card-pad">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-12 md:items-end">
            <div class="md:col-span-5">
                <label for="budget" class="th-label"><i data-lucide="wallet"></i> Budget Anda (Rp)</label>
                <input type="text" id="budget" name="budget" placeholder="contoh: 10000000"
                       value="<?php echo $budget > 0 ? $budget : ''; ?>"
                       class="th-input" inputmode="numeric">
            </div>

            <div class="md:col-span-4">
                <label for="kategori" class="th-label"><i data-lucide="layout-grid"></i> Kategori</label>
                <select name="kategori" id="kategori" class="th-select">
                    <option value="0">Semua kategori</option>
                    <?php // Opsi yang sedang dipilih ditandai selected supaya filter terlihat setelah submit. ?>
                    <?php while ($k = mysqli_fetch_assoc($daftar_kategori)) { ?>
                        <option value="<?php echo (int)$k['category_id']; ?>"
                            <?php if ($kategori == $k['category_id']) { echo 'selected'; } ?>>
                            <?php echo htmlspecialchars($k['category_name']); ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="md:col-span-3">
                <button type="submit" class="th-btn th-btn-primary th-btn-full md:min-h-[43px]">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    Cari rekomendasi
                </button>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center gap-2 text-[10px] text-slate-500">
            <span class="font-semibold">Contoh budget:</span>
            <?php // Pintasan budget umum; kategori yang aktif ikut diteruskan. ?>
            <?php foreach ([5000000, 10000000, 15000000, 20000000] as $contoh) { ?>
                <a href="?budget=<?php echo $contoh; ?>&kategori=<?php echo (int)$kategori; ?>"
                   class="rounded-full border border-slate-200 bg-white px-2.5 py-1 font-semibold text-slate-600 transition-colors hover:border-brand-200 hover:bg-brand-50 hover:text-brand-700">
                    Rp <?php echo number_format($contoh, 0, ',', '.'); ?>
                </a>
            <?php } ?>
        </div>
    </form>

    <?php if ($sudah_cari) { ?>

        <?php if ($terbaik) { ?>

            <?php
            // Hitung sisa budget dan persentase pemakaian untuk progress bar.
            $sisa   = $budget - $terbaik['price'];
            $persen = $budget > 0 ? min(100, round($terbaik['price'] / $budget * 100)) : 0;
            ?>

            <!-- ================= PRODUK PALING COCOK ================= -->
            <section class="relative mt-5 rounded-[20px] border-2 border-brand-500 bg-white p-4 shadow-[0_24px_46px_-32px_rgba(37,99,235,0.5)] sm:p-5">
                <span class="absolute left-5 top-0 -translate-y-1/2 rounded-full bg-gradient-to-r from-brand-600 to-brand-700 px-3.5 py-1.5 text-[9.5px] font-extrabold text-white shadow-sm">
                    <i data-lucide="award" class="mr-1 inline h-3 w-3"></i>
                    Paling cocok untuk budget Anda
                </span>

                <div class="mt-4 grid grid-cols-1 items-center gap-5 md:grid-cols-12">
                    <div class="md:col-span-5">
                        <div class="overflow-hidden rounded-2xl bg-slate-100">
                            <img src="<?php echo htmlspecialchars($terbaik['url']); ?>" alt="<?php echo htmlspecialchars($terbaik['product_name']); ?>"
                                 class="h-64 w-full object-cover">
                        </div>
                    </div>

                    <div class="md:col-span-7">
                        <span class="th-badge th-badge-blue mb-2">Best match</span>
                        <h2 class="text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl"><?php echo htmlspecialchars($terbaik['product_name']); ?></h2>
                        <p class="mt-1 text-[10px] font-extrabold uppercase tracking-wide text-brand-600">
                            <?php echo htmlspecialchars(trim(($terbaik['brand_name'] ?? '') . ' · ' . ($terbaik['category_name'] ?? ''), ' ·')); ?>
                        </p>
                        <p class="mt-1 text-[11.5px] leading-relaxed text-slate-500"><?php echo htmlspecialchars($terbaik['description']); ?></p>

                        <?php $r_terbaik = $rating_produk[$terbaik['product_id']] ?? null; ?>
                        <div class="mt-2 flex items-center gap-1">
                            <?php // Bintang diisi sesuai rata-rata rating; produk tanpa rating memakai bintang abu-abu. ?>
                            <?php for ($i = 1; $i <= 5; $i++) { ?>
                                <i data-lucide="star" class="w-4 h-4 <?php echo $r_terbaik && $i <= round((float)$r_terbaik['rata_rata']) ? "text-amber-400 fill-amber-400" : "text-slate-300"; ?>"></i>
                            <?php } ?>
                            <span class="ml-1 text-[11px] text-slate-500">
                                <?php echo $r_terbaik ? number_format((float)$r_terbaik['rata_rata'], 1, ",", ".") . " (" . (int)$r_terbaik['jumlah'] . " ulasan)" : "Belum ada rating"; ?>
                            </span>
                        </div>

                        <p class="mt-3 text-2xl font-extrabold tracking-tight text-brand-600">
                            Rp <?php echo number_format($terbaik['price'], 0, ',', '.'); ?>
                        </p>

                        <div class="mt-3">
                            <div class="mb-1 flex justify-between text-[10px] text-slate-500">
                                <span>Menggunakan <?php echo $persen; ?>% budget</span>
                                <span>Sisa Rp <?php echo number_format($sisa, 0, ',', '.'); ?></span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                <div class="h-2 rounded-full bg-gradient-to-r from-brand-500 to-brand-700" style="width: <?php echo $persen; ?>%"></div>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <!-- Aksi cepat: tambah ke keranjang lewat action, atau buka halaman detail produk. -->
                            <a href="../actions/cart.php?aksi=tambah&jenis=produk&id=<?php echo (int)$terbaik['product_id']; ?>"
                               class="th-btn th-btn-primary">
                                <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                                Tambah ke keranjang
                            </a>
                            <a href="products/laptop.php?id=<?php echo (int)$terbaik['product_id']; ?>"
                               class="th-btn th-btn-outline">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                                Lihat detail
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ================= PILIHAN LAINNYA ================= -->
            <?php if (count($lainnya) > 0) { ?>
                <section class="mt-6">
                    <div class="home-section-head">
                        <div>
                            <h2 class="th-section-title">Pilihan lain dalam budget Anda</h2>
                            <p class="th-section-sub">Semua harga di bawah Rp <?php echo number_format($budget, 0, ',', '.'); ?></p>
                        </div>
                        <span class="th-badge th-badge-slate"><?php echo count($lainnya); ?> rekomendasi</span>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <?php foreach ($lainnya as $p) { ?>
                            <?php // Ambil rating per produk; null berarti belum ada ulasan. ?>
                            <?php $r_lain = $rating_produk[$p['product_id']] ?? null; ?>
                            <article class="product-card">
                                <div>
                                    <div class="product-media !h-40">
                                        <img src="<?php echo htmlspecialchars($p['url']); ?>" alt="<?php echo htmlspecialchars($p['product_name']); ?>" loading="lazy">
                                    </div>
                                    <span class="product-category">
                                        <i data-lucide="tag" class="w-3 h-3"></i>
                                        <?php echo htmlspecialchars(trim(($p['brand_name'] ?? '') . ' · ' . ($p['category_name'] ?? ''), ' ·')); ?>
                                    </span>
                                    <h3 class="product-name"><?php echo htmlspecialchars($p['product_name']); ?></h3>
                                    <p class="product-desc"><?php echo htmlspecialchars($p['description']); ?></p>

                                    <div class="mt-2 flex items-center gap-1">
                                        <?php for ($i = 1; $i <= 5; $i++) { ?>
                                            <i data-lucide="star" class="w-3 h-3 <?php echo $r_lain && $i <= round((float)$r_lain['rata_rata']) ? "text-amber-400 fill-amber-400" : "text-slate-300"; ?>"></i>
                                        <?php } ?>
                                        <span class="ml-1 text-[10px] text-slate-500">
                                            <?php echo $r_lain ? number_format((float)$r_lain['rata_rata'], 1, ",", ".") . " (" . (int)$r_lain['jumlah'] . ")" : "-"; ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="product-footer">
                                    <div class="flex items-end justify-between gap-2">
                                        <span class="product-price">Rp <?php echo number_format($p['price'], 0, ',', '.'); ?></span>
                                        <span class="text-[9.5px] font-semibold text-emerald-600">
                                            Sisa Rp <?php echo number_format($budget - $p['price'], 0, ',', '.'); ?>
                                        </span>
                                    </div>
                                    <div class="mt-3 grid grid-cols-2 gap-2">
                                        <a href="products/laptop.php?id=<?php echo (int)$p['product_id']; ?>" class="th-btn th-btn-outline th-btn-sm">
                                            Lihat
                                        </a>
                                        <a href="../actions/cart.php?aksi=tambah&jenis=produk&id=<?php echo (int)$p['product_id']; ?>" class="th-btn th-btn-primary th-btn-sm">
                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                            Keranjang
                                        </a>
                                    </div>
                                </div>
                            </article>
                        <?php } ?>
                    </div>
                </section>
            <?php } ?>

        <?php } else { ?>

            <!-- ================= TIDAK ADA YANG COCOK ================= -->
            <!-- Empty state memberi konteks: produk termurah dan selisih budget bila datanya ada. -->
            <div class="empty-state mt-5">
                <span class="empty-state-icon !bg-amber-50 !text-amber-500"><i data-lucide="search-x" class="w-5 h-5"></i></span>
                <h3 class="empty-state-title">Belum ada produk dalam budget ini</h3>

                <?php if ($termurah) { ?>
                    <p class="empty-state-copy">
                        Produk termurah kami adalah <b><?php echo htmlspecialchars($termurah['product_name']); ?></b>
                        seharga Rp <?php echo number_format($termurah['price'], 0, ',', '.'); ?>.
                        Budget Anda kurang Rp <?php echo number_format($termurah['price'] - $budget, 0, ',', '.'); ?>.
                    </p>
                    <a href="products/laptop.php?id=<?php echo (int)$termurah['product_id']; ?>" class="th-btn th-btn-primary th-btn-sm mt-2">
                        Lihat produk termurah
                    </a>
                <?php } else { ?>
                    <p class="empty-state-copy">Belum ada produk di kategori ini.</p>
                <?php } ?>
            </div>

        <?php } ?>

    <?php } else { ?>

        <!-- Tampilan awal sebelum pencarian: penjelasan singkat cara kerja Nexsearch. -->
        <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
            <div class="th-card th-card-pad flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><i data-lucide="wallet" class="w-4 h-4"></i></span>
                <div>
                    <p class="text-[11.5px] font-extrabold text-slate-900">Sesuai budget</p>
                    <p class="mt-0.5 text-[10px] text-slate-500">Produk terbaik di bawah budget Anda.</p>
                </div>
            </div>
            <div class="th-card th-card-pad flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><i data-lucide="scale" class="w-4 h-4"></i></span>
                <div>
                    <p class="text-[11.5px] font-extrabold text-slate-900">Perbandingan jelas</p>
                    <p class="mt-0.5 text-[10px] text-slate-500">Lihat pilihan lain dalam satu daftar.</p>
                </div>
            </div>
            <div class="th-card th-card-pad flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><i data-lucide="sparkles" class="w-4 h-4"></i></span>
                <div>
                    <p class="text-[11.5px] font-extrabold text-slate-900">Rekomendasi cepat</p>
                    <p class="mt-0.5 text-[10px] text-slate-500">Hasil langsung tanpa proses panjang.</p>
                </div>
            </div>
        </div>

    <?php } ?>
</div>
<?php page_end(); ?>
