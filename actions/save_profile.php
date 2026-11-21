<?php
/**
 * Handler perubahan profil akun.
 *
 * Dipakai oleh form profil admin maupun pelanggan. File ini memvalidasi bentuk
 * input lewat validations.php, mengecek keunikan email, lalu memanggil
 * update_account() di repositories/auth.php. Redirect dan flash message dipilih
 * berdasarkan role agar view profil tetap hanya menampilkan form dan pesan.
 */
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../validations.php";

require_login();

// ID selalu diambil dari session, bukan dari form, agar user tidak bisa mengedit akun orang lain.
$id = (int)$_SESSION["user_id"];
$nama = input_text($_POST, "nama");
$email = input_text($_POST, "email");
$telepon = input_text($_POST, "telepon");
$password = (string)($_POST["password"] ?? "");
$balik_admin = "/PWL-SAS/views/admin/profile.php";
$balik_user = "/PWL-SAS/views/profile.php";
// Role 1 = admin dan punya halaman + flash message sendiri; selain itu dianggap pelanggan.
$balik = ($_SESSION["role_id"] ?? 2) == 1 ? $balik_admin : $balik_user;
$key_pesan = ($_SESSION["role_id"] ?? 2) == 1 ? "admin_pesan" : "user_pesan";

// Nama dan email wajib; telepon dan password boleh kosong, tetapi formatnya tetap dicek bila diisi.
$error = validate_profile($_POST);
if ($error !== null) {
    $_SESSION[$key_pesan] = $error;
    header("Location: " . $balik);
    exit;
}

// Email harus unik, tetapi email milik akun ini sendiri tidak dianggap bentrok.
$cek = get_user_by_email($conn, $email);
if ($cek && $cek["user_id"] != $id) {
    $_SESSION[$key_pesan] = "Email sudah dipakai.";
    header("Location: " . $balik);
    exit;
}

// Password kosong berarti tidak diganti; update_account() memilih query yang sesuai.
update_account($conn, $id, $nama, $email, $telepon, $password);
// Sinkronkan session agar nama/email di header langsung berubah tanpa login ulang.
$_SESSION["nama"] = $nama;
$_SESSION["email"] = $email;
$_SESSION[$key_pesan] = "Akun berhasil diubah.";

header("Location: " . $balik);
exit;
