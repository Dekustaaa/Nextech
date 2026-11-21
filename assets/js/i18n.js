/**
 * i18n.js — kamus dan mesin ganti bahasa Nexatech (Indonesia / Inggris).
 *
 * Kenapa dipusatkan?
 * - Tombol bahasa ada di header publik dan panel admin. Keduanya memakai kamus
 *   yang sama agar istilah seperti "Keranjang", "Pesanan", dan "Teknisi" tidak
 *   diterjemahkan berbeda di tiap halaman.
 * - Teks yang sudah ada di markup tidak perlu diberi atribut satu per satu.
 *   Mesin ini mencari teks yang cocok dengan kamus, lalu menyimpan teks asli
 *   agar bisa dikembalikan saat bahasa diganti lagi.
 * - String yang dibuat JavaScript (mis. jumlah ulasan atau toast) bisa memakai
 *   window.techI18n.t("key", { variabel: nilai }).
 *
 * Catatan: hanya frasa yang ada di kamus yang diterjemahkan. Nama produk,
 * nama pelanggan, dan data lain dari database tetap apa adanya.
 */
(function () {
    "use strict";

    var STORAGE_KEY = "techhub-lang";
    var currentLang = "id";
    // Judul tab disimpan sebagai teks asli agar bisa diterjemahkan bolak-balik.
    var originalTitle = document.title;

    try {
        var savedLanguage = localStorage.getItem(STORAGE_KEY);
        if (savedLanguage === "en" || savedLanguage === "id") {
            currentLang = savedLanguage;
        }
    } catch (error) {
        // localStorage bisa diblokir; gunakan bahasa Indonesia sebagai default.
    }

    document.documentElement.lang = currentLang;
    document.documentElement.setAttribute("data-lang", currentLang);

    /*
     * KEYS dipakai untuk teks dinamis yang dibuat JavaScript.
     * PHRASES dipakai untuk mencocokkan teks yang sudah tercetak di halaman.
     */
    var KEYS = Object.create(null);
    var PHRASES = Object.create(null);
    var PATTERNS = [];

    function key(name, id, en) {
        KEYS[name] = { id: id, en: en };
    }

    function add(id, en, aliases) {
        var entry = { id: id, en: en };
        PHRASES[id] = entry;
        if (en && en !== id) {
            PHRASES[en] = entry;
        }
        (aliases || []).forEach(function (alias) {
            if (alias) PHRASES[alias] = entry;
        });
    }

    function pattern(regex, id, en) {
        PATTERNS.push({ re: regex, id: id, en: en });
    }

    /* ------------------------------------------------------------------
       KUNCI UNTUK TEKS DINAMIS
     * ------------------------------------------------------------------ */
    key("reviews", "({count} Ulasan)", "({count} Reviews)");
    key("not_selected", "Belum dipilih", "Not selected");
    key("auto_select", "Pilih otomatis", "Auto select");
    key("technician", "Teknisi", "Technician");
    key("general_technician", "Teknisi umum", "General technician");
    key("service", "Layanan", "Service");
    key("online", "Online", "Online");
    key("busy", "Sibuk", "Busy");
    key("on_duty", "Sedang bertugas", "On duty");
    key("technician_selected", "{name} dipilih sebagai teknisi Anda.", "{name} has been selected as your technician.");
    key("service_selected", "Layanan {name} dipilih.", "{name} service selected.");
    key("full_name", "Nama lengkap", "Full name");
    key("phone_number", "Nomor telepon", "Phone number");
    key("service_type", "Jenis layanan", "Service type");
    key("visit_date", "Tanggal kunjungan", "Visit date");
    key("visit_time", "Jam kunjungan", "Visit time");
    key("complete_address", "Alamat lengkap", "Complete address");
    key("no_services", "Belum ada layanan tersedia.", "No services available yet.");
    key("no_technicians", "Belum ada teknisi yang terdaftar. Anda tetap bisa mengirim permintaan dan kami akan mencarikan teknisi untuk Anda.", "No technicians are registered yet. You can still send a request and we will find a technician for you.");
    key("service_fallback", "Layanan teknisi profesional.", "Professional technician service.");
    key("sending", "Mengirim permintaan...", "Sending request...");
    key("request_sent", "Permintaan berhasil dikirim.", "Request sent successfully.");
    key("field_required", "{field} wajib diisi.", "{field} is required.");
    key("past_date", "Tanggal kunjungan tidak boleh di masa lalu.", "The visit date cannot be in the past.");
    key("min_phone", "Nomor telepon minimal 8 digit.", "The phone number must be at least 8 digits.");
    key("dark_mode", "Mode gelap", "Dark mode");
    key("light_mode", "Mode terang", "Light mode");
    key("enable_dark", "Aktifkan mode gelap", "Enable dark mode");
    key("enable_light", "Aktifkan mode terang", "Enable light mode");
    key("toggle_language", "Ganti ke bahasa Inggris", "Switch to Indonesian");

    /* ------------------------------------------------------------------
       KAMUS FRASA UMUM, HEADER, DAN FOOTER
     * ------------------------------------------------------------------ */
    add("Beranda", "Home", ["Homepage", "Home"]);
    add("Semua Produk", "All Products", ["All Product", "All Products"]);
    add("Nexsearch", "Nexsearch");
    add("Solusi Teknologi & Layanan Teknisi", "Technology Solutions & Technician Services", ["Technology Solutions & Technician Services"]);
    add("Booking", "Booking");
    add("Keranjang", "Cart", ["Cart"]);
    add("Notifikasi", "Notifications", ["Notifications"]);
    add("Profil saya", "My Profile", ["Profil Saya", "My profile", "My Profile"]);
    add("Booking teknisi", "Technician Booking", ["Booking Teknisi", "Technician booking", "Technician Booking"]);
    add("Keluar", "Log Out", ["Log out", "Logout", "Log Out"]);
    add("Masuk", "Log In", ["Log in", "Login", "Log In"]);
    add("Daftar", "Register", ["Sign up", "Sign Up"]);
    add("Belum masuk ke akun", "Not signed in", ["Not signed in"]);
    add("Menu akun", "Account menu", ["Account menu"]);
    add("Navigasi utama", "Main navigation", ["Main navigation"]);
    add("Buka menu", "Open menu", ["Open menu"]);
    add("Buka menu admin", "Open admin menu", ["Open admin menu"]);
    add("Buka profil admin", "Open admin profile", ["Open admin profile"]);
    add("Nexatech - Beranda", "Nexatech - Home", ["Nexatech - Home"]);
    add("Kembali ke beranda", "Back to home", ["Back to home"]);
    add("Teknologi untuk ide besarmu.", "Technology for your big ideas.");
    add("Kategori & brand", "Categories & brands", ["Kategori & Brand", "Categories & Brands"]);
    add("Kategori & Brand", "Categories & Brands", ["Categories & Brands"]);
    add("Kembali", "Back", ["Back"]);
    add("Bahasa", "Language", ["Language"]);
    add("Mode gelap", "Dark mode", ["Dark mode"]);
    add("Mode terang", "Light mode", ["Light mode"]);
    add("Kurangi jumlah", "Decrease quantity", ["Decrease quantity"]);
    add("Tambah jumlah", "Increase quantity", ["Increase quantity"]);
    add("Tambah ke keranjang", "Add to cart", ["Add to cart"]);
    add("Beli sekarang", "Buy now", ["Buy now"]);
    add("Terjual", "Sold", ["Sold"]);
    add("Lanjut belanja", "Continue shopping", ["Continue shopping"]);
    add("Lanjut ke checkout", "Continue to checkout", ["Continue to checkout"]);
    add("Kembali ke keranjang", "Back to cart", ["Back to cart"]);
    add("Lihat", "View", ["View"]);
    add("Lihat semua", "View all", ["View all"]);
    add("Lihat produk", "View products", ["View products"]);
    add("Lihat semua produk", "View all products", ["View all products"]);
    add("Lihat semua kategori", "View all categories", ["View all categories"]);
    add("Lihat semua brand", "View all brands", ["View all brands"]);
    add("Lihat toko", "View store", ["View store"]);
    add("Cari", "Search", ["Search"]);
    add("Reset", "Reset");
    add("Semua", "All", ["All"]);
    add("Kategori", "Category", ["Category"]);
    add("Brand", "Brand");
    add("Produk", "Products", ["Product"]);
    add("Layanan", "Services", ["Service"]);
    add("Teknisi", "Technician", ["Technician"]);
    add("Pelanggan", "Customers", ["Customer"]);
    add("Pesanan", "Orders", ["Order"]);
    add("Dashboard", "Dashboard");
    add("Profil", "Profile", ["Profile"]);
    add("Pengaturan", "Settings", ["Settings"]);
    add("Nama", "Name", ["Name"]);
    add("Nama lengkap", "Full name", ["Full name", "Fullname"]);
    add("Email", "Email");
    add("Password", "Password");
    add("Telepon", "Phone", ["Phone"]);
    add("Alamat", "Address", ["Address"]);
    add("Catatan", "Notes", ["Notes"]);
    add("Tanggal", "Date", ["Date"]);
    add("Jam", "Time", ["Time"]);
    add("Harga", "Price", ["Price"]);
    add("Stok", "Stock", ["Stock"]);
    add("Status", "Status");
    add("Total", "Total");
    add("Subtotal", "Subtotal");
    add("Aksi", "Action", ["Action"]);
    add("Atur", "Manage", ["Manage"]);
    add("Ubah", "Change", ["Change"]);
    add("Detail", "Details", ["Details"]);
    add("Deskripsi", "Description", ["Description"]);
    add("Gambar", "Image", ["Image"]);
    add("Icon", "Icon");
    add("Badge", "Badge");
    add("Kode", "Code", ["Code"]);
    add("Kontak", "Contact", ["Contact"]);
    add("Rating", "Rating");
    add("Order", "Order");
    add("Paket", "Package", ["Package"]);
    add("Bayar", "Payment", ["Payment"]);
    add("Booking", "Booking");

    /* Notifikasi contoh di dropdown header. */
    add("Pesanan selesai", "Order completed", ["Order completed"]);
    add("Teknisi menyelesaikan perbaikan laptop Anda.", "A technician has completed your laptop repair.", ["A technician has completed your laptop repair."]);
    add("10 menit lalu", "10 minutes ago", ["10 minutes ago"]);
    add("Jadwal kunjungan", "Visit schedule", ["Visit schedule"]);
    add("Jangan lupa konfirmasi jadwal besok pagi.", "Do not forget to confirm tomorrow morning's schedule.", ["Don't forget to confirm tomorrow morning's schedule."]);
    add("1 jam lalu", "1 hour ago", ["1 hour ago"]);
    add("Promo teknisi", "Technician promo", ["Technician promo"]);
    add("Diskon 15% untuk layanan instalasi.", "15% off installation services.", ["15% off installation services."]);
    add("Kemarin", "Yesterday", ["Yesterday"]);

    /* ------------------------------------------------------------------
       BERANDA
     * ------------------------------------------------------------------ */
    add("Solusi teknologi lengkap untuk", "Complete technology solutions for", ["Complete technology solutions for"]);
    add("bisnis dan rumahan", "business and home", ["business and home"]);
    add("Beli produk teknologi, pesan jasa teknisi, dan kelola kebutuhan perangkat Anda dari satu tempat yang cepat, rapi, dan mudah dipahami.", "Buy technology products, book technician services, and manage your device needs from one fast, tidy, and easy-to-understand place.");
    add("Belanja sekarang", "Shop now", ["Shop now"]);
    add("Pesan teknisi", "Book a technician", ["Book a technician"]);
    add("Garansi resmi", "Official warranty", ["Official warranty"]);
    add("Layanan 24/7", "24/7 support", ["24/7 support"]);
    add("Teknisi terverifikasi", "Verified technicians", ["Verified technicians"]);
    add("Rating pelanggan", "Customer rating", ["Customer rating"]);
    add("Teknisi siap membantu", "Technicians ready to help", ["Technicians ready to help"]);
    add("Online hari ini", "Online today", ["Online today"]);
    add("Kategori produk & layanan", "Product & service categories", ["Kategori produk &amp; layanan", "Product & service categories"]);
    add("Pilih kategori, lalu jelajahi brand yang tersedia di dalamnya.", "Choose a category, then explore the brands available inside.");
    add("Produk populer", "Popular products", ["Popular products"]);
    add("Hasil pencarian", "Search results", ["Search results"]);
    add("Rekomendasi perangkat untuk kerja, belajar, dan kreativitas.", "Device recommendations for work, study, and creativity.");
    add("Cari laptop, mouse, aksesori...", "Search laptops, mice, accessories...", ["Search laptops, mice, accessories..."]);
    add("Produk tidak ditemukan", "No products found", ["No products found"]);
    add("Coba kata kunci lain, misalnya \"laptop\" atau \"mouse\".", "Try another keyword, for example \"laptop\" or \"mouse\".");
    add("Belum ada rating", "No ratings yet", ["No ratings yet"]);
    add("Stok tersedia", "In stock", ["In stock"]);
    add("Perangkat bermasalah? Teknisi kami siap datang.", "Device trouble? Our technicians are ready to come.");
    add("Pilih teknisi, tentukan layanan dan jadwal kunjungan. Proses cepat, harga transparan, dan teknisi terverifikasi.", "Choose a technician, select a service and visit schedule. Fast process, transparent pricing, and verified technicians.");

    /* ------------------------------------------------------------------
       KATALOG / KATEGORI
     * ------------------------------------------------------------------ */
    add("Katalog Nexatech", "Nexatech Catalog", ["Nexatech Catalog"]);
    add("Jelajahi kategori", "Explore categories", ["Explore categories"]);
    add("Pilih brand favoritmu untuk mempersempit pencarian.", "Choose your favorite brand to narrow the search.");
    add("Temukan produk teknologi berdasarkan kategori dan brand yang tersedia.", "Find technology products by available category and brand.");
    add("Pilih kategori", "Choose a category", ["Choose a category"]);
    add("Semua kategori produk dan layanan Nexatech.", "All Nexatech product and service categories.");
    add("Pilih brand untuk melihat produk yang tersedia.", "Choose a brand to see available products.");
    add("Reset brand", "Reset brand", ["Reset brand"]);
    add("Produk belum tersedia", "No products available yet", ["No products available yet"]);
    add("Lihat semua brand", "View all brands", ["View all brands"]);
    add("Semua produk", "All products", ["All products"]);
    add("untuk brand", "for brand", ["for brand"]);

    /* ------------------------------------------------------------------
       NEXSEARCH / REKOMENDASI
     * ------------------------------------------------------------------ */
    add("Cari produk sesuai budget", "Find products by budget", ["Find products by budget"]);
    add("Masukkan budget dan kategori, kami carikan produk yang paling pas untuk Anda.", "Enter your budget and category, and we will find the best products for you.");
    add("Budget Anda (Rp)", "Your budget (Rp)", ["Your budget (Rp)"]);
    add("contoh: 10000000", "example: 10000000", ["example: 10000000"]);
    add("Semua kategori", "All categories", ["All categories"]);
    add("Cari rekomendasi", "Find recommendations", ["Find recommendations"]);
    add("Contoh budget:", "Budget examples:", ["Budget examples:"]);
    add("Paling cocok untuk budget Anda", "Best fit for your budget", ["Best fit for your budget"]);
    add("Paling sesuai", "Best match", ["Best match"]);
    add("Lihat detail", "View details", ["View details"]);
    add("Pilihan lain dalam budget Anda", "Other options within your budget", ["Other options within your budget"]);
    add("Semua harga di bawah", "All prices below", ["All prices below"]);
    add("Belum ada produk dalam budget ini", "No products within this budget", ["No products within this budget"]);
    add("Budget Anda kurang", "Your budget is short by", ["Your budget is short by"]);
    add("Produk termurah kami adalah", "Our cheapest product is", ["Our cheapest product is"]);
    add("Lihat produk termurah", "View cheapest product", ["View cheapest product"]);
    add("Belum ada produk di kategori ini.", "No products in this category yet.", ["No products in this category yet."]);
    add("Sesuai budget", "Within budget", ["Within budget"]);
    add("Produk terbaik di bawah budget Anda.", "The best products under your budget.", ["The best products under your budget."]);
    add("Perbandingan jelas", "Clear comparison", ["Clear comparison"]);
    add("Lihat pilihan lain dalam satu daftar.", "See other options in one list.", ["See other options in one list."]);
    add("Rekomendasi cepat", "Fast recommendations", ["Fast recommendations"]);
    add("Hasil langsung tanpa proses panjang.", "Instant results without a long process.", ["Instant results without a long process."]);
    add("Cari Sesuai Budget", "Find by Budget", ["Find by Budget"]);

    /* ------------------------------------------------------------------
       BOOKING TEKNISI
     * ------------------------------------------------------------------ */
    add("Pesan Teknisi", "Book a Technician", ["Book a Technician"]);
    add("Temukan teknisi yang tepat", "Find the right technician", ["Find the right technician"]);
    add("untuk kebutuhan perangkat Anda", "for your device needs", ["for your device needs"]);
    add("Isi detail di bawah untuk menjadwalkan permintaan layanan.", "Fill in the details below to schedule a service request.", ["Fill in the details below to schedule a service request."]);
    add("Teknisi kami akan menghubungi Anda secepatnya.", "Our technician will get back to you as soon as possible.", ["Our technician will get back to you as soon as possible."]);
    add("Cepat", "Fast", ["Fast"]);
    add("Terpercaya", "Reliable", ["Reliable"]);
    add("Profesional", "Professional", ["Professional"]);
    add("Teknisi Tersedia", "Available Technician", ["Available Technician"]);
    add("Pilih teknisi sesuai kebutuhan Anda.", "Choose a technician based on your needs.", ["Choose a technician based on your needs."]);
    add("Lihat Semua", "View All", ["View All"]);
    add("Teknisi pilihan", "Featured technician", ["Featured technician"]);
    add("Teknisi Terverifikasi", "Verified Technician", ["Verified Technician"]);
    add("+ Tahun Pengalaman", "+ Years Experience", ["+ Years Experience"]);
    add("+ Tahun", "+ Years", ["+ Years"]);
    add("Nama Lengkap", "Fullname", ["Fullname"]);
    add("Isi nama lengkap", "Fill the fullname", ["Fill the fullname"]);
    add("Nomor Telepon", "Phone Number", ["Phone Number"]);
    add("Jenis Layanan", "Type of Services", ["Type of Services"]);
    add("Tanggal Kunjungan", "Date of Visit", ["Date of Visit"]);
    add("Alamat Lengkap", "Complete Address", ["Complete Address"]);
    add("Isi alamat Anda", "Enter your address", ["Fill your address", "Enter your address"]);
    add("Catatan/Keluhan", "Note/Complain", ["Note/Complain"]);
    add("Isi keluhan Anda", "Describe your issue", ["Fill your complain", "Describe your issue"]);
    add("Kirim Permintaan", "Send request", ["Send request"]);
    add("Terpercaya & Profesional", "Trusted & Professional", ["Trusted & Professional"]);
    add("Teknisi kami bersertifikat dan siap membantu Anda.", "Our technicians are certified and ready to help you.", ["Our technicians are certified and ready to help you."]);
    add("Ringkasan Booking", "Booking Summary", ["Booking Summary"]);
    add("Ringkasan booking", "Booking summary", ["Booking summary"]);
    add("Lihat detail booking Anda", "View your booking details", ["View your booking details"]);
    add("Teknisi Pilihan Anda", "Your Selected Technician", ["Your Selected Technician"]);
    add("Terverifikasi", "Verified", ["Verified"]);
    add("Umpan Balik Baik", "Good Feedback", ["Good Feedback"]);
    add("Mengapa Memilih Kami?", "Why Choose Us?", ["Why Choose Us?"]);
    add("Respons Cepat", "Fast Response", ["Fast Response"]);
    add("Dapatkan bantuan cepat", "Get help quickly", ["Get help quickly"]);
    add("Tim Profesional", "Professional Team", ["Professional Team"]);
    add("Berpengalaman & bersertifikat", "Experienced & certified", ["Experienced & certified"]);
    add("Pembayaran Aman", "Secure Payment", ["Secure Payment"]);
    add("Berbagai metode pembayaran", "Multiple payment methods", ["Multiple payment methods"]);
    add("100% Kepuasan", "100% Satisfaction", ["100% Satisfaction"]);
    add("Kepercayaan Anda prioritas kami", "Your trust is our priority", ["Your trust is our priority"]);
    add("Pilih teknisi Anda", "Choose your technician", ["Choose your technician"]);
    add("Pilih teknisi", "Choose technician", ["Choose technician"]);
    add("Semua teknisi terverifikasi dan siap membantu.", "All technicians are verified and ready to help.");
    add("Cari nama atau keahlian teknisi...", "Search technician name or skill...", ["Search technician name or skill..."]);
    add("Tidak ada teknisi yang cocok dengan pencarian.", "No technicians match your search.", ["No technicians match your search."]);
    add("Layanan Kami", "Our Services", ["Our Services"]);
    add("Pilih jenis layanan", "Choose a service type", ["Choose a service type"]);
    add("Harga akhir menyesuaikan kondisi perangkat.", "The final price depends on the device condition.");
    add("Ringkasan permintaan", "Request summary", ["Request summary"]);
    add("Periksa kembali detail sebelum mengirim permintaan.", "Review the details before sending the request.");
    add("Nomor telepon", "Phone number", ["Phone number"]);
    add("Jadwal", "Schedule", ["Schedule"]);
    add("Kirim permintaan", "Send request", ["Send request"]);
    add("Permintaan Terkirim", "Request Sent", ["Request Sent"]);
    add("Permintaan berhasil dikirim!", "Request sent successfully!", ["Request sent successfully!"]);
    add("Tim teknisi kami akan menghubungi Anda untuk konfirmasi jadwal.", "Our technician team will contact you to confirm the schedule.");
    add("Nomor booking", "Booking number", ["Booking number"]);
    add("Booking lagi", "Book again", ["Book again"]);
    add("Teknisi dipilih.", "Technician selected.", ["Technician selected."]);
    add("Pilih otomatis", "Auto select", ["Auto select"]);
    add("Belum dipilih", "Not selected", ["Not selected"]);
    add("Pilih teknisi terlebih dahulu", "Choose a technician first", ["Choose a technician first"]);
    add("Ganti teknisi", "Change technician", ["Change technician"]);
    add("Lihat teknisi berikutnya", "View next technician", ["View next technician"]);
    add("Sedang bertugas", "On duty", ["On duty"]);
    add("Sibuk", "Busy", ["Busy"]);
    add("Layanan teknisi profesional.", "Professional technician service.", ["Professional technician service."]);
    add("Belum ada layanan tersedia.", "No services available yet.", ["No services available yet."]);
    add("Mengirim permintaan...", "Sending request...", ["Sending request..."]);
    add("Nama lengkap", "Full name", ["Full name"]);
    add("Jenis layanan", "Service type", ["Service type"]);
    add("Tanggal kunjungan", "Visit date", ["Visit date"]);
    add("Jam kunjungan", "Visit time", ["Visit time"]);
    add("Alamat lengkap", "Complete address", ["Complete address"]);
    add("Tutup", "Close", ["Close"]);

    /* ------------------------------------------------------------------
       BOOKING TEKNISI (HALAMAN LAMA)
     * ------------------------------------------------------------------ */
    add("Permintaan Layanan", "Service Request", ["Service Request"]);
    add("Buat permintaan layanan, pilih teknisi, dan pantau aktivitas pesanan Anda.", "Create a service request, choose a technician, and monitor your order activity.");
    add("Coba booking terbaru", "Try a new booking", ["Try a new booking"]);
    add("Buat permintaan baru", "Create a new request", ["Create a new request"]);
    add("Isi detail kunjungan teknisi.", "Fill in the technician visit details.");
    add("No. WhatsApp / telepon", "WhatsApp / phone number", ["WhatsApp / phone number"]);
    add("-- Pilih layanan --", "-- Choose a service --", ["-- Choose a service --"]);
    add("Alamat lengkap kunjungan", "Complete visit address", ["Complete visit address"]);
    add("Keluhan / catatan", "Complaint / notes", ["Complaint / notes"]);
    add("Contoh: Laptop mati total setelah terjatuh", "Example: The laptop is completely dead after being dropped");
    add("Kirim permintaan", "Send request", ["Send request"]);
    add("Siap membantu hari ini.", "Ready to help today.");
    add("Semua teknisi sedang bertugas. Permintaan Anda tetap bisa dikirim.", "All technicians are on duty. You can still send your request.");
    add("Aktivitas terbaru", "Recent activity", ["Recent activity"]);
    add("5 permintaan terakhir.", "Last 5 requests.", ["Last 5 requests."]);
    add("Belum ada aktivitas.", "No activity yet.", ["No activity yet."]);
    add("Belum ada teknisi", "No technician yet", ["No technician yet"]);
    add("Baru saja", "Just now", ["Just now"]);

    /* ------------------------------------------------------------------
       DETAIL PRODUK DAN ULASAN
     * ------------------------------------------------------------------ */
    add("Detail Produk", "Product Detail", ["Product Detail"]);
    add("populer", "popular", ["Popular"]);
    add("Lihat informasi produk, rating, dan ulasan pembeli sebelum membeli.", "View product information, ratings, and buyer reviews before purchasing.");
    add("Lihat keranjang", "View cart", ["View cart"]);
    add("Lihat ulasan", "View reviews", ["View reviews"]);
    add("Pengiriman cepat", "Fast shipping", ["Fast shipping"]);
    add("Dukungan 24/7", "Support 24/7", ["Support 24/7"]);
    add("Rating produk", "Product rating", ["Product rating"]);
    add("Rating dari pembeli yang sudah login.", "Ratings from logged-in buyers.");
    add("Beri rating", "Rate product", ["Rate product"]);
    add("Ubah rating kamu", "Change your rating", ["Change your rating"]);
    add("Perbarui rating", "Update rating", ["Update rating"]);
    add("Ulasan", "Reviews", ["Review"]);
    add("Kirim rating", "Submit rating", ["Submit rating"]);
    add("Ulasan pembeli", "Buyer reviews", ["Buyer reviews"]);
    add("Pengalaman nyata dari pelanggan Nexatech.", "Real experiences from Nexatech customers.");
    add("Ceritakan pengalaman kamu memakai produk ini", "Tell us about your experience with this product", ["Tell us about your experience with this product"]);
    add("Masuk untuk memberi rating", "Log in to rate", ["Log in to rate"]);
    add("Rating hanya bisa dikirim setelah kamu login.", "Ratings can only be submitted after you log in.");
    add("Belum ada ulasan", "No reviews yet", ["No reviews yet"]);
    add("Jadilah yang pertama memberi rating untuk produk ini.", "Be the first to rate this product.");
    add("Pengguna", "User", ["User"]);

    /* ------------------------------------------------------------------
       KERANJANG DAN CHECKOUT
     * ------------------------------------------------------------------ */
    add("Keranjang Anda", "Your Cart", ["Your Cart"]);
    add("Keranjang belanja", "Shopping cart", ["Shopping cart"]);
    add("Lengkapi pesanan produk dan layanan teknisi Anda sebelum melanjutkan ke checkout.", "Complete your product and technician service order before continuing to checkout.");
    add("Keranjang masih kosong", "Your cart is still empty", ["Your cart is still empty"]);
    add("Yuk, pilih produk atau layanan teknisi yang Anda butuhkan.", "Choose the product or technician service you need.");
    add("Mulai belanja", "Start shopping", ["Start shopping"]);
    add("Tambah layanan teknisi", "Add technician service", ["Add technician service"]);
    add("Layanan profesional langsung dari teknisi terverifikasi.", "Professional services directly from verified technicians.");
    add("Ringkasan pesanan", "Order summary", ["Order summary"]);
    add("Subtotal produk", "Product subtotal", ["Product subtotal"]);
    add("Subtotal layanan", "Service subtotal", ["Service subtotal"]);
    add("Biaya layanan", "Service fee", ["Service fee"]);
    add("Gratis", "Free", ["Free"]);
    add("Pembayaran aman, garansi resmi, dan jadwal teknisi bisa diubah.", "Secure payment, official warranty, and reschedulable technician visits.");
    add("Butuh bantuan?", "Need help?", ["Need help?"]);
    add("Senin-Jumat, 09.00-18.00 WIB", "Monday-Friday, 09.00-18.00 WIB", ["Monday-Friday, 09.00-18.00 WIB"]);
    add("Checkout Aman", "Secure Checkout", ["Secure Checkout"]);
    add("Selesaikan pesanan Anda", "Complete your order", ["Complete your order"]);
    add("Periksa ringkasan belanja, pilih metode pembayaran, lalu konfirmasi pesanan.", "Review your shopping summary, choose a payment method, then confirm the order.");
    add("Pembayaran aman", "Secure payment", ["Secure payment"]);
    add("Ringkasan belanja", "Shopping summary", ["Shopping summary"]);
    add("Produk dan layanan yang akan diproses.", "Products and services to be processed.");
    add("Metode pembayaran", "Payment method", ["Payment method"]);
    add("Kartu kredit", "Credit card", ["Credit card"]);
    add("Transfer bank", "Bank transfer", ["Bank transfer"]);
    add("E-wallet", "E-wallet", ["E-wallet"]);
    add("Bayar sekarang", "Pay now", ["Pay now"]);
    add("Pembayaran aman dengan enkripsi SSL dan garansi produk resmi.", "Secure payment with SSL encryption and official product warranty.");
    add("Pembayaran Berhasil", "Payment Successful", ["Payment Successful"]);
    add("Pembayaran berhasil!", "Payment successful!");
    add("Terima kasih sudah berbelanja di Nexatech. Pesanan Anda sedang kami proses.", "Thank you for shopping at Nexatech. Your order is being processed.");
    add("Nomor order", "Order number", ["Order number"]);
    add("Total dibayar", "Total paid", ["Total paid"]);
    add("Estimasi pengiriman 2-3 hari kerja. Teknisi akan menghubungi Anda untuk jadwal layanan.", "Estimated delivery 2-3 business days. A technician will contact you to schedule the service.");
    add("Cetak bukti pembayaran", "Print payment receipt", ["Print payment receipt"]);
    add("Aman", "Secure", ["Secure"]);
    add("item", "item", ["items"]);
    add("Qty", "Qty");

    /* ------------------------------------------------------------------
       AUTH DAN PROFIL
     * ------------------------------------------------------------------ */
    add("Akun Nexatech", "Nexatech Account", ["Nexatech Account"]);
    add("Gabung Nexatech", "Join Nexatech", ["Join Nexatech"]);
    add("Mulai perjalanan teknologi Anda bersama Nexatech.", "Start your technology journey with Nexatech.");
    add("Buat akun gratis untuk berbelanja, menyimpan riwayat pesanan, dan memesan teknisi kapan saja.", "Create a free account to shop, save order history, and book technicians anytime.");
    add("Promo dan penawaran spesial", "Special promos and offers", ["Special promos and offers"]);
    add("Riwayat pesanan tersimpan", "Saved order history", ["Saved order history"]);
    add("Bantuan pelanggan 24/7", "24/7 customer support", ["24/7 customer support"]);
    add("nama@email.com", "name@email.com");
    add("Satu akun untuk semua kebutuhan teknologi.", "One account for all your technology needs.");
    add("Belanja produk, pantau pesanan, dan pesan teknisi dengan pengalaman yang lebih cepat dan aman.", "Shop for products, track orders, and book technicians with a faster and safer experience.");
    add("Belanja produk teknologi pilihan", "Shop selected technology products", ["Shop selected technology products"]);
    add("Booking teknisi terverifikasi", "Book verified technicians", ["Book verified technicians"]);
    add("Pantau pesanan dalam satu tempat", "Track orders in one place", ["Track orders in one place"]);
    add("Nexatech 2.0 — teknologi untuk ide besarmu.", "Nexatech 2.0 — technology for your big ideas.", ["Nexatech 2.0 - teknologi untuk ide besarmu."]);
    add("Selamat datang kembali", "Welcome back", ["Welcome back"]);
    add("Masuk untuk melanjutkan belanja dan mengelola pesanan Anda.", "Sign in to continue shopping and manage your orders.");
    add("Masukkan password", "Enter password", ["Enter password"]);
    add("Belum punya akun?", "Do not have an account?", ["Don't have an account?"]);
    add("Daftar sekarang", "Register now", ["Register now"]);
    add("Buat akun baru", "Create a new account", ["Create a new account"]);
    add("Daftar gratis dan mulai gunakan semua layanan Nexatech.", "Register for free and start using all Nexatech services.");
    add("Nama kamu", "Your name", ["Your name"]);
    add("Minimal 8 karakter", "At least 8 characters", ["At least 8 characters"]);
    add("Sudah punya akun?", "Already have an account?", ["Already have an account?"]);
    add("Masuk di sini", "Sign in here", ["Sign in here"]);
    add("Akun Saya", "My Account", ["My Account"]);
    add("Kelola informasi akun agar pesanan dan layanan teknisi berjalan lancar.", "Manage your account information so orders and technician services run smoothly.");
    add("Akun aktif", "Active account", ["Active account"]);
    add("Belum diisi", "Not filled yet", ["Not filled yet"]);
    add("Keranjang saya", "My cart", ["My cart"]);
    add("Keluar dari akun", "Log out of account", ["Log out of account"]);
    add("Informasi akun", "Account information", ["Account information"]);
    add("Perbarui data diri dan keamanan akun Anda.", "Update your personal data and account security.");
    add("Privat", "Private", ["Private"]);
    add("Password baru", "New password", ["New password"]);
    add("Kosongkan kalau tidak ingin mengganti password", "Leave blank if you do not want to change the password", ["Leave blank if you don't want to change the password"]);
    add("Gunakan minimal 8 karakter agar akun lebih aman.", "Use at least 8 characters to keep your account safer.");
    add("Simpan perubahan", "Save changes", ["Save changes"]);
    add("Data akun Anda hanya digunakan untuk memproses pesanan dan layanan teknisi.", "Your account data is only used to process orders and technician services.");

    /* ------------------------------------------------------------------
       ADMIN
     * ------------------------------------------------------------------ */
    add("Panel Admin", "Admin Panel", ["Admin Panel"]);
    add("Panel admin", "Admin panel", ["Admin panel"]);
    add("Menu utama", "Main menu", ["Main menu"]);
    add("Navigasi admin", "Admin navigation", ["Admin navigation"]);
    add("Administrator", "Administrator");
    add("Ringkasan toko", "Store overview", ["Store overview"]);
    add("Katalog produk", "Product catalog", ["Product catalog"]);
    add("Struktur katalog", "Catalog structure", ["Catalog structure"]);
    add("Operasional penjualan", "Sales operations", ["Sales operations"]);
    add("Layanan teknisi", "Technician services", ["Technician services"]);
    add("Basis pelanggan", "Customer base", ["Customer base"]);
    add("Akun admin", "Admin account", ["Admin account"]);
    add("Dashboard Admin", "Admin Dashboard", ["Admin Dashboard"]);
    add("Kelola produk, pesanan, teknisi, dan operasional bisnis dari satu tempat.", "Manage products, orders, technicians, and business operations from one place.");
    add("Tambah produk", "Add product", ["Add product"]);
    add("Edit produk", "Edit product", ["Edit product"]);
    add("Edit kategori", "Edit category", ["Edit category"]);
    add("Simpan perubahan", "Save changes", ["Save changes"]);
    add("Buat listing baru", "Create a new listing", ["Create a new listing"]);
    add("Jadwalkan teknisi", "Schedule a technician", ["Schedule a technician"]);
    add("Atur kunjungan", "Manage visits", ["Manage visits"]);
    add("Kelola pelanggan", "Manage customers", ["Manage customers"]);
    add("Lihat profil & aktivitas", "View profile & activity", ["View profile & activity"]);
    add("Ringkasan", "Summary", ["Summary"]);
    add("Hari ini", "Today", ["Today"]);
    add("Pesanan baru", "New orders", ["New orders"]);
    add("Teknisi tersedia", "Available technicians", ["Available technicians"]);
    add("Produk terlaris", "Best-selling product", ["Best-selling product"]);
    add("Total produk", "Total products", ["Total products"]);
    add("Pesanan terbaru", "Latest orders", ["Latest orders"]);
    add("Layanan populer", "Popular services", ["Popular services"]);
    add("Belum ada pesanan.", "No orders yet.", ["No orders yet."]);
    add("Belum ada kategori.", "No categories yet.", ["No categories yet."]);
    add("Belum ada produk.", "No products yet.", ["No products yet."]);
    add("Belum ada booking.", "No bookings yet.", ["No bookings yet."]);
    add("Belum ada pelanggan.", "No customers yet.", ["No customers yet."]);
    add("Belum pernah belanja.", "Has never shopped.", ["Has never shopped."]);
    add("Daftar booking", "Booking list", ["Booking list"]);
    add("Daftar kategori", "Category list", ["Category list"]);
    add("Daftar pelanggan", "Customer list", ["Customer list"]);
    add("Daftar pesanan", "Order list", ["Order list"]);
    add("Daftar produk", "Product list", ["Product list"]);
    add("Riwayat pesanan", "Order history", ["Order history"]);
    add("Buat booking", "Create booking", ["Create booking"]);
    add("Simpan booking", "Save booking", ["Save booking"]);
    add("Simpan", "Save", ["Save"]);
    add("Batal", "Cancel", ["Cancel"]);
    add("Edit", "Edit", ["Edit"]);
    add("Hapus", "Delete", ["Delete"]);
    add("Tambah", "Add", ["Add"]);
    add("Kembali", "Back", ["Back"]);
    add("Nama kategori", "Category name", ["Category name"]);
    add("Nama produk", "Product name", ["Product name"]);
    add("Nama lengkap", "Full name", ["Full name"]);
    add("Password baru (kosongkan kalau tidak ganti)", "New password (leave blank to keep current)", ["New password (leave blank if not changing)"]);
    add("Keluhan", "Complaint", ["Complaint"]);
    add("URL gambar", "Image URL", ["Image URL"]);
    add("Icon (nama lucide)", "Icon (lucide name)", ["Icon (lucide name)"]);
    add("Pilih", "Choose", ["Choose"]);
    add("Pilih kategori", "Choose a category", ["Choose a category"]);
    add("Menunggu", "Pending", ["Pending"]);
    add("Dikonfirmasi", "Confirmed", ["Confirmed"]);
    add("Diproses", "Processing", ["Processing"]);
    add("Dikirim", "Shipped", ["Shipped"]);
    add("Selesai", "Completed", ["Completed"]);
    add("Dibatalkan", "Cancelled", ["Cancelled"]);
    add("Lunas", "Paid", ["Paid"]);
    add("Gagal", "Failed", ["Failed"]);
    add("Tersedia", "Available", ["Available"]);
    add("Aktif", "Active", ["Active"]);
    add("Nonaktif", "Inactive", ["Inactive"]);
    add("Terdaftar", "Registered", ["Registered"]);
    add("Bulan ini", "This month", ["This month"]);
    add("Omzet bulan ini", "Revenue this month", ["Revenue this month"]);
    add("Siap menerima kunjungan", "Ready to accept visits", ["Ready to accept visits"]);
    add("Pengguna dengan role user", "Users with the user role", ["Users with the user role"]);
    add("Total pesanan", "Total orders", ["Total orders"]);
    add("Pendapatan", "Revenue", ["Revenue"]);
    add("Teknisi aktif", "Active technicians", ["Active technicians"]);
    add("Kelola toko dalam satu dashboard.", "Manage the store in one dashboard.", ["Manage the store in one dashboard."]);
    add("Pantau pesanan, produk, booking teknisi, dan pelanggan Nexatech dengan tampilan yang rapi dan terintegrasi.", "Monitor orders, products, technician bookings, and Nexatech customers in a tidy and integrated view.");
    add("Produk & kategori terpusat", "Centralized products & categories", ["Centralized products & categories"]);
    add("Pesanan dan booking real-time", "Real-time orders and bookings", ["Real-time orders and bookings"]);
    add("Data pelanggan lebih terukur", "More measurable customer data", ["More measurable customer data"]);
    add("Email admin", "Admin email", ["Admin email"]);
    add("Login Admin", "Admin Login", ["Admin Login"]);
    add("Masuk dengan akun admin untuk melanjutkan ke dashboard.", "Sign in with an admin account to continue to the dashboard.");
    add("Masuk dashboard", "Sign in to dashboard", ["Sign in to dashboard"]);
    add("Bukan admin?", "Not an admin?", ["Not an admin?"]);
    add("Login user", "User login", ["User login"]);
    add("Tambah kategori", "Add category", ["Add category"]);
    add("Tambah, ubah, dan hapus produk. Kategori diambil dari halaman kategori.", "Add, edit, and delete products. Categories are taken from the category page.");
    add("Tambah kategori baru, kategori langsung bisa dipilih di form produk.", "Add a new category; it can be selected immediately in the product form.");
    add("Lihat status bayar dan status paket, ubah kalau ada yang lunas atau dikirim.", "View payment and package status, and update when paid or shipped.");
    add("Buat booking baru dan atur teknisi + statusnya.", "Create a new booking and manage the technician and status.");
    add("Lihat profil dan aktivitas belanja tiap pelanggan.", "View each customer's profile and shopping activity.");
    add("Ganti nama, email, telepon, dan password akun admin.", "Change the admin account name, email, phone, and password.");
    add("Profil Pelanggan", "Customer Profile", ["Customer Profile"]);
    add("Tanggal lahir", "Date of birth", ["Date of birth"]);
    add("Belum ada alamat", "No address yet", ["No address yet"]);
    add("-- Pilih kategori --", "-- Choose a category --", ["-- Choose a category --"]);
    add("-- Pilih --", "-- Choose --", ["-- Choose --"]);

    /* ------------------------------------------------------------------
       POLA TEKS DINAMIS
     * ------------------------------------------------------------------ */
    pattern(/^1 kategori$/i, "1 kategori", "1 category");
    pattern(/^1 category$/i, "1 kategori", "1 category");
    pattern(/^(\d+)\s+kategori$/i, "$1 kategori", "$1 categories");
    pattern(/^(\d+)\s+categories$/i, "$1 kategori", "$1 categories");
    pattern(/^1 produk$/i, "1 produk", "1 product");
    pattern(/^1 product$/i, "1 produk", "1 product");
    pattern(/^(\d+)\s+produk$/i, "$1 produk", "$1 products");
    pattern(/^(\d+)\s+products$/i, "$1 produk", "$1 products");
    pattern(/^1 brand tersedia$/i, "1 brand tersedia", "1 brand available");
    pattern(/^1 brand available$/i, "1 brand tersedia", "1 brand available");
    pattern(/^(\d+)\s+brand tersedia$/i, "$1 brand tersedia", "$1 brands available");
    pattern(/^(\d+)\s+brands available$/i, "$1 brand tersedia", "$1 brands available");
    pattern(/^1 brand$/i, "1 brand", "1 brand");
    pattern(/^(\d+)\s+brand$/i, "$1 brand", "$1 brands");
    pattern(/^(\d+)\s+brands$/i, "$1 brand", "$1 brands");
    pattern(/^1 produk ditemukan$/i, "1 produk ditemukan", "1 product found");
    pattern(/^1 product found$/i, "1 produk ditemukan", "1 product found");
    pattern(/^(\d+)\s+produk ditemukan$/i, "$1 produk ditemukan", "$1 products found");
    pattern(/^(\d+)\s+products found$/i, "$1 produk ditemukan", "$1 products found");
    pattern(/^(\d+)\s+hasil untuk "(.+)"$/i, "$1 hasil untuk \"$2\"", "$1 results for \"$2\"");
    pattern(/^(\d+)\s+results for "(.+)"$/i, "$1 hasil untuk \"$2\"", "$1 results for \"$2\"");
    pattern(/^\((\d+) Reviews\)$/i, "($1 Ulasan)", "($1 Reviews)");
    pattern(/^\((\d+) Ulasan\)$/i, "($1 Ulasan)", "($1 Reviews)");
    pattern(/^1 ulasan$/i, "1 ulasan", "1 review");
    pattern(/^1 review$/i, "1 ulasan", "1 review");
    pattern(/^(\d+) ulasan$/i, "$1 ulasan", "$1 reviews");
    pattern(/^(\d+) reviews$/i, "$1 ulasan", "$1 reviews");
    pattern(/^(.+) \(mulai (Rp .+)\)$/i, "$1 (mulai $2)", "$1 (starting at $2)");
    pattern(/^1 menit lalu$/i, "1 menit lalu", "1 minute ago");
    pattern(/^(\d+) menit lalu$/i, "$1 menit lalu", "$1 minutes ago");
    pattern(/^1 jam lalu$/i, "1 jam lalu", "1 hour ago");
    pattern(/^(\d+) jam lalu$/i, "$1 jam lalu", "$1 hours ago");
    pattern(/^1 hari lalu$/i, "1 hari lalu", "1 day ago");
    pattern(/^(\d+) hari lalu$/i, "$1 hari lalu", "$1 days ago");
    pattern(/^(\d+)\+\s+Years Experience$/i, "$1+ Tahun Pengalaman", "$1+ Years Experience");
    pattern(/^(\d+)\+\s+Tahun Pengalaman$/i, "$1+ Tahun Pengalaman", "$1+ Years Experience");
    pattern(/^(\d+)\+\s+Years$/i, "$1+ Tahun", "$1+ Years");
    pattern(/^(\d+)\+\s+Tahun$/i, "$1+ Tahun", "$1+ Years");
    pattern(/^Jelajahi (.+)$/i, "Jelajahi $1", "Explore $1");
    pattern(/^Explore (.+)$/i, "Jelajahi $1", "Explore $1");
    pattern(/^Produk (.+)$/i, "Produk $1", "$1 products");
    pattern(/^Semua produk (.+)$/i, "Semua produk $1", "All $1 products");
    pattern(/^All (.+) products$/i, "Semua produk $1", "All $1 products");
    pattern(/^Hapus (.+)$/i, "Hapus $1", "Delete $1");
    pattern(/^Delete (.+)$/i, "Hapus $1", "Delete $1");
    pattern(/^Pilih (.+)$/i, "Pilih $1", "Choose $1");
    pattern(/^Choose (.+)$/i, "Pilih $1", "Choose $1");
    pattern(/^Daftar produk \((\d+)\)$/i, "Daftar produk ($1)", "Product list ($1)");
    pattern(/^Daftar kategori \((\d+)\)$/i, "Daftar kategori ($1)", "Category list ($1)");
    pattern(/^Daftar pesanan \((\d+)\)$/i, "Daftar pesanan ($1)", "Order list ($1)");
    pattern(/^Daftar booking \((\d+)\)$/i, "Daftar booking ($1)", "Booking list ($1)");
    pattern(/^Daftar pelanggan \((\d+)\)$/i, "Daftar pelanggan ($1)", "Customer list ($1)");
    pattern(/^Riwayat pesanan \((\d+)\)$/i, "Riwayat pesanan ($1)", "Order history ($1)");
    pattern(/^(\d+)\s+pesanan baru hari ini$/i, "$1 pesanan baru hari ini", "$1 new orders today");
    pattern(/^dari (\d+)$/i, "dari $1", "out of $1");
    pattern(/^Sejak (.+)$/i, "Sejak $1", "Since $1");
    pattern(/^Mulai (Rp .+)$/i, "Mulai $1", "Starting at $1");
    pattern(/^Detail profil dan riwayat belanja (.+)\.$/i, "Detail profil dan riwayat belanja $1.", "Profile details and shopping history for $1.");
    pattern(/^Menggunakan (\d+)% budget$/i, "Menggunakan $1% budget", "Uses $1% of budget");
    pattern(/^Sisa (Rp .+)$/i, "Sisa $1", "Remaining $1");
    pattern(/^1 rekomendasi$/i, "1 rekomendasi", "1 recommendation");
    pattern(/^(\d+) rekomendasi$/i, "$1 rekomendasi", "$1 recommendations");
    pattern(/^Semua harga di bawah (Rp .+)$/i, "Semua harga di bawah $1", "All prices below $1");
    pattern(/^seharga (Rp .+)\.$/i, "seharga $1.", "for $1.");
    pattern(/^Budget Anda kurang (Rp .+)\.$/i, "Budget Anda kurang $1.", "Your budget is short by $1.");
    pattern(/^([\d.,]+) \((\d+) ulasan\)$/i, "$1 ($2 Ulasan)", "$1 ($2 reviews)");
    pattern(/^Keranjang \((\d+)\)$/i, "Keranjang ($1)", "Cart ($1)");
    pattern(/^1 item$/i, "1 item", "1 item");
    pattern(/^(\d+)\s+item$/i, "$1 item", "$1 items");
    pattern(/^1 layanan$/i, "1 layanan", "1 service");
    pattern(/^(\d+)\s+layanan$/i, "$1 layanan", "$1 services");
    pattern(/^(\d+)\s+pesanan yang memerlukan tindak lanjut$/i, "$1 pesanan yang memerlukan tindak lanjut", "$1 orders require follow-up");
    pattern(/^(\d+)\s+produk\s+•\s+(\d+)\s+layanan$/i, "$1 produk • $2 layanan", "$1 products • $2 services");
    pattern(/^Order #(.+)$/i, "Order #$1", "Order #$1");
    pattern(/^Qty (\d+)$/i, "Qty $1", "Qty $1");
    pattern(/^© (\d{4}) Nexatech\. Teknologi untuk ide besarmu\.$/i, "© $1 Nexatech. Teknologi untuk ide besarmu.", "© $1 Nexatech. Technology for your big ideas.");
    pattern(/^© (\d{4}) Nexatech\. Technology for your big ideas\.$/i, "© $1 Nexatech. Teknologi untuk ide besarmu.", "© $1 Nexatech. Technology for your big ideas.");

    /* ------------------------------------------------------------------
       MESIN PENERJEMAH
     * ------------------------------------------------------------------ */
    function normalize(value) {
        return String(value == null ? "" : value).replace(/\s+/g, " ").trim();
    }

    function interpolate(template, variables) {
        return String(template).replace(/\{(\w+)\}/g, function (match, name) {
            return Object.prototype.hasOwnProperty.call(variables, name) ? variables[name] : match;
        });
    }

    function t(name, variables) {
        var entry = KEYS[name];
        if (!entry) return name;
        return interpolate(entry[currentLang] || entry.id, variables || {});
    }

    function translatePhrase(value) {
        var normalized = normalize(value);
        if (!normalized) return value;

        var entry = PHRASES[normalized];
        if (entry) {
            return entry[currentLang] || entry.id;
        }

        for (var i = 0; i < PATTERNS.length; i++) {
            var item = PATTERNS[i];
            if (item.re.test(normalized)) {
                return normalized.replace(item.re, item[currentLang] || item.id);
            }
        }

        return value;
    }

    function translateText(value) {
        var match = /^(\s*)([\s\S]*?)(\s*)$/.exec(String(value));
        if (!match) return value;
        var core = match[2];
        if (!core || !core.trim()) return value;
        return match[1] + translatePhrase(core) + match[3];
    }

    // Menyimpan teks asli per text node/atribut agar bahasa bisa bolak-balik.
    var textState = new WeakMap();
    var attrState = new WeakMap();

    function shouldSkip(element) {
        if (!element || !element.closest) return true;
        return !!element.closest("script, style, noscript, textarea, code, pre, svg, [data-i18n-skip], .notranslate");
    }

    /*
     * Atribut pada textarea tetap boleh diterjemahkan (placeholder, title,
     * aria-label), tetapi isi textarea tidak boleh disentuh karena merupakan
     * data yang sedang diketik pengguna.
     */
    function shouldSkipAttribute(element) {
        if (!element || !element.closest) return true;
        return !!element.closest("script, style, noscript, code, pre, svg, [data-i18n-skip], .notranslate");
    }

    function translateTextNode(node) {
        var parent = node.parentElement;
        if (!parent || shouldSkip(parent)) return;

        var current = node.nodeValue;
        if (!current || !current.trim()) return;

        var state = textState.get(node);
        if (!state || state.applied !== current) {
            // Teks berubah dari luar mesin i18n (mis. hasil render JavaScript).
            state = { original: current, applied: current };
            textState.set(node, state);
        }

        var next = translateText(state.original);
        if (next !== current) {
            node.nodeValue = next;
            state.applied = next;
        }
    }

    function translateAttribute(element, attribute) {
        if (shouldSkipAttribute(element)) return;
        var current = element.getAttribute(attribute);
        if (!current || !current.trim()) return;

        var map = attrState.get(element);
        if (!map) {
            map = Object.create(null);
            attrState.set(element, map);
        }

        var state = map[attribute];
        if (!state || state.applied !== current) {
            state = { original: current, applied: current };
            map[attribute] = state;
        }

        var next = translateText(state.original);
        if (next !== current) {
            element.setAttribute(attribute, next);
            state.applied = next;
        }
    }

    /*
     * Judul tab ikut diterjemahkan per segmen. Pemisah " - " dan " | " dipertahankan
     * agar format judul halaman admin/publik tidak berubah.
     */
    function translateTitle(value) {
        /*
         * Selalu pecah per segmen. Menerjemahkan seluruh judul lebih dulu bisa
         * memicu pola seperti "^Produk (.+)$" dan merusak judul admin
         * "Produk - Nexatech Admin".
         */
        return String(value).split(/(\s+[|–—-]\s+)/).map(function (part) {
            if (/^\s+[|–—-]\s+$/.test(part)) return part;
            return translateText(part);
        }).join("");
    }

    function syncDocumentTitle() {
        // Judul server sudah berbahasa Indonesia; hanya perlu diterjemahkan saat EN.
        document.title = currentLang === "en" ? translateTitle(originalTitle) : originalTitle;
    }

    function apply(root) {
        var scope = root || document.body;
        if (!scope) return;

        var walker = document.createTreeWalker(scope, NodeFilter.SHOW_TEXT, {
            acceptNode: function (node) {
                return shouldSkip(node.parentElement)
                    ? NodeFilter.FILTER_REJECT
                    : NodeFilter.FILTER_ACCEPT;
            }
        });

        var nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);
        nodes.forEach(translateTextNode);

        var elements = [];
        if (scope.nodeType === 1) {
            elements.push(scope);
            elements = elements.concat(Array.prototype.slice.call(scope.querySelectorAll("*")));
        }
        elements.forEach(function (element) {
            ["placeholder", "title", "aria-label", "alt"].forEach(function (attribute) {
                translateAttribute(element, attribute);
            });
        });

        syncDocumentTitle();
    }

    function syncLanguageUI() {
        document.documentElement.lang = currentLang;
        document.documentElement.setAttribute("data-lang", currentLang);

        document.querySelectorAll("[data-lang-label]").forEach(function (element) {
            element.textContent = currentLang.toUpperCase();
        });

        document.querySelectorAll("[data-lang-toggle]").forEach(function (button) {
            var label = t("toggle_language");
            button.setAttribute("aria-label", label);
            button.title = label;
        });
    }

    function bindLanguageButtons() {
        document.querySelectorAll("[data-lang-toggle]").forEach(function (button) {
            if (button.getAttribute("data-i18n-bound") === "1") return;
            button.setAttribute("data-i18n-bound", "1");
            button.addEventListener("click", function () {
                setLanguage(currentLang === "id" ? "en" : "id");
            });
        });
    }

    function setLanguage(language) {
        if (language !== "id" && language !== "en") return;
        currentLang = language;

        try {
            localStorage.setItem(STORAGE_KEY, currentLang);
        } catch (error) {
            // Preferensi tetap berlaku untuk sesi ini meskipun localStorage diblokir.
        }

        apply();
        syncLanguageUI();
        document.dispatchEvent(new CustomEvent("techhub:languagechange", {
            detail: { lang: currentLang }
        }));
    }

    function init() {
        bindLanguageButtons();
        apply();
        syncLanguageUI();
    }

    // API publik dipakai theme.js, admin.js, dan technician.js.
    window.techI18n = {
        get lang() {
            return currentLang;
        },
        setLang: setLanguage,
        toggle: function () {
            setLanguage(currentLang === "id" ? "en" : "id");
        },
        t: t,
        translate: translateText,
        apply: apply
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
