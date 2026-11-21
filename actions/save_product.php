<?php
/**
 * Handler tambah/edit produk dari panel admin.
 *
 * Menerima POST form produk, memvalidasi bentuk input lewat validations.php,
 * memastikan pemakai adalah admin, lalu meneruskan data ke
 * repositories/products.php. Satu file menangani dua mode (tambah dan ubah)
 * berdasarkan product_id, sehingga view admin tidak perlu tahu detail SQL.
 */
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../repositories/products.php";
require_once __DIR__ . "/../validations.php";

// Hanya admin yang boleh menulis data katalog.
require_admin();

// Helper menjaga tipe data tetap konsisten: teks di-trim, kolom angka di-cast ke int.
$nama = input_text($_POST, "product_name");
$kategori = input_number($_POST, "category_id");
$harga = input_number($_POST, "price");
$stok = input_number($_POST, "stock");
$deskripsi = input_text($_POST, "description");
$url = input_text($_POST, "url");
$badge = input_text($_POST, "badge");
$id = input_number($_POST, "product_id");

// Nama dan kategori adalah data minimal agar produk bisa ditampilkan di katalog.
$error = validate_product($_POST);
if ($error !== null) {
    $_SESSION["admin_pesan"] = $error;
    header("Location: /PWL-SAS/views/admin/products.php");
    exit;
}

// product_id > 0 berasal dari form edit; 0/kosong berarti tambah produk baru.
if ($id > 0) {
    update_product($conn, $id, $nama, $kategori, $harga, $stok, $deskripsi, $url, $badge);
    $_SESSION["admin_pesan"] = "Produk berhasil diubah.";
} else {
    save_product($conn, $nama, $kategori, $harga, $stok, $deskripsi, $url, $badge);
    $_SESSION["admin_pesan"] = "Produk berhasil ditambah.";
}

header("Location: /PWL-SAS/views/admin/products.php");
exit;
