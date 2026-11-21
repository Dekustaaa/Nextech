<?php
/**
 * Handler hapus kategori.
 *
 * Dipanggil dari tombol hapus di halaman admin kategori. File ini memeriksa
 * akses admin, memastikan ID valid, lalu menghapus lewat repository dan kembali
 * ke daftar kategori. Aksi dipisah dari view agar tidak ikut terjalankan saat
 * halaman sekadar dimuat.
 */
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../repositories/categories.php";
require_once __DIR__ . "/../validations.php";

// Guard admin: selain admin akan diarahkan ke halaman login admin.
cek_admin();

// ID berasal dari query string; helper memastikan hanya angka positif yang diproses.
$id = input_angka($_GET, "id");
if (validasi_id($id)) {
    hapus_kategori($conn, $id);
    $_SESSION["admin_pesan"] = "Kategori berhasil dihapus.";
}

// Redirect agar daftar kategori dimuat ulang dan aksi tidak terulang saat refresh.
header("Location: /PWL-SAS/views/admin/categories.php");
exit;
