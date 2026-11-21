<?php
/**
 * validations.php — pusat aturan validasi input form.
 *
 * Kenapa validasi dipisah dari action?
 * - Aturan dan pesan error tidak tersebar di banyak handler.
 * - Action tetap fokus pada alur simpan, redirect, dan flash message.
 * - Validasi di sini hanya memeriksa bentuk input. Pengecekan yang membutuhkan
 *   database (email unik, layanan/teknisi tersedia) tetap dilakukan di action
 *   atau repository karena membutuhkan koneksi.
 *
 * Setiap fungsi validasi mengembalikan string pesan error bila gagal, atau
 * null bila input lolos. Pola ini membuat action cukup menulis satu pengecekan.
 */

/** Ambil teks dari array input dan rapikan spasi di awal/akhir. */
function input_text($data, $key) {
    return trim((string)($data[$key] ?? ""));
}

/** Ambil angka dari array input; nilai yang bukan angka menjadi 0. */
function input_number($data, $key) {
    return (int)($data[$key] ?? 0);
}

/** Email valid bila formatnya lolos filter bawaan PHP. */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/** Nomor telepon boleh berisi angka, spasi, +, -, dan tanda kurung. */
function validate_phone($telepon) {
    return preg_match('/^[0-9+()\-\s]{8,20}$/', $telepon) === 1;
}

/** Tanggal harus memakai format Y-m-d dan benar-benar ada di kalender. */
function validate_date($tanggal) {
    $date = DateTime::createFromFormat("!Y-m-d", $tanggal);
    return $date !== false && $date->format("Y-m-d") === $tanggal;
}

/** Jam menerima format H:i atau H:i:s. */
function validate_time($jam) {
    return preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $jam) === 1;
}

/** ID harus bilangan bulat positif. */
function validate_id($id) {
    return (int)$id > 0;
}

/** Nilai harus ada di daftar pilihan yang diizinkan. */
function validate_choice($nilai, $daftar) {
    return in_array($nilai, $daftar, true);
}

/** Daftar status yang dipakai form booking admin. */
function get_booking_statuses() {
    return ["Menunggu", "Dikonfirmasi", "Selesai", "Dibatalkan"];
}

/** Daftar status pengiriman/pesanan admin. */
function get_order_statuses() {
    return ["Pending", "Diproses", "Dikirim", "Selesai", "Dibatalkan"];
}

/** Daftar status pembayaran admin. */
function get_payment_statuses() {
    return ["Pending", "Lunas", "Gagal"];
}

// ---------- Validasi per form ----------

/** Validasi form login admin maupun pelanggan. */
function validate_login($input) {
    $email = input_text($input, "email");
    $password = (string)($input["password"] ?? "");

    if ($email === "" || $password === "") {
        return "Email dan password wajib diisi.";
    }
    if (!validate_email($email)) {
        return "Format email tidak valid.";
    }
    return null;
}

/** Validasi form registrasi akun pelanggan. */
function validate_register($input) {
    $nama = input_text($input, "nama");
    $email = input_text($input, "email");
    $password = (string)($input["password"] ?? "");

    if ($nama === "" || $email === "" || $password === "") {
        return "Semua kolom wajib diisi.";
    }
    if (!validate_email($email)) {
        return "Format email tidak valid.";
    }
    if (strlen($password) < 6) {
        return "Password minimal 6 karakter.";
    }
    return null;
}

/** Validasi form profil admin/pelanggan. Password opsional saat tidak diganti. */
function validate_profile($input) {
    $nama = input_text($input, "nama");
    $email = input_text($input, "email");
    $telepon = input_text($input, "telepon");
    $password = (string)($input["password"] ?? "");

    if ($nama === "" || $email === "") {
        return "Nama dan email wajib diisi.";
    }
    if (!validate_email($email)) {
        return "Format email tidak valid.";
    }
    if ($telepon !== "" && !validate_phone($telepon)) {
        return "Format nomor telepon tidak valid.";
    }
    if ($password !== "" && strlen($password) < 6) {
        return "Password minimal 6 karakter.";
    }
    return null;
}

