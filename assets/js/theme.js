/**
 * theme.js — interaksi bersama untuk semua halaman publik Nexatech.
 *
 * Kenapa dipusatkan?
 * - Header publik dipakai di mainpage, katalog, Nexsearch, keranjang, profil,
 *   dan booking. Dropdown serta menu mobile harus berperilaku sama di semuanya.
 * - Toast juga dipakai beberapa halaman, jadi helper-nya ditaruh di sini agar
 *   tidak ada implementasi ganda.
 * - File ini tidak menyentuh data toko; semua data tetap berasal dari PHP.
 */
(function () {
    "use strict";

    // Jalankan callback segera jika DOM sudah siap, atau tunggu DOMContentLoaded.
    function onReady(callback) {
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", callback);
        } else {
            callback();
        }
    }

    onReady(function () {
        /*
         * Daftar dropdown header.
         * Menambah dropdown baru cukup menambahkan pasangan tombol-menu di sini;
         * logika buka/tutup dan akses keyboard tidak perlu ditulis ulang.
         */
        var dropdowns = [
            { button: "notif-btn", menu: "notif-menu" },
            { button: "profile-btn", menu: "profile-menu" }
        ];

        function closeDropdown(item) {
            var button = document.getElementById(item.button);
            var menu = document.getElementById(item.menu);
            if (!button || !menu) return;
            menu.hidden = true;
            button.setAttribute("aria-expanded", "false");
        }

        function closeAll(except) {
            dropdowns.forEach(function (item) {
                if (!except || item.button !== except.button) closeDropdown(item);
            });
        }

        dropdowns.forEach(function (item) {
            var button = document.getElementById(item.button);
            var menu = document.getElementById(item.menu);
            if (!button || !menu) return;

            button.addEventListener("click", function (event) {
                event.stopPropagation();
                var akanBuka = menu.hidden;
                closeAll(item);
                menu.hidden = !akanBuka;
                button.setAttribute("aria-expanded", akanBuka ? "true" : "false");
            });

            menu.addEventListener("click", function (event) {
                event.stopPropagation();
            });
        });

        document.addEventListener("click", function () {
            closeAll();
        });

        document.addEventListener("keydown", function (event) {
            if (event.key === "Escape") closeAll();
        });

        // Menu mobile hanya ada di breakpoint kecil; elemennya tetap ada di DOM
        // agar bisa dibuka/tutup tanpa memuat ulang halaman.
        var mobileButton = document.getElementById("mobile-menu-btn");
        var mobileMenu = document.getElementById("mobile-menu");

        if (mobileButton && mobileMenu) {
            mobileButton.addEventListener("click", function (event) {
                event.stopPropagation();
                var akanBuka = mobileMenu.hidden;
                mobileMenu.hidden = !akanBuka;
                mobileButton.setAttribute("aria-expanded", akanBuka ? "true" : "false");
            });

            mobileMenu.addEventListener("click", function (event) {
                if (event.target.closest("a")) {
                    mobileMenu.hidden = true;
                    mobileButton.setAttribute("aria-expanded", "false");
                }
                event.stopPropagation();
            });
        }

        /*
         * Tema publik: terang/gelap.
         * Preferensi disimpan di localStorage. preferences.js sudah memasang
         * kelas dark sebelum CSS dilukis, sedangkan bagian ini menyinkronkan
         * label tombol dan menangani klik user.
         */
        var themeRoot = document.documentElement;
        var themeToggles = document.querySelectorAll("[data-theme-toggle]");

        function tr(key, fallback) {
            if (window.techI18n && typeof window.techI18n.t === "function") {
                return window.techI18n.t(key);
            }
            return fallback;
        }

        function syncThemeToggles() {
            var dark = themeRoot.classList.contains("dark");
            var actionLabel = dark ? tr("enable_light", "Aktifkan mode terang") : tr("enable_dark", "Aktifkan mode gelap");
            var modeLabel = dark ? tr("light_mode", "Mode terang") : tr("dark_mode", "Mode gelap");

            themeToggles.forEach(function (button) {
                button.setAttribute("aria-pressed", dark ? "true" : "false");
                button.setAttribute("aria-label", actionLabel);
                button.title = modeLabel;
            });

            document.querySelectorAll("[data-theme-label]").forEach(function (label) {
                label.textContent = modeLabel;
            });
        }

        if (themeToggles.length) {
            themeToggles.forEach(function (button) {
                button.addEventListener("click", function () {
                    var dark = themeRoot.classList.toggle("dark");
                    try {
                        localStorage.setItem("techhub-theme", dark ? "dark" : "light");
                    } catch (error) {
                        // Tema tetap berubah untuk sesi ini jika localStorage diblokir.
                    }
                    syncThemeToggles();
                });
            });
            syncThemeToggles();
        }

        // Label tombol tema ikut berubah saat bahasa diganti.
        document.addEventListener("techhub:languagechange", syncThemeToggles);

        if (window.lucide) {
            lucide.createIcons();
        }

        /*
         * Toast global: dipanggil halaman lain lewat window.techToast().
         * Elemen dibuat dinamis supaya halaman tidak perlu menyiapkan markup toast.
         */
        window.techToast = function (message, type) {
            if (!message) return;

            // Pesan dari PHP tetap bisa diterjemahkan jika ada di kamus i18n.
            if (window.techI18n && typeof window.techI18n.translate === "function") {
                message = window.techI18n.translate(message);
            }

            var toast = document.createElement("div");
            toast.className = "toast" + (type === "error" ? " is-error" : "");
            toast.setAttribute("role", "status");

            var icon = document.createElement("span");
            icon.className = "toast-icon";
            icon.innerHTML = '<i data-lucide="' + (type === "error" ? "alert-circle" : "check") + '" class="w-4 h-4"></i>';

            var text = document.createElement("span");
            text.textContent = message;

            toast.appendChild(icon);
            toast.appendChild(text);
            document.body.appendChild(toast);

            if (window.lucide) lucide.createIcons();

            window.requestAnimationFrame(function () {
                toast.classList.add("is-visible");
            });

            window.setTimeout(function () {
                toast.classList.remove("is-visible");
                window.setTimeout(function () {
                    toast.remove();
                }, 300);
            }, 3200);
        };

        var toastMessage = document.querySelector("[data-toast-message]");
        if (toastMessage) {
            window.techToast(
                toastMessage.getAttribute("data-toast-message"),
                toastMessage.getAttribute("data-toast-type") || "success"
            );
            toastMessage.remove();
        }

        // Anchor internal di halaman yang sama digulir halus tanpa reload.
        var hashLinks = document.querySelectorAll('a[href*="#"]');
        hashLinks.forEach(function (link) {
            link.addEventListener("click", function () {
                var href = link.getAttribute("href") || "";
                var targetId = href.split("#")[1];
                if (!targetId) return;
                var target = document.getElementById(targetId);
                if (!target) return;

                var currentPath = window.location.pathname.replace(/\/+$/, "");
                var linkPath = link.pathname.replace(/\/+$/, "");
                if (currentPath === linkPath) {
                    window.setTimeout(function () {
                        target.scrollIntoView({ behavior: "smooth", block: "start" });
                    }, 40);
                }
            });
        });
    });
})();
