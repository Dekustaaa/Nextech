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
function input_teks($data, $key) {
    return trim((string)($data[$key] ?? ""));
}

/** Ambil angka dari array input; nilai yang bukan angka menjadi 0. */
function input_angka($data, $key) {
    return (int)($data[$key] ?? 0);
}

/** Email valid bila formatnya lolos filter bawaan PHP. */
function validasi_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/** Nomor telepon boleh berisi angka, spasi, +, -, dan tanda kurung. */
function validasi_telepon($telepon) {
    return preg_match('/^[0-9+()\-\s]{8,20}$/', $telepon) === 1;
}

/** Tanggal harus memakai format Y-m-d dan benar-benar ada di kalender. */
function validasi_tanggal($tanggal) {
    $date = DateTime::createFromFormat("!Y-m-d", $tanggal);
    return $date !== false && $date->format("Y-m-d") === $tanggal;
}

/** Jam menerima format H:i atau H:i:s. */
function validasi_jam($jam) {
    return preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $jam) === 1;
}

/** ID harus bilangan bulat positif. */
function validasi_id($id) {
    return (int)$id > 0;
}

/** Nilai harus ada di daftar pilihan yang diizinkan. */
function validasi_pilihan($nilai, $daftar) {
    return in_array($nilai, $daftar, true);
}

/** Daftar status yang dipakai form booking admin. */
function daftar_status_booking() {
    return ["Menunggu", "Dikonfirmasi", "Selesai", "Dibatalkan"];
}

/** Daftar status pengiriman/pesanan admin. */
function daftar_status_pesanan() {
    return ["Pending", "Diproses", "Dikirim", "Selesai", "Dibatalkan"];
}

/** Daftar status pembayaran admin. */
function daftar_status_pembayaran() {
    return ["Pending", "Lunas", "Gagal"];
}

// ---------- Validasi per form ----------

/** Validasi form login admin maupun pelanggan. */
function validasi_login($input) {
    $email = input_teks($input, "email");
    $password = (string)($input["password"] ?? "");

    if ($email === "" || $password === "") {
        return "Email dan password wajib diisi.";
    }
    if (!validasi_email($email)) {
        return "Format email tidak valid.";
    }
    return null;
}

/** Validasi form registrasi akun pelanggan. */
function validasi_register($input) {
    $nama = input_teks($input, "nama");
    $email = input_teks($input, "email");
    $password = (string)($input["password"] ?? "");

    if ($nama === "" || $email === "" || $password === "") {
        return "Semua kolom wajib diisi.";
    }
    if (!validasi_email($email)) {
        return "Format email tidak valid.";
    }
    if (strlen($password) < 6) {
        return "Password minimal 6 karakter.";
    }
    return null;
}

/** Validasi form profil admin/pelanggan. Password opsional saat tidak diganti. */
function validasi_profil($input) {
    $nama = input_teks($input, "nama");
    $email = input_teks($input, "email");
    $telepon = input_teks($input, "telepon");
    $password = (string)($input["password"] ?? "");

    if ($nama === "" || $email === "") {
        return "Nama dan email wajib diisi.";
    }
    if (!validasi_email($email)) {
        return "Format email tidak valid.";
    }
    if ($telepon !== "" && !validasi_telepon($telepon)) {
        return "Format nomor telepon tidak valid.";
    }
    if ($password !== "" && strlen($password) < 6) {
        return "Password minimal 6 karakter.";
    }
    return null;
}

/** Validasi form tambah/edit kategori. */
function validasi_kategori($input) {
    $nama = input_teks($input, "category_name");

    if ($nama === "") {
        return "Nama kategori wajib diisi.";
    }
    return null;
}

/** Validasi form tambah/edit produk. */
function validasi_produk($input) {
    $nama = input_teks($input, "product_name");
    $kategori = input_angka($input, "category_id");
    $harga = input_angka($input, "price");
    $stok = input_angka($input, "stock");

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
function validasi_booking($input, $pesan_wajib = "Semua kolom bertanda wajib harus diisi.") {
    $nama = input_teks($input, "nama");
    $telepon = input_teks($input, "telepon");
    $service_id = input_angka($input, "service_id");
    $alamat = input_teks($input, "alamat");
    $tanggal = input_teks($input, "tanggal");
    $jam = input_teks($input, "jam");

    if ($nama === "" || $telepon === "" || $service_id <= 0 || $alamat === "" || $tanggal === "" || $jam === "") {
        return $pesan_wajib;
    }
    if (!validasi_tanggal($tanggal)) {
        return "Format tanggal kunjungan tidak valid.";
    }
    if (!validasi_jam($jam)) {
        return "Format jam kunjungan tidak valid.";
    }
    if ($tanggal < date("Y-m-d")) {
        return "Tanggal kunjungan tidak boleh di masa lalu.";
    }
    if (!validasi_telepon($telepon)) {
        return "Format nomor telepon tidak valid.";
    }
    return null;
}

/** Validasi nilai bintang pada form rating produk. */
function validasi_rating($input) {
    $rating = input_angka($input, "rating");

    if ($rating < 1 || $rating > 5) {
        return "Pilih rating bintang 1 sampai 5.";
    }
    return null;
}

/** Validasi form perubahan teknisi/status booking admin. */
function validasi_update_booking($input) {
    $id = input_angka($input, "booking_id");
    $technician_id = input_angka($input, "technician_id");
    $status = input_teks($input, "status");

    if (!validasi_id($id)) {
        return "Data booking tidak valid.";
    }
    if ($technician_id < 0) {
        return "Data teknisi tidak valid.";
    }
    if ($status !== "" && !validasi_pilihan($status, daftar_status_booking())) {
        return "Status booking tidak valid.";
    }
    return null;
}

/** Validasi data pembayaran yang dikirim dari halaman checkout. */
function validasi_payment($input) {
    $no_order = input_teks($input, "no_order");
    $metode = input_teks($input, "metode");
    $total = input_angka($input, "total");

    if ($no_order === "") {
        return "Nomor pesanan tidak valid.";
    }
    if ($total < 0) {
        return "Total pembayaran tidak valid.";
    }
    if (!validasi_pilihan($metode, ["Kartu kredit", "Transfer bank", "E-wallet"])) {
        return "Metode pembayaran tidak valid.";
    }
    return null;
}

/** Validasi form perubahan status pesanan dan pembayaran admin. */
function validasi_order_status($input) {
    $id = input_angka($input, "order_id");
    $status = input_teks($input, "status");
    $bayar = input_teks($input, "payment_status");

    if (!validasi_id($id)) {
        return "Data pesanan tidak valid.";
    }
    if ($status !== "" && !validasi_pilihan($status, daftar_status_pesanan())) {
        return "Status pesanan tidak valid.";
    }
    if ($bayar !== "" && !validasi_pilihan($bayar, daftar_status_pembayaran())) {
        return "Status pembayaran tidak valid.";
    }
    return null;
}
