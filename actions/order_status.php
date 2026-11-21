<?php
/**
 * Handler perubahan status pesanan dari panel admin.
 *
 * Form admin dapat mengirim status pesanan dan/atau status pembayaran. ID dan
 * pilihan status divalidasi lewat validations.php sebelum memanggil fungsi
 * update di repositories/orders.php. Dengan begitu view hanya menampilkan
 * dropdown dan tidak menulis SQL sendiri.
 */
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../repositories/orders.php";
require_once __DIR__ . "/../validations.php";

cek_admin();

// Status pesanan (orders) dan status pembayaran (payments) adalah dua kolom berbeda.
$id = input_angka($_POST, "order_id");
$status = input_teks($_POST, "status");
$bayar = input_teks($_POST, "payment_status");

// Tolak ID atau status yang tidak dikenal sebelum mengubah database.
$error = validasi_order_status($_POST);
if ($error !== null) {
    $_SESSION["admin_pesan"] = $error;
    header("Location: /PWL-SAS/views/admin/orders.php");
    exit;
}

// Masing-masing hanya diubah jika nilainya dikirim, sehingga admin bisa mengganti salah satu saja.
if ($status !== "") {
    ubah_status_pesanan($conn, $id, $status);
}
if ($bayar !== "") {
    ubah_status_bayar($conn, $id, $bayar);
}

// Satu flash message untuk kedua perubahan agar daftar pesanan langsung memberi umpan balik.
$_SESSION["admin_pesan"] = "Pesanan berhasil diubah.";
header("Location: /PWL-SAS/views/admin/orders.php");
exit;
