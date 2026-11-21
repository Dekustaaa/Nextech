<?php
/**
 * Halaman detail produk.
 *
 * Membaca ?id= lalu mengambil satu produk beserta nama kategori dan brand
 * memakai prepared statement. Halaman menampilkan foto, harga, badge, ringkasan
 * rating, form rating/ulasan (khusus pengguna login), dan daftar ulasan. ID yang
 * tidak valid atau produk yang tidak ditemukan akan diarahkan ke mainpage.php.
 * Form rating mengirim data ke actions/simpan_rating.php.
 */
session_start();
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../repositories/ratings.php";
require_once __DIR__ . "/../../components/landing/layout.php";

$id = (int)($_GET["id"] ?? 0);
// ID tidak valid tidak bisa diproses, jadi pengguna dikembalikan ke beranda.
if ($id <= 0) {
    header("Location: /PWL-SAS/mainpage.php");
    exit;
}

// Prepared statement dipakai karena $id berasal dari URL; placeholder ? mencegah SQL injection.
$stmt = mysqli_prepare($conn, "SELECT products.*, categories.category_name, categories.image,
                                      brands.brand_name, brands.brand_slug
                               FROM products
                               LEFT JOIN categories ON products.category_id = categories.category_id
                               LEFT JOIN brands ON products.brand_id = brands.brand_id
                               WHERE products.product_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Produk tidak ditemukan (mis. ID sudah dihapus) juga dialihkan ke beranda.
if (!$row) {
    header("Location: /PWL-SAS/mainpage.php");
    exit;
}

// Data rating: ringkasan agregat, seluruh ulasan, dan rating milik user yang sedang login (jika ada).
$ringkasan = ambil_ringkasan_rating($conn, $id);
$ulasan = ambil_ulasan_produk($conn, $id);
$rating_saya = null;
if (isset($_SESSION["user_id"])) {
    $rating_saya = ambil_rating_user($conn, $id, $_SESSION["user_id"]);
}

// Nilai default 0 dipakai bila produk belum punya rating sama sekali.
$rata = $ringkasan["rata_rata"] ? round((float)$ringkasan["rata_rata"], 1) : 0;
$jumlah_rating = (int)$ringkasan["jumlah"];

// Flash message hasil simpan rating dari actions/simpan_rating.php, ditampilkan sekali.
$pesan = $_SESSION["rating_pesan"] ?? "";
$status = $_SESSION["rating_status"] ?? "ok";
unset($_SESSION["rating_pesan"]);
unset($_SESSION["rating_status"]);

$activeNav = 'products';
atas("Detail Produk - " . $row["product_name"]);
?>

<div class="page-wrap page-wrap-narrow">

    <nav class="mb-3 flex flex-wrap items-center gap-1.5 text-[10.5px] text-slate-500" aria-label="Breadcrumb">
        <!-- Breadcrumb: beranda > daftar produk > kategori (opsional) > nama produk. -->
        <a href="/PWL-SAS/mainpage.php" class="font-semibold text-slate-500 hover:text-brand-600">Homepage</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <a href="/PWL-SAS/pages/categories/index.php" class="font-semibold text-slate-500 hover:text-brand-600">Produk</a>
        <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <?php if (!empty($row["category_name"])) { ?>
            <!-- Kategori hanya muncul di breadcrumb jika produk memang punya kategori. -->
            <a href="/PWL-SAS/pages/categories/index.php?kategori=<?php echo (int)$row["category_id"]; ?>" class="font-semibold text-slate-500 hover:text-brand-600">
                <?php echo htmlspecialchars($row["category_name"]); ?>
            </a>
            <i data-lucide="chevron-right" class="w-3 h-3"></i>
        <?php } ?>
        <span class="truncate font-bold text-slate-700"><?php echo htmlspecialchars($row["product_name"]); ?></span>
    </nav>

    <section class="page-hero reveal" aria-labelledby="product-title">
        <div class="page-hero-copy">
            <p class="page-eyebrow">Product Detail</p>
            <h1 id="product-title" class="page-title"><?php echo htmlspecialchars($row["product_name"]); ?></h1>
            <p class="page-subtitle">Lihat informasi produk, rating, dan ulasan pembeli sebelum membeli.</p>
            <div class="page-hero-actions">
                <a href="/PWL-SAS/mainpage.php#produk" class="th-btn th-btn-outline th-btn-sm">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    Kembali
                </a>
                <?php if (!empty($row["brand_name"])) { ?>
                    <!-- Badge brand hanya dirender bila join brands menghasilkan nama. -->
                    <span class="th-badge th-badge-blue">
                        <i data-lucide="award" class="w-3 h-3"></i>
                        <?php echo htmlspecialchars($row["brand_name"]); ?>
                    </span>
                <?php } ?>
                <a href="/PWL-SAS/views/cart.php" class="th-btn th-btn-ghost th-btn-sm">
                    <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                    Lihat keranjang
                </a>
            </div>
        </div>
        <span class="page-hero-icon" aria-hidden="true"><i data-lucide="package"></i></span>
    </section>

    <!-- ================= PRODUK ================= -->
    <section class="th-card p-4 sm:p-5">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
            <div class="relative lg:col-span-6">
                <div class="relative overflow-hidden rounded-2xl bg-slate-100">
                    <img src="<?php echo htmlspecialchars($row["url"]); ?>"
                         alt="<?php echo htmlspecialchars($row["product_name"]); ?>"
                         class="h-[330px] w-full object-cover sm:h-[410px]">

                    <div class="absolute left-4 top-4 flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/60 bg-white/90 px-3 py-1.5 text-[10px] font-bold text-slate-800 shadow-sm backdrop-blur-md">
                            <i data-lucide="headphones" class="w-3.5 h-3.5 text-brand-600"></i>
                            Layanan 24/7
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/60 bg-white/90 px-3 py-1.5 text-[10px] font-bold text-slate-800 shadow-sm backdrop-blur-md">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                            Garansi resmi
                        </span>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-6">
                <?php if (!empty($row["badge"])) { ?>
                    <!-- Badge promosi/label dari kolom badge produk, mis. "Terlaris". -->
                    <span class="th-badge th-badge-blue mb-3">
                        <i data-lucide="sparkles" class="w-3 h-3"></i>
                        <?php echo htmlspecialchars($row["badge"]); ?>
                    </span>
                <?php } ?>

                <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
                    <?php echo htmlspecialchars($row["product_name"]); ?>
                </h2>

                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <div class="flex items-center gap-0.5">
                        <?php // Render 5 bintang; yang nilainya <= rata-rata diberi warna kuning. ?>
                        <?php for ($i = 1; $i <= 5; $i++) { ?>
                            <i data-lucide="star" class="w-4 h-4 <?php echo $i <= round($rata) ? "text-amber-400 fill-amber-400" : "text-slate-300"; ?>"></i>
                        <?php } ?>
                    </div>
                    <span class="text-[11px] font-extrabold text-slate-800"><?php echo number_format($rata, 1, ",", "."); ?></span>
                    <span class="text-[11px] text-slate-500">(<?php echo $jumlah_rating; ?> ulasan)</span>
                    <a href="#ulasan" class="text-[11px] font-bold text-brand-600 hover:underline">Lihat ulasan</a>
                </div>

                <p class="mt-4 text-[12px] leading-relaxed text-slate-500">
                    <?php echo htmlspecialchars($row["description"]); ?>
                </p>

                <div class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <div>
                        <p class="text-[9.5px] font-semibold uppercase tracking-wide text-slate-400">Harga</p>
                        <p class="mt-0.5 text-2xl font-extrabold tracking-tight text-slate-900">
                            Rp <?php echo number_format($row["price"], 0, ",", "."); ?>
                        </p>
                    </div>
                    <span class="th-badge th-badge-green">
                        <i data-lucide="check-circle-2" class="w-3 h-3"></i>
                        Stok tersedia
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <!-- Aksi beli memakai link GET ke actions/cart.php, bukan form, agar bisa dipakai dari kartu produk. -->
                    <a href="/PWL-SAS/actions/cart.php?aksi=tambah&jenis=produk&id=<?php echo (int)$row["product_id"]; ?>"
                       class="th-btn th-btn-primary th-btn-full">
                        <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                        Tambah ke keranjang
                    </a>
                    <a href="/PWL-SAS/pages/technician/" class="th-btn th-btn-outline th-btn-full">
                        <i data-lucide="wrench" class="w-4 h-4"></i>
                        Pesan teknisi
                    </a>
                </div>

                <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <a href="/PWL-SAS/views/checkout.php" class="th-btn th-btn-dark th-btn-full">
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                        Checkout
                    </a>
                    <a href="/PWL-SAS/views/cart.php" class="th-btn th-btn-ghost th-btn-full">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        Lihat keranjang
                    </a>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2 border-t border-slate-100 pt-4">
                    <div class="text-center">
                        <i data-lucide="truck" class="mx-auto h-4 w-4 text-brand-600"></i>
                        <p class="mt-1 text-[9.5px] font-semibold text-slate-500">Pengiriman cepat</p>
                    </div>
                    <div class="text-center">
                        <i data-lucide="shield-check" class="mx-auto h-4 w-4 text-brand-600"></i>
                        <p class="mt-1 text-[9.5px] font-semibold text-slate-500">Garansi resmi</p>
                    </div>
                    <div class="text-center">
                        <i data-lucide="headphones" class="mx-auto h-4 w-4 text-brand-600"></i>
                        <p class="mt-1 text-[9.5px] font-semibold text-slate-500">Support 24/7</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= ULASAN ================= -->
    <section id="ulasan" class="mt-5 scroll-mt-24">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-12 lg:items-start">
            <div class="lg:col-span-4 th-card th-card-pad">
                <div class="th-card-head">
                    <div>
                        <h2 class="th-card-title">Rating produk</h2>
                        <p class="th-card-sub">Rating dari pembeli yang sudah login.</p>
                    </div>
                </div>

                <div class="rounded-2xl border border-brand-100 bg-brand-50/60 p-4">
                    <div class="flex items-end gap-3">
                        <span class="text-4xl font-extrabold tracking-tight text-slate-900"><?php echo number_format($rata, 1, ",", "."); ?></span>
                        <div class="pb-1">
                            <div class="flex items-center gap-0.5">
                                <?php for ($i = 1; $i <= 5; $i++) { ?>
                                    <i data-lucide="star" class="w-4 h-4 <?php echo $i <= round($rata) ? "text-amber-400 fill-amber-400" : "text-slate-300"; ?>"></i>
                                <?php } ?>
                            </div>
                            <p class="mt-1 text-[10.5px] text-slate-500"><?php echo $jumlah_rating; ?> ulasan</p>
                        </div>
                    </div>
                </div>

                <?php if ($pesan != "") { ?>
                    <!-- Warna alert mengikuti $status dari action: ok = sukses, selain itu error. -->
                    <div class="th-alert <?php echo $status == "ok" ? "th-alert-success" : "th-alert-danger"; ?> mt-3" role="status">
                        <i data-lucide="<?php echo $status == "ok" ? "check-circle-2" : "alert-circle"; ?>" class="w-4 h-4 shrink-0"></i>
                        <span><?php echo htmlspecialchars($pesan); ?></span>
                    </div>
                <?php } ?>

                <?php if (isset($_SESSION["user_id"])) { ?>
                    <!-- Rating hanya untuk user login; product_id dikirim hidden, rating & ulasan diisi user. -->
                    <form action="/PWL-SAS/actions/simpan_rating.php" method="POST" class="mt-4 space-y-3.5 rounded-2xl border border-slate-200 p-4">
                        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                        <div>
                            <p class="mb-2 text-[11px] font-semibold text-slate-700"><?php echo $rating_saya ? "Ubah rating kamu" : "Beri rating"; ?></p>
                            <div class="flex items-center gap-1">
                                <?php // Radio bintang tersembunyi (peer) membuat bintang bisa diklik; nilai lama otomatis checked. ?>
                                <?php for ($i = 1; $i <= 5; $i++) { ?>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="rating" value="<?php echo $i; ?>" class="peer sr-only" required
                                               <?php echo $rating_saya && (int)$rating_saya["rating"] == $i ? "checked" : ""; ?>>
                                        <span class="text-3xl leading-none text-slate-300 transition-colors peer-checked:text-amber-400">&#9733;</span>
                                    </label>
                                <?php } ?>
                            </div>
                        </div>
                        <div>
                            <label for="review" class="th-label">Ulasan</label>
                            <textarea id="review" name="review" maxlength="1000" placeholder="Ceritakan pengalaman kamu memakai produk ini"
                                      class="th-textarea"><?php echo htmlspecialchars($rating_saya["review"] ?? ""); ?></textarea>
                        </div>
                        <button type="submit" class="th-btn th-btn-primary th-btn-full">
                            <i data-lucide="send" class="w-4 h-4"></i>
                            <?php echo $rating_saya ? "Perbarui rating" : "Kirim rating"; ?>
                        </button>
                    </form>
                <?php } else { ?>
                    <!-- Tamu diarahkan login/daftar dulu sebelum bisa mengirim rating. -->
                    <div class="mt-4 rounded-2xl border border-slate-200 p-4 text-center">
                        <span class="mx-auto flex h-9 w-9 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                            <i data-lucide="log-in" class="w-4 h-4"></i>
                        </span>
                        <p class="mt-2 text-[12px] font-bold text-slate-800">Masuk untuk memberi rating</p>
                        <p class="mt-0.5 text-[10.5px] text-slate-500">Rating hanya bisa dikirim setelah kamu login.</p>
                        <div class="mt-3 flex justify-center gap-2">
                            <a href="/PWL-SAS/views/auth/login.php" class="th-btn th-btn-primary th-btn-sm">Masuk</a>
                            <a href="/PWL-SAS/views/auth/register.php" class="th-btn th-btn-outline th-btn-sm">Daftar</a>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <div class="lg:col-span-8 th-card th-card-pad">
                <div class="th-card-head">
                    <div>
                        <h2 class="th-card-title">Ulasan pembeli</h2>
                        <p class="th-card-sub">Pengalaman nyata dari pelanggan Nexatech.</p>
                    </div>
                    <span class="th-badge th-badge-slate"><?php echo count($ulasan); ?> ulasan</span>
                </div>

                <?php if (count($ulasan) == 0) { ?>
                    <!-- Empty state bila belum ada pembeli yang mengirim ulasan. -->
                    <div class="empty-state">
                        <span class="empty-state-icon"><i data-lucide="message-square" class="w-5 h-5"></i></span>
                        <h3 class="empty-state-title">Belum ada ulasan</h3>
                        <p class="empty-state-copy">Jadilah yang pertama memberi rating untuk produk ini.</p>
                    </div>
                <?php } ?>

                <div class="space-y-3">
                    <?php foreach ($ulasan as $u) { ?>
                        <!-- Satu ulasan: avatar inisial, nama, tanggal, bintang, dan isi review (opsional). -->
                        <article class="flex gap-3 rounded-2xl border border-slate-100 p-3.5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-900 text-[11px] font-bold text-white">
                                <?php echo htmlspecialchars(strtoupper(substr($u["full_name"] ?: "Pengguna", 0, 1))); ?>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="text-[12px] font-bold text-slate-900"><?php echo htmlspecialchars($u["full_name"] ?: "Pengguna"); ?></p>
                                    <span class="text-[9.5px] text-slate-400"><?php echo date("d M Y", strtotime($u["created_at"])); ?></span>
                                </div>
                                <div class="my-1 flex items-center gap-0.5">
                                    <?php for ($i = 1; $i <= 5; $i++) { ?>
                                        <i data-lucide="star" class="w-3.5 h-3.5 <?php echo $i <= (int)$u["rating"] ? "text-amber-400 fill-amber-400" : "text-slate-300"; ?>"></i>
                                    <?php } ?>
                                </div>
                                <?php $isi_ulasan = trim($u["review"] ?? ""); ?>
                                <?php if ($isi_ulasan != "") { ?>
                                    <p class="text-[10.5px] leading-relaxed text-slate-600"><?php echo nl2br(htmlspecialchars($isi_ulasan)); ?></p>
                                <?php } ?>
                            </div>
                        </article>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</div>
<?php
bawah();
mysqli_close($conn);
?>
