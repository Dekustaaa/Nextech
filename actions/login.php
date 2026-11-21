<?php
/**
 * Handler login untuk pelanggan dan admin.
 *
 * Menerima POST dari dua form login yang berbeda, memvalidasi bentuk input lewat
 * validations.php, lalu mencocokkan kredensial lewat repositories/auth.php.
 * Setelah itu identitas disimpan ke session dan user diarahkan ke dashboard
 * admin atau katalog sesuai role. Logika ini dipisah dari view agar form login
 * tidak perlu memuat query atau aturan session.
 */
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../validations.php";

$email = input_text($_POST, "email");
$password = (string)($_POST["password"] ?? "");
// "asal" menandai form mana yang dipakai, karena admin dan user punya halaman login berbeda.
$asal = input_text($_POST, "asal");
if ($asal === "") {
    $asal = "user";
}

// Validasi bentuk input sebelum query agar data yang jelas tidak valid tidak diproses lebih jauh.
$error = validate_login($_POST);
if ($error !== null) {
    $_SESSION["auth_pesan"] = $error;
    if ($asal == "admin") {
        header("Location: /PWL-SAS/views/admin/login.php");
    } else {
        header("Location: /PWL-SAS/views/auth/login.php");
    }
    exit;
}

$user = get_user_by_email($conn, $email);

// Cocokkan kredensial; pesan dibuat umum agar tidak membocorkan email mana yang terdaftar.
// Catatan: data contoh menyimpan password apa adanya, sehingga dibandingkan langsung (bukan password_verify).
if (!$user || $user["password_hash"] != $password) {
    $_SESSION["auth_pesan"] = "Email atau password salah.";
    if ($asal == "admin") {
        header("Location: /PWL-SAS/views/admin/login.php");
    } else {
        header("Location: /PWL-SAS/views/auth/login.php");
    }
    exit;
}

// Login dari form admin hanya boleh memakai akun dengan role_id = 1.
if ($asal == "admin" && $user["role_id"] != 1) {
    $_SESSION["auth_pesan"] = "Akun ini bukan admin.";
    header("Location: /PWL-SAS/views/admin/login.php");
    exit;
}

// Simpan identitas ke session agar require_login()/require_admin() bisa memeriksanya di request berikutnya.
$_SESSION["user_id"] = $user["user_id"];
$_SESSION["role_id"] = $user["role_id"];
$_SESSION["nama"] = $user["full_name"];
$_SESSION["email"] = $user["email"];
// Ganti ID session setelah login untuk mengurangi risiko session fixation.
session_regenerate_id(true);

// Arahkan sesuai role: admin ke dashboard, pelanggan ke katalog utama.
if ($user["role_id"] == 1) {
    header("Location: /PWL-SAS/views/admin/dashboard.php");
} else {
    header("Location: /PWL-SAS/mainpage.php");
}
exit;
