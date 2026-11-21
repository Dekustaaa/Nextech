<?php
/**
 * Handler tambah/edit kategori produk.
 *
 * Menerima POST dari panel admin, memvalidasi input lewat validations.php,
 * memastikan akses admin, lalu memanggil simpan_kategori() atau
 * ubah_kategori() di repositories/categories.php. Pemisahan ini membuat view
 * kategori tidak perlu menulis query atau aturan validasi sendiri.
 */
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../repositories/categories.php";
require_once __DIR__ . "/../validations.php";

cek_admin();

$nama = input_teks($_POST, "category_name");
$deskripsi = input_teks($_POST, "cat_description");
$image = input_teks($_POST, "image");
// category_id > 0 berarti edit; 0 berarti tambah kategori baru.
$id = input_angka($_POST, "category_id");

// Nama kategori wajib karena dipakai sebagai label di katalog dan filter.
$error = validasi_kategori($_POST);
if ($error !== null) {
    $_SESSION["admin_pesan"] = $error;
    header("Location: /PWL-SAS/views/admin/categories.php");
    exit;
}

if ($id > 0) {
    ubah_kategori($conn, $id, $nama, $deskripsi, $image);
    $_SESSION["admin_pesan"] = "Kategori berhasil diubah.";
} else {
    simpan_kategori($conn, $nama, $deskripsi, $image);
    $_SESSION["admin_pesan"] = "Kategori berhasil ditambah.";
}

header("Location: /PWL-SAS/views/admin/categories.php");
exit;
