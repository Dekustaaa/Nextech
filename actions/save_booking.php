<?php
/**
 * Handler pembuatan booking dari panel admin.
 *
 * Admin mengisi form booking manual untuk pelanggan (misalnya pesanan lewat
 * telepon). File ini memvalidasi input lewat validations.php, menyimpan lewat
 * repositories/technicians.php, dan menandai teknisi sibuk. View admin cukup
 * menampilkan form serta flash message hasilnya.
 */
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../repositories/technicians.php";
require_once __DIR__ . "/../validations.php";

cek_admin();

$nama = input_teks($_POST, "nama");
$telepon = input_teks($_POST, "telepon");
$service_id = input_angka($_POST, "service_id");
$technician_id = input_angka($_POST, "technician_id");
$alamat = input_teks($_POST, "alamat");
$tanggal = input_teks($_POST, "tanggal");
$jam = input_teks($_POST, "jam");
$keluhan = input_teks($_POST, "keluhan");

// Validasi bentuk input sebelum INSERT agar tidak membuat booking tanpa data kunjungan.
$error = validasi_booking($_POST, "Semua kolom wajib diisi.");
if ($error !== null) {
    $_SESSION["admin_pesan"] = $error;
    header("Location: /PWL-SAS/views/admin/booking.php");
    exit;
}

// Nomor booking mengikuti waktu server; technician_id 0 akan disimpan sebagai NULL oleh repository.
$no_booking = "BK-" . date("ymdHis");
simpan_booking($conn, $no_booking, $nama, $telepon, $service_id, $technician_id, $alamat, $tanggal, $jam, $keluhan);

// Setelah booking tersimpan, teknisi yang ditugaskan harus berstatus sibuk.
if ($technician_id > 0) {
    teknisi_sibuk($conn, $technician_id);
}

$_SESSION["admin_pesan"] = "Booking $no_booking berhasil dibuat.";
header("Location: /PWL-SAS/views/admin/booking.php");
exit;
