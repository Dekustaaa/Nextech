<?php
/**
 * Handler hapus produk.
 *
 * Dipanggil lewat link query string dari tabel produk admin. Setelah memastikan
 * pemakai admin dan ID valid, file ini memanggil delete_product() lalu kembali ke
 * halaman daftar dengan flash message. Penghapusan dipisahkan dari view agar
 * aksi destruktif tidak terjadi saat halaman hanya dibuka.
 */
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../repositories/products.php";
require_once __DIR__ . "/../validations.php";

// Guard admin: pelanggan yang membuka URL ini akan diarahkan ke login admin.
require_admin();

// ID diambil dari query string tombol hapus; helper memastikan nilainya angka positif.
$id = input_number($_GET, "id");
if (validate_id($id)) {
    delete_product($conn, $id);
    $_SESSION["admin_pesan"] = "Produk berhasil dihapus.";
}

// Redirect selalu dijalankan agar daftar produk dimuat ulang tanpa mengulang aksi saat refresh.
header("Location: /PWL-SAS/views/admin/products.php");
exit;
