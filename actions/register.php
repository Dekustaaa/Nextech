<?php
/**
 * Handler registrasi akun pelanggan.
 *
 * Menerima POST dari views/auth/register.php, memvalidasi bentuk input lewat
 * validations.php, lalu memakai fungsi di repositories/auth.php untuk mengecek
 * email dan menyimpan user baru. Semua balasan berupa redirect + flash message
 * di session; tidak ada HTML di sini supaya tampilan form tetap terpisah.
 */
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../validations.php";

// Ambil input form dengan helper agar trim konsisten di semua action.
$nama = input_text($_POST, "nama");
$email = input_text($_POST, "email");
$password = (string)($_POST["password"] ?? "");

// Validasi bentuk input di server; pengecekan di browser tetap bisa dilewati.
$error = validate_register($_POST);
if ($error !== null) {
    $_SESSION["auth_pesan"] = $error;
    header("Location: /PWL-SAS/views/auth/register.php");
    exit;
}

// Email adalah identitas login dan harus unik, jadi tolak sebelum INSERT.
if (get_user_by_email($conn, $email)) {
    $_SESSION["auth_pesan"] = "Email sudah dipakai.";
    header("Location: /PWL-SAS/views/auth/register.php");
    exit;
}

// register_user() membuat baris users (role pelanggan) sekaligus profil kosongnya.
// Hasil boolean menentukan flash message dan halaman tujuan redirect.
if (register_user($conn, $nama, $email, $password)) {
    $_SESSION["auth_pesan"] = "Registrasi berhasil, silakan login.";
    header("Location: /PWL-SAS/views/auth/login.php");
} else {
    $_SESSION["auth_pesan"] = "Registrasi gagal.";
    header("Location: /PWL-SAS/views/auth/register.php");
}
exit;
