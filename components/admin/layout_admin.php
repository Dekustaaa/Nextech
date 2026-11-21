<?php
/**
 * layout_admin.php — kerangka halaman admin Nexatech.
 *
 * Kenapa memakai fungsi admin_start()/admin_end()?
 * - Sidebar, topbar, breadcrumb, dan header halaman admin selalu sama.
 * - Dengan satu layout, perubahan navigasi cukup dilakukan di file ini.
 * - CSS admin dipusatkan di /assets/css/admin.css dan interaksinya di
 *   /assets/js/admin.js, sehingga file PHP fokus pada data dan markup.
 */

/**
 * Membuka layout admin dan mencetak sidebar + topbar.
 *
 * @param string $judul   Judul halaman yang tampil di breadcrumb dan header.
 * @param string $desc    Deskripsi singkat halaman.
 * @param string $halaman Nama file aktif, dipakai untuk menandai menu.
 */
function admin_start($judul, $desc, $halaman) {
    // Data akun untuk kartu admin di sidebar dan topbar.
    $nama = $_SESSION["nama"] ?? "Admin Nexatech";
    $email = $_SESSION["email"] ?? "admin@nexatech.id";
    $inisial = strtoupper(substr($nama, 0, 2));

    // Menu utama admin: [label, ikon Lucide, file tujuan].
    $menu = [
        ["Dashboard", "layout-dashboard", "dashboard.php"],
        ["Produk", "box", "products.php"],
        ["Kategori", "tag", "categories.php"],
        ["Pesanan", "clipboard-list", "orders.php"],
        ["Booking Teknisi", "calendar-days", "booking.php"],
        ["Pelanggan", "users", "customers.php"],
        ["Profil", "user-round", "profile.php"]
    ];

    $pageIcons = [
        "dashboard.php" => "layout-dashboard",
        "products.php" => "package",
        "categories.php" => "tags",
        "orders.php" => "clipboard-list",
        "booking.php" => "calendar-check-2",
        "customers.php" => "users",
        "profile.php" => "user-round"
    ];
    $pageIcon = $pageIcons[$halaman] ?? "sparkles";

    $pageEyebrows = [
        "dashboard.php" => "Ringkasan toko",
        "products.php" => "Katalog produk",
        "categories.php" => "Struktur katalog",
        "orders.php" => "Operasional penjualan",
        "booking.php" => "Layanan teknisi",
        "customers.php" => "Basis pelanggan",
        "profile.php" => "Akun admin"
    ];
    $pageEyebrow = $pageEyebrows[$halaman] ?? "Panel admin";

    $bulan = [1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr", 5 => "Mei", 6 => "Jun", 7 => "Jul", 8 => "Agu", 9 => "Sep", 10 => "Okt", 11 => "Nov", 12 => "Des"];
    $tanggal = date("d") . " " . $bulan[(int)date("n")] . " " . date("Y");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo htmlspecialchars($judul); ?> - Nexatech Admin</title>
    <link rel="icon" type="image/svg+xml" href="/PWL-SAS/assets/img/logo.svg">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            // Samakan perilaku dark mode admin dengan storefront.
            darkMode: "class",
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif']
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a'
                        }
                    }
                }
            }
        };
    </script>
    <!--
        i18n.js dimuat sebelum admin.js agar kamus bahasa sudah tersedia.
        admin.js tetap dimuat di head agar kelas dark bisa dipasang sebelum
        CSS dilukis; CSS admin memakai cache-buster filemtime().
    -->
    <script src="/PWL-SAS/assets/js/i18n.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/i18n.js'); ?>"></script>
    <script src="/PWL-SAS/assets/js/admin.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/admin.js'); ?>"></script>
    <link rel="stylesheet" href="/PWL-SAS/assets/css/admin.css?v=<?php echo filemtime(__DIR__ . '/../../assets/css/admin.css'); ?>">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="admin-shell min-h-screen">
    <!-- Sidebar tetap: brand, menu utama, tautan toko, logout, dan kartu admin. -->
    <aside id="admin-sidebar" class="admin-sidebar">
        <a href="dashboard.php" class="admin-brand" aria-label="Nexatech Admin - Dashboard">
            <span class="admin-brand-mark">
                <img src="/PWL-SAS/assets/img/logo.svg" alt="" width="36" height="33">
            </span>
            <span>
                <span class="admin-brand-text">Nexatech<span>.</span></span>
                <span class="admin-brand-caption">Admin Panel</span>
            </span>
        </a>

        <div class="admin-nav-label">Menu utama</div>
        <nav class="admin-nav" aria-label="Navigasi admin">
            <?php foreach ($menu as $m) { ?>
                <?php $aktif = ($m[2] == $halaman); ?>
                <a href="<?php echo $m[2]; ?>" class="admin-nav-link<?php echo $aktif ? ' is-active' : ''; ?>"<?php echo $aktif ? ' aria-current="page"' : ''; ?>>
                    <i data-lucide="<?php echo $m[1]; ?>" class="admin-nav-icon"></i>
                    <span><?php echo htmlspecialchars($m[0]); ?></span>
                    <?php if ($aktif) { ?><span class="admin-nav-dot" aria-hidden="true"></span><?php } ?>
                </a>
            <?php } ?>
        </nav>

        <div class="admin-sidebar-footer">
            <a href="/PWL-SAS/mainpage.php" class="admin-store-link">
                <i data-lucide="store" class="w-4 h-4"></i>
                <span>Lihat toko</span>
                <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 ml-auto"></i>
            </a>
            <a href="/PWL-SAS/actions/logout.php" class="admin-logout-link">
                <i data-lucide="log-out" class="w-4 h-4"></i>
                <span>Logout</span>
            </a>
            <a href="profile.php" class="admin-user-card">
                <span class="admin-user-avatar"><?php echo htmlspecialchars($inisial); ?></span>
                <span class="admin-user-copy">
                    <strong><?php echo htmlspecialchars($nama); ?></strong>
                    <small><?php echo htmlspecialchars($email); ?></small>
                </span>
                <i data-lucide="chevron-right" class="w-4 h-4 admin-user-chevron"></i>
            </a>
        </div>
    </aside>

    <!-- Overlay gelap di belakang sidebar saat menu mobile terbuka. -->
    <div id="admin-overlay" class="admin-mobile-overlay" aria-hidden="true"></div>

    <main class="admin-main">
        <!-- Topbar: tombol menu mobile, breadcrumb, tanggal, toggle tema, dan akun. -->
        <div class="admin-topbar">
            <div class="admin-topbar-left">
                <button type="button" id="admin-menu-btn" class="admin-menu-btn" aria-label="Buka menu admin" aria-expanded="false" aria-controls="admin-sidebar">
                    <i data-lucide="menu" class="w-[19px] h-[19px]"></i>
                </button>
                <nav class="admin-breadcrumb" aria-label="Breadcrumb">
                    <i data-lucide="house" class="w-3.5 h-3.5"></i>
                    <span>Admin</span>
                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                    <strong><?php echo htmlspecialchars($judul); ?></strong>
                </nav>
            </div>

            <div class="admin-topbar-right">
                <!-- Tombol bahasa memakai kamus i18n yang sama dengan storefront. -->
                <button type="button" class="admin-lang-toggle" data-lang-toggle aria-label="Ganti bahasa" title="Ganti bahasa">
                    <i data-lucide="languages" class="w-[17px] h-[17px]"></i>
                    <span class="admin-lang-label" data-lang-label data-i18n-skip>ID</span>
                </button>
                <button type="button" id="admin-theme-toggle" class="admin-theme-toggle" data-admin-theme-toggle aria-label="Aktifkan mode gelap" aria-pressed="false" title="Mode gelap">
                    <i data-lucide="moon" class="admin-theme-icon admin-theme-icon-moon"></i>
                    <i data-lucide="sun" class="admin-theme-icon admin-theme-icon-sun"></i>
                </button>
                <span class="admin-topbar-date">
                    <i data-lucide="calendar-days" class="w-3.5 h-3.5"></i>
                    <?php echo htmlspecialchars($tanggal); ?>
                </span>
                <a href="profile.php" class="admin-topbar-user" aria-label="Buka profil admin">
                    <span class="admin-topbar-avatar"><?php echo htmlspecialchars($inisial); ?></span>
                    <span class="admin-topbar-user-copy">
                        <strong><?php echo htmlspecialchars($nama); ?></strong>
                        <small>Administrator</small>
                    </span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>

        <!-- Header halaman: eyebrow, judul, deskripsi, dan ikon konteks. -->
        <section class="admin-page-head">
            <div class="admin-page-head-copy">
                <p class="admin-page-eyebrow"><?php echo htmlspecialchars($pageEyebrow); ?></p>
                <h1 class="admin-page-title"><?php echo htmlspecialchars($judul); ?></h1>
                <p class="admin-page-desc"><?php echo htmlspecialchars($desc); ?></p>
            </div>
            <span class="admin-page-icon" aria-hidden="true">
                <i data-lucide="<?php echo htmlspecialchars($pageIcon); ?>"></i>
            </span>
        </section>

        <div class="admin-content">
<?php
}

function admin_end() {
?>
        </div>
    </main>

    <?php // Menu mobile, toggle tema, dan ikon ditangani assets/js/admin.js. ?>
</body>
</html>
<?php
}
?>
