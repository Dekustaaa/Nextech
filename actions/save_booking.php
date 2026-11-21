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

require_admin();

$nama = input_text($_POST, "nama");
$telepon = input_text($_POST, "telepon");
$service_id = input_number($_POST, "service_id");
$technician_id = input_number($_POST, "technician_id");
$alamat = input_text($_POST, "alamat");
$tanggal = input_text($_POST, "tanggal");
$jam = input_text($_POST, "jam");
$keluhan = input_text($_POST, "keluhan");

// Validasi bentuk input sebelum INSERT agar tidak membuat booking tanpa data kunjungan.
$error = validate_booking($_POST, "Semua kolom wajib diisi.");
if ($error !== null) {
    $_SESSION["admin_pesan"] = $error;
    header("Location: /PWL-SAS/views/admin/booking.php");
    exit;
}

// Nomor booking mengikuti waktu server; technician_id 0 akan disimpan sebagai NULL oleh repository.
$no_booking = "BK-" . date("ymdHis");
save_booking($conn, $no_booking, $nama, $telepon, $service_id, $technician_id, $alamat, $tanggal, $jam, $keluhan);

// Setelah booking tersimpan, teknisi yang ditugaskan harus berstatus sibuk.
if ($technician_id > 0) {
    mark_technician_busy($conn, $technician_id);
}

$_SESSION["admin_pesan"] = "Booking $no_booking berhasil dibuat.";
header("Location: /PWL-SAS/views/admin/booking.php");
exit;
