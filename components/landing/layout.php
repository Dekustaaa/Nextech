<?php
/**
 * layout.php — kerangka (layout) bersama untuk semua halaman publik Nexatech.
 *
 * Kenapa file ini dipakai?
 * - Header dan footer publik sebelumnya disalin di banyak halaman. Salinan itu
 *   membuat perubahan menu harus dilakukan berkali-kali dan gampang berbeda.
 * - Sekarang halaman publik cukup memanggil atas() di awal dan bawah() di akhir.
 * - CSS/JS bersama dimuat dari folder /assets agar lokasi aset konsisten.
 *
 * Cara pakai singkat:
 *   $GLOBALS['activeNav'] = 'booking';          // menandai menu yang aktif
 *   atas('Judul Halaman', $extraHead);          // buka <html> ... <main>
 *   ... isi halaman ...
 *   bawah($extraScripts);                       // tutup </main>, footer, </html>
 */

if (session_status() === PHP_SESSION_NONE) {
    // Session dipakai header untuk membaca status login dan jumlah keranjang.
    session_start();
}

/**
 * Membuka dokumen HTML, memuat aset global, lalu mencetak header publik.
 *
 * @param string $pageTitle Judul tab browser.
 * @param string $extraHead HTML tambahan untuk <head> (meta, CSS khusus halaman).
 */
function atas($pageTitle = "Nexatech", $extraHead = "") {
    // Nilai ini diisi halaman sebelum memanggil atas(), misalnya 'home' atau 'booking'.
    $activeNav = $GLOBALS['activeNav'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="icon" type="image/svg+xml" href="/PWL-SAS/assets/img/logo.svg">

    <!--
        Preferensi publik dimuat sebelum CSS agar tema gelap dan bahasa pilihan
        user langsung aktif tanpa kedipan tampilan terang/Indonesia.
    -->
    <script src="/PWL-SAS/assets/js/preferences.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/preferences.js'); ?>"></script>

    <!-- Font merek Nexatech: Plus Jakarta Sans untuk UI, Caveat untuk aksen tulisan tangan. -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CDN dipakai untuk utility class cepat; komponen utama tetap di theme.css. -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Konfigurasi Tailwind disamakan dengan design token Nexatech di theme.css.
        tailwind.config = {
            // darkMode class membuat utility dark:* mengikuti <html class="dark">.
            darkMode: "class",
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        script: ['Caveat', 'cursive']
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
        Theme publik dipusatkan di /assets/css/theme.css.
        filemtime() dipakai sebagai cache-buster: browser otomatis mengambil versi
        terbaru setiap kali file CSS diubah, tanpa harus menaikkan nomor versi manual.
    -->
    <link rel="stylesheet" href="/PWL-SAS/assets/css/theme.css?v=<?php echo filemtime(__DIR__ . '/../../assets/css/theme.css'); ?>">
    <?php echo $extraHead; ?>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="min-h-screen antialiased flex flex-col">
<?php // Header bersama: menu, pencarian, keranjang, notifikasi, dan profil. ?>
<?php include __DIR__ . "/header.php"; ?>
<main class="flex-1 w-full">
<?php
}

/**
 * Menutup <main>, mencetak footer bersama, lalu memuat script global.
 *
 * @param string $extraScripts Script khusus halaman yang harus dimuat setelah
 *                             theme.js, misalnya data JSON + technician.js.
 */
function bawah($extraScripts = "") {
?>
</main>
<?php // Footer bersama agar tautan dan copyright konsisten di seluruh halaman. ?>
<?php include __DIR__ . "/footer.php"; ?>

<!--
    i18n.js memuat kamus bahasa sebelum theme.js. Urutan ini penting agar
    label tema dan toast yang dibuat theme.js sudah bisa memakai terjemahan.
-->
<script src="/PWL-SAS/assets/js/i18n.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/i18n.js'); ?>"></script>
<!-- Script global: dropdown header, menu mobile, toast, dan inisialisasi ikon Lucide. -->
<script src="/PWL-SAS/assets/js/theme.js?v=<?php echo filemtime(__DIR__ . '/../../assets/js/theme.js'); ?>"></script>
<?php echo $extraScripts; ?>
<script>
    if (window.lucide) {
        lucide.createIcons();
    }
</script>
</body>
</html>
<?php
}
?>
