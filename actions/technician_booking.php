<?php
/**
 * Handler form booking publik di pages/technician.
 *
 * Menerima POST berisi data pelanggan, layanan, teknisi, dan jadwal, lalu
 * memvalidasi bentuk input lewat validations.php dan menyimpan booking.
 * Berbeda dari actions/booking.php, file ini tidak mewajibkan login karena form
 * teknisi dapat diisi pengunjung. Semua hasil diproses dengan redirect + flash
 * message ke halaman form.
 */
session_start();

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/technicians.php";
require_once __DIR__ . "/../repositories/services.php";
require_once __DIR__ . "/../validations.php";

$tujuan = "/PWL-SAS/pages/technician/";

// Tolak akses langsung (GET) karena file ini hanya memproses submit form.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . $tujuan);
    exit;
}

$nama          = input_text($_POST, 'nama');
$telepon       = input_text($_POST, 'telepon');
$service_id    = input_number($_POST, 'service_id');
$technician_id = input_number($_POST, 'technician_id');
$alamat        = input_text($_POST, 'alamat');
$tanggal       = input_text($_POST, 'tanggal');
$jam           = input_text($_POST, 'jam');
$keluhan       = input_text($_POST, 'keluhan');

// Closure ini menyimpan input terakhir ke session lalu redirect, supaya form tidak kosong setelah validasi gagal.
$simpan_lama = function () use ($tujuan) {
    $_SESSION['booking_old'] = $_POST;
    header("Location: " . $tujuan);
    exit;
};

// Validasi bentuk input di server karena validasi HTML bisa dilewati.
$error = validate_booking($_POST);
if ($error !== null) {
    $_SESSION['booking_error'] = $error;
    $simpan_lama();
}

// Pastikan service_id benar-benar ada, bukan sekadar angka yang dikirim dari form.
$service = get_service_by_id($conn, $service_id);
if (!$service) {
    $_SESSION['booking_error'] = 'Layanan yang dipilih tidak ditemukan.';
    $simpan_lama();
}

// Tanpa pilihan teknisi, sistem memilih otomatis kandidat rating tertinggi yang masih tersedia.
if ($technician_id <= 0) {
    $otomatis = get_auto_technician($conn);
    $technician_id = $otomatis ? (int)$otomatis['technician_id'] : 0;
} else {
    // Teknisi pilihan user harus dicek ulang karena statusnya bisa berubah sejak form dibuka.
    $cek = is_technician_available($conn, $technician_id);
    if (!$cek) {
        $_SESSION['booking_error'] = 'Teknisi yang dipilih sudah tidak tersedia. Silakan pilih teknisi lain.';
        $simpan_lama();
    }
}

// Nomor booking dibentuk dari tanggal dan jam server, cukup unik untuk alur demo.
$no_booking = "BK-" . date("ymdHis");

// save_booking() mengembalikan false bila INSERT gagal; form diisi ulang dengan pesan error.
if (!save_booking($conn, $no_booking, $nama, $telepon, $service_id, $technician_id, $alamat, $tanggal, $jam, $keluhan)) {
    $_SESSION['booking_error'] = 'Gagal menyimpan permintaan booking. Silakan coba lagi.';
    $simpan_lama();
}

// Booking berhasil -> tandai teknisi sibuk agar tidak menerima penugasan lain.
if ($technician_id > 0) {
    mark_technician_busy($conn, $technician_id);
}

$teknisi = $technician_id > 0 ? is_technician_available($conn, $technician_id) : null;
$nama_teknisi = $teknisi['technician_name'] ?? 'Pilih otomatis';

/* is_technician_available mengembalikan null karena status baru saja diubah
   menjadi "sibuk", jadi ambil ulang tanpa filter status. */
if (!$teknisi && $technician_id > 0) {
    $query = mysqli_query($conn, "SELECT technician_name FROM technicians WHERE technician_id = " . (int)$technician_id);
    $row = mysqli_fetch_assoc($query);
    $nama_teknisi = $row['technician_name'] ?? 'Teknisi';
}

// Data ringkas ini dipakai halaman form untuk menampilkan kartu konfirmasi booking.
$_SESSION['booking_sukses'] = [
    'kode'     => $no_booking,
    'nama'     => $nama,
    'teknisi'  => $nama_teknisi,
    'layanan'  => $service['service_name'],
    'jadwal'   => date('d/m/Y', strtotime($tanggal)) . ', ' . substr($jam, 0, 5),
];

header("Location: " . $tujuan);
exit;
