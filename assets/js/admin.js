/**
 * admin.js — seluruh interaksi ringan panel admin Nexatech.
 *
 * Kenapa dipisah dari layout_admin.php?
 * - Script yang menempel di file PHP sulit dipakai ulang dan membuat markup sulit dibaca.
 * - File ini juga dipakai halaman login admin, jadi inisialisasi ikon Lucide dan
 *   preferensi tema gelap tidak ditulis dua kali.
 * - Tidak ada proses bisnis di sini; semua aksi data tetap ditangani PHP.
 */
(function () {
    "use strict";

    /*
     * Terapkan tema gelap sebelum CSS selesai dimuat.
     * Script ini sengaja dimuat di <head> agar tidak terjadi "flash" putih
     * ketika admin sebelumnya memilih mode gelap.
     */
    try {
        if (localStorage.getItem("techhub-admin-theme") === "dark") {
            document.documentElement.classList.add("dark");
        }
    } catch (error) {
        // localStorage bisa diblokir browser; abaikan dan gunakan tema terang.
    }

    /* Jalankan inisialisasi setelah seluruh DOM siap. */
    function onReady(callback) {
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", callback);
        } else {
            callback();
        }
    }

    onReady(function () {
        const sidebar = document.getElementById("admin-sidebar");
        const overlay = document.getElementById("admin-overlay");
        const menuButton = document.getElementById("admin-menu-btn");

        /*
         * Menu mobile admin.
         * Kelas is-open/is-visible dipakai oleh CSS, sedangkan aria-expanded
         * memberi tahu pembaca layar apakah menu sedang terbuka.
         */
        function closeMenu() {
            if (!sidebar || !overlay || !menuButton) return;
            sidebar.classList.remove("is-open");
            overlay.classList.remove("is-visible");
            menuButton.setAttribute("aria-expanded", "false");
            document.body.classList.remove("admin-menu-open");
        }

        function toggleMenu() {
            if (!sidebar || !overlay || !menuButton) return;
            const open = sidebar.classList.toggle("is-open");
            overlay.classList.toggle("is-visible", open);
            menuButton.setAttribute("aria-expanded", open ? "true" : "false");
            document.body.classList.toggle("admin-menu-open", open);
        }

        if (menuButton) menuButton.addEventListener("click", toggleMenu);
        if (overlay) overlay.addEventListener("click", closeMenu);

        // Menutup sidebar setelah admin memilih menu di layar kecil.
        if (sidebar) {
            sidebar.querySelectorAll("a").forEach(function (link) {
                link.addEventListener("click", closeMenu);
            });
        }

        // Escape adalah jalur cepat untuk menutup menu tanpa mouse.
        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") closeMenu();
        });

        /*
         * Toggle tema gelap/terang.
         * Preferensi disimpan di localStorage agar bertahan setelah refresh.
         * Query memakai data attribute agar halaman login dan layout admin
         * sama-sama bisa memakai tombol dengan markup yang sama.
         */
        const themeToggles = document.querySelectorAll("[data-admin-theme-toggle]");
        const themeRoot = document.documentElement;

        function tr(key, fallback) {
            if (window.techI18n && typeof window.techI18n.t === "function") {
                return window.techI18n.t(key);
            }
            return fallback;
        }

        function syncThemeToggle() {
            const dark = themeRoot.classList.contains("dark");
            const actionLabel = dark ? tr("enable_light", "Aktifkan mode terang") : tr("enable_dark", "Aktifkan mode gelap");
            const modeLabel = dark ? tr("light_mode", "Mode terang") : tr("dark_mode", "Mode gelap");

            themeToggles.forEach(function (button) {
                button.setAttribute("aria-pressed", dark ? "true" : "false");
                button.setAttribute("aria-label", actionLabel);
                button.title = modeLabel;
            });
        }

        if (themeToggles.length) {
            themeToggles.forEach(function (button) {
                button.addEventListener("click", function () {
                    const dark = themeRoot.classList.toggle("dark");
                    try {
                        localStorage.setItem("techhub-admin-theme", dark ? "dark" : "light");
                    } catch (error) {
                        // Jika localStorage tidak tersedia, tema tetap berubah untuk sesi ini.
                    }
                    syncThemeToggle();
                });
            });
            syncThemeToggle();
        }

        // Label tombol tema ikut berubah saat bahasa diganti.
        document.addEventListener("techhub:languagechange", syncThemeToggle);

        // Lucide mengganti <i data-lucide="..."> menjadi SVG setelah DOM siap.
        if (window.lucide && typeof window.lucide.createIcons === "function") {
            window.lucide.createIcons();
        }
    });
})();
