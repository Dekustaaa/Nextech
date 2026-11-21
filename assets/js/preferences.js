/**
 * preferences.js — menerapkan preferensi tampilan publik sebelum halaman dilukis.
 *
 * Kenapa dimuat di <head>?
 * - Kalau mode gelap baru dipasang setelah DOM siap, pengguna akan melihat
 *   kedipan tema terang terlebih dahulu.
 * - Bahasa juga ditulis di sini agar atribut <html lang> langsung benar.
 *
 * File ini hanya membaca localStorage; perubahan tema/bahasa saat halaman
 * berjalan ditangani assets/js/theme.js dan assets/js/i18n.js.
 */
(function () {
    "use strict";

    var root = document.documentElement;

    // Bahasa default Nexatech adalah Indonesia; simpan pilihan terakhir user.
    var language = "id";
    try {
        var savedLanguage = localStorage.getItem("techhub-lang");
        if (savedLanguage === "en" || savedLanguage === "id") {
            language = savedLanguage;
        }
    } catch (error) {
        // localStorage bisa diblokir; halaman tetap memakai bahasa default.
    }
    root.lang = language;
    root.setAttribute("data-lang", language);

    // Preferensi tema publik terpisah dari preferensi tema admin.
    try {
        if (localStorage.getItem("techhub-theme") === "dark") {
            root.classList.add("dark");
        }
    } catch (error) {
        // Jika localStorage tidak tersedia, gunakan tema terang.
    }
})();