/** Validasi form tambah/edit kategori. */
function validate_category($input) {
    $nama = input_text($input, "category_name");

    if ($nama === "") {
        return "Nama kategori wajib diisi.";
    }
    return null;
}

/** Validasi form tambah/edit produk. */
function validate_product($input) {
    $nama = input_text($input, "product_name");
    $kategori = input_number($input, "category_id");
    $harga = input_number($input, "price");
    $stok = input_number($input, "stock");

    if ($nama === "" || $kategori <= 0) {
        return "Nama dan kategori wajib diisi.";
    }
    if ($harga < 0) {
        return "Harga tidak boleh negatif.";
    }
    if ($stok < 0) {
        return "Stok tidak boleh negatif.";
    }
    return null;
}

/**
 * Validasi form booking (publik, user login, dan admin).
 * Pesan kolom wajib bisa diganti agar teks lama tiap halaman tetap sama.
 */
function validate_booking($input, $pesan_wajib = "Semua kolom bertanda wajib harus diisi.") {
    $nama = input_text($input, "nama");
    $telepon = input_text($input, "telepon");
    $service_id = input_number($input, "service_id");
    $alamat = input_text($input, "alamat");
    $tanggal = input_text($input, "tanggal");
    $jam = input_text($input, "jam");

    if ($nama === "" || $telepon === "" || $service_id <= 0 || $alamat === "" || $tanggal === "" || $jam === "") {
        return $pesan_wajib;
    }
    if (!validate_date($tanggal)) {
        return "Format tanggal kunjungan tidak valid.";
    }
    if (!validate_time($jam)) {
        return "Format jam kunjungan tidak valid.";
    }
    if ($tanggal < date("Y-m-d")) {
        return "Tanggal kunjungan tidak boleh di masa lalu.";
    }
    if (!validate_phone($telepon)) {
        return "Format nomor telepon tidak valid.";
    }
    return null;
}

/** Validasi nilai bintang pada form rating produk. */
function validate_rating($input) {
    $rating = input_number($input, "rating");

    if ($rating < 1 || $rating > 5) {
        return "Pilih rating bintang 1 sampai 5.";
    }
    return null;
}

/** Validasi form perubahan teknisi/status booking admin. */
function validate_booking_update($input) {
    $id = input_number($input, "booking_id");
    $technician_id = input_number($input, "technician_id");
    $status = input_text($input, "status");

    if (!validate_id($id)) {
        return "Data booking tidak valid.";
    }
    if ($technician_id < 0) {
        return "Data teknisi tidak valid.";
    }
    if ($status !== "" && !validate_choice($status, get_booking_statuses())) {
        return "Status booking tidak valid.";
    }
    return null;
}

/** Validasi data pembayaran yang dikirim dari halaman checkout. */
function validate_payment($input) {
    $no_order = input_text($input, "no_order");
    $metode = input_text($input, "metode");
    $total = input_number($input, "total");

    if ($no_order === "") {
        return "Nomor pesanan tidak valid.";
    }
    if ($total < 0) {
        return "Total pembayaran tidak valid.";
    }
    if (!validate_choice($metode, ["Kartu kredit", "Transfer bank", "E-wallet"])) {
        return "Metode pembayaran tidak valid.";
    }
    return null;
}

/** Validasi form perubahan status pesanan dan pembayaran admin. */
function validate_order_status($input) {
    $id = input_number($input, "order_id");
    $status = input_text($input, "status");
    $bayar = input_text($input, "payment_status");

    if (!validate_id($id)) {
        return "Data pesanan tidak valid.";
    }
    if ($status !== "" && !validate_choice($status, get_order_statuses())) {
        return "Status pesanan tidak valid.";
    }
    if ($bayar !== "" && !validate_choice($bayar, get_payment_statuses())) {
        return "Status pembayaran tidak valid.";
    }
    return null;
}
