<?php
/**
 * Handler pembayaran dari halaman checkout.
 *
 * Menerima POST berisi nomor order, metode, dan total, memvalidasi bentuknya
 * lewat validations.php, lalu menyimpan ringkasannya di session untuk halaman
 * sukses. Keranjang dikosongkan karena transaksi dianggap selesai. File ini
 * tidak menulis ke tabel orders/payments; alur demo ini hanya meneruskan data
 * tampilan ke payment_success.php.
 */
// Sesi menyimpan status login dan ringkasan pembayaran sementara.
session_start();
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../validations.php";

require_login();

// Checkout hanya boleh dipicu dari submit form, bukan akses URL langsung.
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: /PWL-SAS/mainpage.php");
    exit;
}

// Hidden input tetap harus dicek karena nilainya bisa diubah lewat browser.
$error = validate_payment($_POST);
if ($error !== null) {
    $_SESSION["checkout_error"] = $error;
    header("Location: /PWL-SAS/views/checkout.php");
    exit;
}

$no_order = input_text($_POST, "no_order");
$metode = input_text($_POST, "metode");
$total = input_number($_POST, "total");

// Ringkasan ini dipakai payment_success.php untuk menampilkan detail pembayaran.
$_SESSION["pesanan"] = [
    "no_order" => $no_order,
    "metode" => $metode,
    "total" => $total,
    "tanggal" => date("d-m-Y H:i")
];

// Kedua keranjang dikosongkan agar item tidak tersisa setelah pembayaran.
$_SESSION["cart"] = [];
$_SESSION["layanan"] = [];

// Redirect mencegah form pembayaran terkirim ulang saat halaman di-refresh.
header("Location: /PWL-SAS/views/payment_success.php");
exit;
