/* ==================================================================
   Nexatech - Booking Teknisi
   Halaman: pages/technician/index.php
   --------------------------------------------------------------
   Kenapa file ini dipisah?
   - Halaman booking punya interaksi kompleks: memilih teknisi, mengganti
     layanan, menghitung ringkasan, membuka modal, dan menampilkan toast.
   - Interaksi header (dropdown notifikasi/profil/menu mobile) tidak ada di
     sini karena header sudah memakai komponen bersama + theme.js.
   - Data awal dikirim PHP lewat window.TECHHUB_BOOKING, bukan hardcode di JS,
     sehingga daftar teknisi/layanan selalu sesuai database.
 * ================================================================== */

(function () {
    "use strict";

    // Data awal dari PHP: teknisi, layanan, pilihan awal, dan status sukses.
    const raw = window.TECHHUB_BOOKING || {};
    const technicians = Array.isArray(raw.technicians) ? raw.technicians.map(normalizeTech) : [];
    const services = Array.isArray(raw.services) ? raw.services.map(normalizeService) : [];

    // Helper singkat agar teks yang dibuat JavaScript ikut kamus i18n.
    function tr(key, variables, fallback) {
        if (window.techI18n && typeof window.techI18n.t === "function") {
            return window.techI18n.t(key, variables || {});
        }
        return fallback || key;
    }

    const state = {
        selectedTechId: Number(raw.selectedTechnician) || 0,
        selectedServiceId: Number(raw.selectedService) || 0,
        lastFocus: null,
        toastTimer: null,
    };

    const $ = (selector, scope) => (scope || document).querySelector(selector);
    const $$ = (selector, scope) => Array.from((scope || document).querySelectorAll(selector));

    const el = {
        form: $("#booking-form"),
        fullname: $("#fullname"),
        phone: $("#phone"),
        serviceSelect: $("#service"),
        techSelect: $("#technician"),
        date: $("#visit-date"),
        time: $("#visit-time"),
        address: $("#address"),
        notes: $("#notes"),
        submit: $("#submit-btn"),
        featured: $("#featured-tech"),
        featuredPhoto: $("#featured-tech-photo"),
        featuredName: $("#featured-tech-name"),
        featuredSpecialty: $("#featured-tech-specialty"),
        featuredExp: $("#featured-tech-exp"),
        featuredRating: $("#featured-tech-rating"),
        featuredReviews: $("#featured-tech-reviews"),
        featuredOnline: $("#featured-online"),
        dots: $("#tech-dots"),
        selectedPhoto: $("#selected-tech-photo"),
        selectedName: $("#selected-tech-name"),
        selectedSpecialty: $("#selected-tech-specialty"),
        selectedRating: $("#selected-tech-rating"),
        selectedReviews: $("#selected-tech-reviews"),
        selectedYears: $("#selected-tech-years"),
        summaryTechName: $("#summary-tech-name"),
        summaryTechPhoto: $("#summary-tech-photo"),
        summaryServiceName: $("#summary-service-name"),
        techModalList: $("#tech-modal-list"),
        techEmpty: $("#tech-empty"),
        techSearch: $("#tech-search"),
        serviceModalList: $("#service-modal-list"),
        summaryName: $("#summary-name"),
        summaryPhone: $("#summary-phone"),
        summaryService: $("#summary-service"),
        summaryTechnician: $("#summary-technician"),
        summarySchedule: $("#summary-schedule"),
        summaryAddress: $("#summary-address"),
        summaryNotes: $("#summary-notes"),
        toast: $("#toast"),
        toastMessage: $("#toast-message"),
        toastIcon: $(".toast-icon"),
    };

    /* ------------------------------------------------------------------
       HELPERS
     * ------------------------------------------------------------------ */
    function normalizeTech(item) {
        return {
            id: Number(item.id) || 0,
            nama: item.nama || tr("technician", null, "Teknisi"),
            spesialis: item.spesialis || tr("general_technician", null, "Teknisi umum"),
            rating: Number(item.rating) || 0,
            online: Boolean(item.online),
            status: item.status || (item.online ? "tersedia" : "sibuk"),
            foto: item.foto || "",
            pengalaman: Number(item.pengalaman) || 0,
            ulasan: Number(item.ulasan) || 0,
        };
    }

    function normalizeService(item) {
        return {
            id: Number(item.id) || 0,
            nama: item.nama || tr("service", null, "Layanan"),
            harga: Number(item.harga) || 0,
            deskripsi: item.deskripsi || "",
        };
    }

    function escapeHtml(value) {
        return String(value == null ? "" : value).replace(/[&<>"']/g, (character) => ({
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;",
        }[character]));
    }

    function findTech(id) {
        return technicians.find((tech) => tech.id === Number(id)) || null;
    }

    function findService(id) {
        return services.find((service) => service.id === Number(id)) || null;
    }

    function availableTechs() {
        const available = technicians.filter((tech) => tech.online);
        return available.length ? available : technicians;
    }

    function formatRating(value) {
        return Number(value || 0).toFixed(1).replace(".", ",");
    }

    function formatDate(value) {
        if (!value) return "-";
        const parts = String(value).split("-");
        if (parts.length !== 3) return value;
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    function formatTime(value) {
        return value ? String(value).slice(0, 5) : "-";
    }

    function formatRupiah(value) {
        return "Rp " + Number(value || 0).toLocaleString("id-ID");
    }

    function todayValue() {
        const now = new Date();
        const month = String(now.getMonth() + 1).padStart(2, "0");
        const day = String(now.getDate()).padStart(2, "0");
        return `${now.getFullYear()}-${month}-${day}`;
    }

    function createIcons() {
        if (window.lucide && typeof window.lucide.createIcons === "function") {
            window.lucide.createIcons();
        }
    }

    /* ------------------------------------------------------------------
       TOAST
     * ------------------------------------------------------------------ */
    function showToast(message, type) {
        if (!el.toast) return;

        const isError = type === "error";
        el.toast.classList.toggle("is-error", isError);

        if (el.toastIcon) {
            el.toastIcon.innerHTML = isError
                ? '<i data-lucide="alert-triangle" class="w-4 h-4"></i>'
                : '<i data-lucide="check" class="w-4 h-4"></i>';
        }

        if (el.toastMessage) {
            el.toastMessage.textContent = message;
        }

        createIcons();
        window.clearTimeout(state.toastTimer);
        el.toast.classList.add("is-visible");
        state.toastTimer = window.setTimeout(() => {
            el.toast.classList.remove("is-visible");
        }, 2800);
    }

    /* ------------------------------------------------------------------
       PILIH TEKNISI
     * ------------------------------------------------------------------ */
    function selectTechnician(id, options) {
        const opts = options || {};
        const tech = findTech(id);
        if (!tech) return;

        state.selectedTechId = tech.id;

        if (el.featured && el.featuredPhoto) {
            el.featured.dataset.techId = String(tech.id);
            el.featuredPhoto.src = tech.foto;
            el.featuredPhoto.alt = tech.nama;
            if (el.featuredName) el.featuredName.textContent = tech.nama;
            if (el.featuredSpecialty) el.featuredSpecialty.textContent = tech.spesialis;
            if (el.featuredExp) el.featuredExp.textContent = String(tech.pengalaman);
            if (el.featuredRating) el.featuredRating.textContent = formatRating(tech.rating);
            if (el.featuredReviews) el.featuredReviews.textContent = tr("reviews", { count: tech.ulasan });

            if (el.featuredOnline) {
                el.featuredOnline.classList.toggle("is-offline", !tech.online);
                el.featuredOnline.innerHTML = tech.online
                    ? `<span class="online-dot"></span> ${escapeHtml(tr("online"))}`
                    : `<span class="online-dot"></span> ${escapeHtml(tr("busy"))}`;
            }

            el.featured.classList.remove("is-changing");
            void el.featured.offsetWidth;
            el.featured.classList.add("is-changing");
        }

        if (el.selectedPhoto) {
            el.selectedPhoto.src = tech.foto;
            el.selectedPhoto.alt = tech.nama;
        }
        if (el.selectedName) el.selectedName.textContent = tech.nama;
        if (el.selectedSpecialty) el.selectedSpecialty.textContent = tech.spesialis;
        if (el.selectedRating) el.selectedRating.textContent = formatRating(tech.rating);
        if (el.selectedReviews) el.selectedReviews.textContent = tr("reviews", { count: tech.ulasan });
        if (el.selectedYears) el.selectedYears.textContent = String(tech.pengalaman);

        if (el.summaryTechName) el.summaryTechName.textContent = tech.nama;
        if (el.summaryTechPhoto) {
            el.summaryTechPhoto.src = tech.foto;
            el.summaryTechPhoto.alt = tech.nama;
            el.summaryTechPhoto.hidden = false;
        }

        if (el.techSelect) {
            const option = Array.from(el.techSelect.options).find((item) => Number(item.value) === tech.id);
            if (option) el.techSelect.value = String(tech.id);
        }

        syncDots();
        syncTechModalSelection();
        refreshSummary();

        if (!opts.silent) {
            showToast(tr("technician_selected", { name: tech.nama }));
        }
    }

    function syncDots() {
        $$(".tech-dot").forEach((dot) => {
            dot.classList.toggle("is-active", Number(dot.dataset.techId) === state.selectedTechId);
        });
    }

    function syncTechModalSelection() {
        $$(".modal-tech-item").forEach((item) => {
            const isSelected = Number(item.dataset.techId) === state.selectedTechId;
            item.classList.toggle("is-selected", isSelected);
            item.setAttribute("aria-pressed", isSelected ? "true" : "false");
        });
    }

    function cycleTechnician() {
        const list = availableTechs();
        if (!list.length) return;

        let currentIndex = list.findIndex((tech) => tech.id === state.selectedTechId);
        if (currentIndex < 0) currentIndex = 0;
        const nextIndex = (currentIndex + 1) % list.length;
        selectTechnician(list[nextIndex].id);
    }

    /* ------------------------------------------------------------------
       LAYANAN
     * ------------------------------------------------------------------ */
    function updateServiceUI() {
        const service = findService(state.selectedServiceId);
        if (el.summaryServiceName) {
            el.summaryServiceName.textContent = service ? service.nama : tr("not_selected");
        }

        $$(".modal-service-item").forEach((item) => {
            const isSelected = Number(item.dataset.serviceId) === state.selectedServiceId;
            item.classList.toggle("is-selected", isSelected);
            item.setAttribute("aria-pressed", isSelected ? "true" : "false");
        });

        refreshSummary();
    }

    function selectService(id, options) {
        const opts = options || {};
        const service = findService(id);
        if (!service) return;

        state.selectedServiceId = service.id;
        if (el.serviceSelect) el.serviceSelect.value = String(service.id);
        updateServiceUI();

        if (!opts.silent) {
            showToast(tr("service_selected", { name: service.nama }));
        }
    }

    function renderServiceModal() {
        if (!el.serviceModalList) return;

        if (!services.length) {
            el.serviceModalList.innerHTML = `<p class="modal-empty" style="display:block">${escapeHtml(tr("no_services"))}</p>`;
            return;
        }

        el.serviceModalList.innerHTML = services.map((service) => `
            <button type="button"
                    class="modal-service-item ${service.id === state.selectedServiceId ? "is-selected" : ""}"
                    data-service-id="${service.id}"
                    aria-pressed="${service.id === state.selectedServiceId ? "true" : "false"}">
                <span class="modal-service-copy">
                    <span class="modal-service-name">${escapeHtml(service.nama)}</span>
                    <span class="modal-service-desc">${escapeHtml(service.deskripsi || tr("service_fallback"))}</span>
                </span>
                <span class="modal-service-price">${formatRupiah(service.harga)}</span>
            </button>
        `).join("");
    }

    /* ------------------------------------------------------------------
       TECHNICIAN MODAL
     * ------------------------------------------------------------------ */
    function renderTechModal(query) {
        if (!el.techModalList) return;

        const keyword = String(query || "").trim().toLowerCase();
        const filtered = technicians.filter((tech) => {
            if (!keyword) return true;
            return tech.nama.toLowerCase().includes(keyword) || tech.spesialis.toLowerCase().includes(keyword);
        });

        el.techModalList.innerHTML = filtered.map((tech) => `
            <button type="button"
                    class="modal-tech-item ${tech.id === state.selectedTechId ? "is-selected" : ""} ${!tech.online ? "is-busy-item" : ""}"
                    data-tech-id="${tech.id}"
                    aria-pressed="${tech.id === state.selectedTechId ? "true" : "false"}"
                    ${!tech.online ? "disabled" : ""}>
                <img src="${escapeHtml(tech.foto)}" alt="${escapeHtml(tech.nama)}" loading="lazy">
                <span class="modal-tech-copy">
                    <span class="modal-tech-name">${escapeHtml(tech.nama)}</span>
                    <span class="modal-tech-meta">
                        <span>${escapeHtml(tech.spesialis)}</span>
                        <span>•</span>
                        <span>★ ${formatRating(tech.rating)}</span>
                        <span>•</span>
                        <span class="modal-tech-status ${tech.online ? "" : "is-busy"}">
                            ${escapeHtml(tech.online ? tr("online") : tr("on_duty"))}
                        </span>
                    </span>
                </span>
                <span class="modal-tech-check">
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                </span>
            </button>
        `).join("");

        if (el.techEmpty) {
            el.techEmpty.hidden = filtered.length > 0;
        }

        createIcons();
    }

    /* ------------------------------------------------------------------
       RINGKASAN
     * ------------------------------------------------------------------ */
    function refreshSummary() {
        const tech = findTech(state.selectedTechId);
        const service = findService(state.selectedServiceId);

        if (el.summaryName) el.summaryName.textContent = (el.fullname && el.fullname.value.trim()) || "-";
        if (el.summaryPhone) el.summaryPhone.textContent = (el.phone && el.phone.value.trim()) || "-";
        if (el.summaryService) el.summaryService.textContent = service ? service.nama : tr("not_selected");
        if (el.summaryTechnician) el.summaryTechnician.textContent = tech ? `${tech.nama} - ${tech.spesialis}` : tr("auto_select");

        const date = el.date ? el.date.value : "";
        const time = el.time ? el.time.value : "";
        const schedule = date || time
            ? `${formatDate(date)}${time ? ", " + formatTime(time) : ""}`
            : "-";
        if (el.summarySchedule) el.summarySchedule.textContent = schedule;

        if (el.summaryAddress) el.summaryAddress.textContent = (el.address && el.address.value.trim()) || "-";
        if (el.summaryNotes) el.summaryNotes.textContent = (el.notes && el.notes.value.trim()) || "-";
    }

    /* ------------------------------------------------------------------
       MODAL
     * ------------------------------------------------------------------ */
    function openModal(modal) {
        if (!modal) return;

        state.lastFocus = document.activeElement;
        modal.classList.add("is-open");
        modal.setAttribute("aria-hidden", "false");
        document.body.classList.add("modal-open");

        window.setTimeout(() => {
            const focusTarget = modal.querySelector("input, button:not(.modal-close), [href]");
            if (focusTarget) focusTarget.focus({ preventScroll: true });
        }, 120);
    }

    function closeModal(modal) {
        if (!modal) return;

        modal.classList.remove("is-open");
        modal.setAttribute("aria-hidden", "true");

        if (!$(".modal.is-open")) {
            document.body.classList.remove("modal-open");
        }

        if (state.lastFocus && typeof state.lastFocus.focus === "function") {
            state.lastFocus.focus({ preventScroll: true });
        }
    }

    function closeTopModal() {
        const openModals = $$(".modal.is-open");
        if (openModals.length) {
            closeModal(openModals[openModals.length - 1]);
        }
    }

    function openTechnicianModal() {
        renderTechModal("");
        if (el.techSearch) el.techSearch.value = "";
        openModal($("#modal-technician"));
    }

    function openServiceModal() {
        renderServiceModal();
        openModal($("#modal-services"));
    }

    function openSummaryModal() {
        refreshSummary();
        openModal($("#modal-summary"));
    }

    /* ------------------------------------------------------------------
       VALIDASI & SUBMIT
     * ------------------------------------------------------------------ */
    function validateForm() {
        const requiredFields = [
            { field: el.fullname, key: "full_name" },
            { field: el.phone, key: "phone_number" },
            { field: el.serviceSelect, key: "service_type" },
            { field: el.techSelect, key: "technician" },
            { field: el.date, key: "visit_date" },
            { field: el.time, key: "visit_time" },
            { field: el.address, key: "complete_address" },
        ];

        for (const item of requiredFields) {
            if (!item.field) continue;
            const value = String(item.field.value || "").trim();

            if (!value || (item.field === el.techSelect && Number(value) === 0)) {
                item.field.focus();
                item.field.reportValidity();
                showToast(tr("field_required", { field: tr(item.key) }), "error");
                return false;
            }
        }

        if (el.date && el.date.value && el.date.value < todayValue()) {
            el.date.focus();
            showToast(tr("past_date"), "error");
            return false;
        }

        if (el.phone) {
            const digits = el.phone.value.replace(/\D/g, "");
            if (digits.length < 8) {
                el.phone.focus();
                showToast(tr("min_phone"), "error");
                return false;
            }
        }

        return true;
    }

    function setSubmitting() {
        if (!el.submit) return;
        el.submit.disabled = true;
        el.submit.classList.add("is-loading");
        const label = el.submit.querySelector(".submit-label span");
        if (label) label.textContent = tr("sending");
    }

    /* ------------------------------------------------------------------
       INIT
       Catatan: dropdown notifikasi, dropdown profil, dan menu mobile
       sudah ditangani assets/js/theme.js karena header kini komponen bersama.
       File ini hanya mengurus interaksi khusus halaman booking.
     * ------------------------------------------------------------------ */
    function init() {
        /* Teknisi awal */
        if (state.selectedTechId && findTech(state.selectedTechId)) {
            selectTechnician(state.selectedTechId, { silent: true });
        } else {
            const first = availableTechs()[0];
            if (first) selectTechnician(first.id, { silent: true });
        }

        /* Layanan awal */
        if (state.selectedServiceId && findService(state.selectedServiceId)) {
            selectService(state.selectedServiceId, { silent: true });
        } else if (services[0]) {
            selectService(services[0].id, { silent: true });
        } else {
            refreshSummary();
        }

        /* Pilih teknisi lewat dropdown */
        if (el.techSelect) {
            el.techSelect.addEventListener("change", () => {
                const id = Number(el.techSelect.value);
                if (id > 0) selectTechnician(id);
            });
        }

        /* Pilih layanan lewat dropdown */
        if (el.serviceSelect) {
            el.serviceSelect.addEventListener("change", () => {
                const id = Number(el.serviceSelect.value);
                if (id > 0) selectService(id);
            });
        }

        /* Tombol ganti teknisi */
        const nextTech = $("#next-tech");
        if (nextTech) nextTech.addEventListener("click", cycleTechnician);

        const viewAll = $("#view-all-tech");
        if (viewAll) viewAll.addEventListener("click", openTechnicianModal);

        const changeTech = $("#change-tech");
        if (changeTech) changeTech.addEventListener("click", openTechnicianModal);

        /* Dots teknisi */
        if (el.dots) {
            el.dots.addEventListener("click", (event) => {
                const dot = event.target.closest("[data-tech-id]");
                if (!dot) return;
                selectTechnician(Number(dot.dataset.techId));
            });
        }

        /* Baris ringkasan */
        $$("[data-open-modal]").forEach((row) => {
            row.addEventListener("click", () => {
                const target = row.dataset.openModal;
                if (target === "technician") openTechnicianModal();
                else if (target === "services") openServiceModal();
                else openSummaryModal();
            });
        });

        /* Modal teknisi */
        if (el.techModalList) {
            el.techModalList.addEventListener("click", (event) => {
                const item = event.target.closest(".modal-tech-item");
                if (!item || item.disabled) return;
                const tech = findTech(item.dataset.techId);
                if (!tech) return;
                selectTechnician(tech.id);
                closeModal($("#modal-technician"));
            });
        }

        if (el.techSearch) {
            el.techSearch.addEventListener("input", () => {
                renderTechModal(el.techSearch.value);
            });
        }

        /* Modal layanan */
        if (el.serviceModalList) {
            el.serviceModalList.addEventListener("click", (event) => {
                const item = event.target.closest(".modal-service-item");
                if (!item) return;
                selectService(Number(item.dataset.serviceId));
                closeModal($("#modal-services"));
            });
        }

        /* Tutup modal */
        $$("[data-close-modal]").forEach((trigger) => {
            trigger.addEventListener("click", () => {
                closeModal(trigger.closest(".modal"));
            });
        });

        $$(".modal").forEach((modal) => {
            modal.addEventListener("mousedown", (event) => {
                if (event.target === modal) closeModal(modal);
            });
        });

        /* Kirim dari ringkasan */
        const summarySend = $("#summary-send");
        if (summarySend) {
            summarySend.addEventListener("click", () => {
                closeModal($("#modal-summary"));
                if (el.form) {
                    if (typeof el.form.requestSubmit === "function") el.form.requestSubmit();
                    else el.form.submit();
                }
            });
        }

        /* Form */
        if (el.form) {
            el.form.addEventListener("input", refreshSummary);
            el.form.addEventListener("change", refreshSummary);
            el.form.addEventListener("submit", (event) => {
                if (!validateForm()) {
                    event.preventDefault();
                    return;
                }
                setSubmitting();
            });
        }

        /*
         * Keyboard:
         * - Escape menutup modal booking yang sedang terbuka.
         * - Tab ditahan di dalam modal (focus trap) agar aksesibilitas tetap baik.
         * Header/dropdown tidak ditangani di sini karena sudah ada theme.js.
         */
        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                closeTopModal();
            }

            if (event.key === "Tab") {
                const openModalEl = $(".modal.is-open .modal-panel");
                if (!openModalEl) return;

                const focusables = $$(
                    'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
                    openModalEl
                ).filter((item) => item.offsetParent !== null);

                if (!focusables.length) return;

                const first = focusables[0];
                const last = focusables[focusables.length - 1];

                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });

        /*
         * Teks yang dirender ulang oleh JavaScript (mis. jumlah ulasan, status
         * online, dan isi modal) ditandai data-i18n-skip di markup. Saat bahasa
         * diganti, render ulang bagian tersebut supaya ikut bahasa terbaru.
         */
        document.addEventListener("techhub:languagechange", () => {
            renderTechModal(el.techSearch ? el.techSearch.value : "");
            renderServiceModal();
            if (state.selectedTechId) {
                selectTechnician(state.selectedTechId, { silent: true });
            }
            updateServiceUI();
            refreshSummary();
        });

        /* Success modal */
        if (raw.success && typeof raw.success === "object") {
            window.setTimeout(() => {
                openModal($("#modal-success"));
                showToast(tr("request_sent"));
            }, 320);
        }

    }

    init();
})();
