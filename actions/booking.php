<?php
/**
 * Handler booking untuk user yang sudah login.
 *
 * Dipakai form di views/technician_booking.php. File ini memvalidasi data
 * kunjungan lewat validations.php, memilih teknisi otomatis bila user tidak
 * memilih, lalu menyimpan booking lewat repositories/technicians.php. Hasilnya
 * dikirim sebagai flash message agar view tetap fokus pada tampilan form.
 */
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../repositories/technicians.php";
require_once __DIR__ . "/../validations.php";

cek_login();

// Selain POST tidak ada data yang bisa diproses, jadi kembalikan ke form.
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: /PWL-SAS/views/technician_booking.php");
    exit;
}

$nama = input_teks($_POST, 'nama');
$telepon = input_teks($_POST, 'telepon');
$service_id = input_angka($_POST, 'service_id');
$technician_id = input_angka($_POST, 'technician_id');
$alamat = input_teks($_POST, 'alamat');
$tanggal = input_teks($_POST, 'tanggal');
$jam = input_teks($_POST, 'jam');
$keluhan = input_teks($_POST, 'keluhan');

// Validasi bentuk input, termasuk format tanggal/jam dan nomor telepon.
$error = validasi_booking($_POST);
if ($error !== null) {
    $_SESSION['pesan_booking'] = ['gagal', $error];
    header("Location: /PWL-SAS/views/technician_booking.php");
    exit;
}

// Tanpa teknisi pilihan, ambil otomatis kandidat dengan rating tertinggi yang masih tersedia.
if ($technician_id == 0) {
    $auto = ambil_teknisi_otomatis($conn);
    $technician_id = $auto ? (int)$auto['technician_id'] : 0;
} else {
    // Teknisi pilihan dicek ulang untuk mengantisipasi status yang berubah sejak form dibuka.
    $cek = cek_teknisi_tersedia($conn, $technician_id);
    if (!$cek) {
        $_SESSION['pesan_booking'] = ['gagal', 'Teknisi yang dipilih sudah tidak tersedia. Silakan pilih yang lain.'];
        header("Location: /PWL-SAS/views/technician_booking.php");
        exit;
    }
}

// Nomor booking memakai tanggal + jam server agar mudah dibaca pelanggan.
$no_booking = "BK-" . date("ymdHis");

// simpan_booking() false berarti INSERT gagal; tampilkan pesan dan biarkan user mencoba lagi.
if (!simpan_booking($conn, $no_booking, $nama, $telepon, $service_id, $technician_id, $alamat, $tanggal, $jam, $keluhan)) {
    $_SESSION['pesan_booking'] = ['gagal', 'Gagal menyimpan booking.'];
    header("Location: /PWL-SAS/views/technician_booking.php");
    exit;
}

// Booking tersimpan -> kunci teknisi agar tidak diambil booking lain.
if ($technician_id > 0) {
    teknisi_sibuk($conn, $technician_id);
}

// Flash sukses berisi nomor booking supaya user bisa mencatatnya.
$_SESSION['pesan_booking'] = ['sukses', "Permintaan berhasil dikirim! Nomor booking Anda: $no_booking"];
header("Location: /PWL-SAS/views/technician_booking.php");
exit;
