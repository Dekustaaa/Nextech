<?php
/**
 * header.php — header publik tunggal untuk mainpage, katalog, Nexsearch,
 * keranjang, profil, dan halaman booking teknisi.
 *
 * Kenapa dipisah?
 * - Sebelumnya setiap halaman menyalin markup header sendiri sehingga menu atau
 *   tombol keranjang bisa berbeda. Komponen ini memastikan semua halaman publik
 *   memakai header yang sama persis.
 * - Halaman hanya perlu mengisi $GLOBALS['activeNav'] agar menu aktif tersorot.
 */

if (session_status() === PHP_SESSION_NONE) {
    // Session diperlukan untuk membaca user yang login dan isi keranjang.
    session_start();
}

// Nilai aktif dikirim oleh halaman (mis. 'home', 'products', 'booking').
$activeNav = $GLOBALS['activeNav'] ?? '';

// Data akun dipakai pada dropdown profil di sisi kanan header.
$login       = isset($_SESSION['user_id']);
$nama_login  = trim($_SESSION['nama'] ?? '');
$email_login = trim($_SESSION['email'] ?? '');
if ($nama_login === '') {
    $nama_login = explode('@', $email_login)[0] ?? '';
}
$inisial_login = $nama_login !== '' ? strtoupper(substr($nama_login, 0, 2)) : 'TH';

// Hitung total item keranjang: produk memakai qty, layanan teknisi dihitung per item.
$cart_count = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $qty) {
        $cart_count += max(1, (int)$qty);
    }
}
if (!empty($_SESSION['layanan']) && is_array($_SESSION['layanan'])) {
    $cart_count += count($_SESSION['layanan']);
}

// Helper kecil agar penulisan class aktif tidak berulang di desktop dan mobile.
$navActive = function ($key) use ($activeNav) {
    return $activeNav === $key ? ' nav-link-active' : '';
};
$mobileActive = function ($key) use ($activeNav) {
    return $activeNav === $key ? ' mobile-link-active' : '';
};
?>
<header class="site-header">
    <div class="header-inner">
        <!-- Logo selalu mengarah ke beranda agar konsisten di semua halaman. -->
        <a href="/PWL-SAS/mainpage.php" class="brand" aria-label="Nexatech - Beranda">
            <span class="brand-mark">
                <i data-lucide="wrench" class="w-[19px] h-[19px]"></i>
            </span>
            <span class="brand-text">Nexatech<span>.</span></span>
        </a>

        <!-- Navigasi desktop. Class nav-link-active berasal dari $activeNav halaman. -->
        <nav class="main-nav" aria-label="Navigasi utama">
            <a href="/PWL-SAS/mainpage.php" class="nav-link<?php echo $navActive('home'); ?>">
                <i data-lucide="house" class="nav-icon"></i>
                <span>Homepage</span>
            </a>
            <a href="/PWL-SAS/pages/categories/index.php" class="nav-link<?php echo $navActive('products'); ?>">
                <i data-lucide="layout-grid" class="nav-icon"></i>
                <span>All Product</span>
            </a>
            <a href="/PWL-SAS/views/recommendations.php" class="nav-link<?php echo $navActive('nexsearch'); ?>">
                <i data-lucide="search" class="nav-icon"></i>
                <span>Nexsearch</span>
            </a>
            <a href="/PWL-SAS/pages/technician/" class="nav-link<?php echo $navActive('booking'); ?>">
                <i data-lucide="calendar-days" class="nav-icon"></i>
                <span>Booking</span>
            </a>
        </nav>

        <!-- Aksi kanan: menu mobile, bahasa, tema, keranjang, notifikasi, dan profil. -->
        <div class="header-actions">
            <button type="button" id="mobile-menu-btn" class="icon-btn md:hidden" aria-label="Buka menu" aria-expanded="false">
                <i data-lucide="menu" class="w-[19px] h-[19px]"></i>
            </button>

            <!--
                Tombol bahasa dan tema disembunyikan di layar kecil agar header
                tidak sesak. Keduanya tetap tersedia di dalam menu mobile.
            -->
            <button type="button" class="lang-btn header-tool" data-lang-toggle aria-label="Ganti bahasa" title="Ganti bahasa">
                <i data-lucide="languages" class="w-[17px] h-[17px]"></i>
                <span class="lang-btn-label" data-lang-label data-i18n-skip>ID</span>
            </button>
            <button type="button" class="icon-btn header-tool theme-toggle" data-theme-toggle aria-label="Aktifkan mode gelap" aria-pressed="false" title="Mode gelap">
                <i data-lucide="moon" class="theme-icon-moon w-[19px] h-[19px]"></i>
                <i data-lucide="sun" class="theme-icon-sun w-[19px] h-[19px]"></i>
            </button>

            <!-- Keranjang menampilkan badge jumlah; angka dibatasi 99+ agar tidak melebar. -->
            <a href="/PWL-SAS/views/cart.php"
               class="icon-btn<?php echo $activeNav === 'cart' ? ' is-active' : ''; ?>"
               title="Keranjang"
               aria-label="Keranjang">
                <i data-lucide="shopping-cart" class="w-[19px] h-[19px]"></i>
                <?php if ($cart_count > 0) { ?>
                    <span class="cart-count"><?php echo $cart_count > 99 ? '99+' : (int)$cart_count; ?></span>
                <?php } ?>
            </a>

            <!-- Dropdown notifikasi. Interaksi buka/tutup ditangani assets/js/theme.js. -->
            <div class="relative">
                <button type="button" id="notif-btn" class="icon-btn" aria-label="Notifikasi" aria-expanded="false">
                    <i data-lucide="bell" class="w-[19px] h-[19px]"></i>
                    <span class="notif-dot" aria-hidden="true"></span>
                </button>

                <div id="notif-menu" class="dropdown-panel notif-panel" hidden>
                    <div class="dropdown-title">Notifikasi</div>
                    <div class="notif-item">
                        <span class="notif-icon"><i data-lucide="check-circle-2" class="w-4 h-4"></i></span>
                        <div>
                            <p class="notif-heading">Pesanan selesai</p>
                            <p class="notif-copy">Teknisi menyelesaikan perbaikan laptop Anda.</p>
                            <p class="notif-time">10 menit lalu</p>
                        </div>
                    </div>
                    <div class="notif-item">
                        <span class="notif-icon"><i data-lucide="calendar-clock" class="w-4 h-4"></i></span>
                        <div>
                            <p class="notif-heading">Jadwal kunjungan</p>
                            <p class="notif-copy">Jangan lupa konfirmasi jadwal besok pagi.</p>
                            <p class="notif-time">1 jam lalu</p>
                        </div>
                    </div>
                    <div class="notif-item">
                        <span class="notif-icon"><i data-lucide="ticket-percent" class="w-4 h-4"></i></span>
                        <div>
                            <p class="notif-heading">Promo teknisi</p>
                            <p class="notif-copy">Diskon 15% untuk layanan instalasi.</p>
                            <p class="notif-time">Kemarin</p>
                        </div>
                    </div>
                </div>
            </div>

            <span class="header-divider" aria-hidden="true"></span>

            <!-- Dropdown profil: tampil berbeda untuk user login dan guest. -->
            <div class="relative">
                <button type="button" id="profile-btn" class="profile-btn" aria-label="Menu akun" aria-expanded="false">
                    <span class="profile-fallback" style="display:grid"><?php echo htmlspecialchars($inisial_login); ?></span>
                    <i data-lucide="chevron-down" class="profile-chevron"></i>
                </button>

                <div id="profile-menu" class="dropdown-panel profile-panel" hidden>
                    <div class="px-4 pt-4 pb-3 border-b border-slate-100">
                        <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars($nama_login !== '' ? $nama_login : 'Guest'); ?></p>
                        <p class="text-[11px] text-slate-500 mt-0.5"><?php echo htmlspecialchars($email_login !== '' ? $email_login : 'Belum masuk ke akun'); ?></p>
                    </div>
                    <div class="p-2">
                        <?php if ($login) { ?>
                            <a href="/PWL-SAS/views/profile.php" class="dropdown-link"><i data-lucide="user-round" class="w-4 h-4"></i> Profil saya</a>
                            <a href="/PWL-SAS/views/cart.php" class="dropdown-link"><i data-lucide="shopping-cart" class="w-4 h-4"></i> Keranjang</a>
                            <a href="/PWL-SAS/pages/technician/" class="dropdown-link"><i data-lucide="calendar-days" class="w-4 h-4"></i> Booking teknisi</a>
                            <a href="/PWL-SAS/actions/logout.php" class="dropdown-link dropdown-link-danger"><i data-lucide="log-out" class="w-4 h-4"></i> Keluar</a>
                        <?php } else { ?>
                            <a href="/PWL-SAS/views/auth/login.php" class="dropdown-link"><i data-lucide="log-in" class="w-4 h-4"></i> Masuk</a>
                            <a href="/PWL-SAS/views/auth/register.php" class="dropdown-link"><i data-lucide="user-plus" class="w-4 h-4"></i> Daftar</a>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu mobile: markup terpisah agar bisa ditampilkan tanpa mengubah nav desktop. -->
    <div id="mobile-menu" class="mobile-menu" hidden>
        <a href="/PWL-SAS/mainpage.php" class="mobile-link<?php echo $mobileActive('home'); ?>"><i data-lucide="house" class="w-4 h-4"></i> Homepage</a>
        <a href="/PWL-SAS/pages/categories/index.php" class="mobile-link<?php echo $mobileActive('products'); ?>"><i data-lucide="layout-grid" class="w-4 h-4"></i> All Product</a>
        <a href="/PWL-SAS/views/recommendations.php" class="mobile-link<?php echo $mobileActive('nexsearch'); ?>"><i data-lucide="search" class="w-4 h-4"></i> Nexsearch</a>
        <a href="/PWL-SAS/pages/technician/" class="mobile-link<?php echo $mobileActive('booking'); ?>"><i data-lucide="calendar-days" class="w-4 h-4"></i> Booking</a>
        <div class="my-2 h-px bg-slate-100"></div>
        <a href="/PWL-SAS/views/cart.php" class="mobile-link<?php echo $mobileActive('cart'); ?>"><i data-lucide="shopping-cart" class="w-4 h-4"></i> Keranjang<?php echo $cart_count > 0 ? ' (' . (int)$cart_count . ')' : ''; ?></a>
        <?php if ($login) { ?>
            <a href="/PWL-SAS/views/profile.php" class="mobile-link<?php echo $mobileActive('profile'); ?>"><i data-lucide="user-round" class="w-4 h-4"></i> Profil saya</a>
            <a href="/PWL-SAS/actions/logout.php" class="mobile-link text-red-500"><i data-lucide="log-out" class="w-4 h-4"></i> Keluar</a>
        <?php } else { ?>
            <a href="/PWL-SAS/views/auth/login.php" class="mobile-link"><i data-lucide="log-in" class="w-4 h-4"></i> Masuk</a>
            <a href="/PWL-SAS/views/auth/register.php" class="mobile-link"><i data-lucide="user-plus" class="w-4 h-4"></i> Daftar</a>
        <?php } ?>

        <!-- Preferensi tampilan juga tersedia di menu mobile. -->
        <div class="mobile-tools">
            <button type="button" class="mobile-tool" data-lang-toggle aria-label="Ganti bahasa" title="Ganti bahasa">
                <i data-lucide="languages" class="w-4 h-4"></i>
                <span>Bahasa</span>
                <span class="mobile-tool-value" data-lang-label data-i18n-skip>ID</span>
            </button>
            <button type="button" class="mobile-tool" data-theme-toggle aria-label="Aktifkan mode gelap" aria-pressed="false">
                <i data-lucide="moon" class="w-4 h-4 theme-icon-moon"></i>
                <i data-lucide="sun" class="w-4 h-4 theme-icon-sun"></i>
                <span data-theme-label>Mode gelap</span>
            </button>
        </div>
    </div>
</header>
