<?php
/**
 * Handler perubahan booking dari panel admin.
 *
 * Admin dapat mengganti teknisi dan/atau status booking. Input divalidasi lewat
 * validations.php, lalu status teknisi disinkronkan: sibuk saat booking masih
 * aktif, tersedia lagi saat booking selesai/dibatalkan. View admin cukup
 * mengirim POST dan menerima flash message.
 */
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../repositories/technicians.php";
require_once __DIR__ . "/../validations.php";

cek_admin();

// Nilai 0 atau string kosong berarti field tersebut tidak diubah oleh admin.
$id = input_angka($_POST, "booking_id");
$technician_id = input_angka($_POST, "technician_id");
$status = input_teks($_POST, "status");

// Tolak ID dan status yang tidak valid sebelum menyentuh data booking.
$error = validasi_update_booking($_POST);
if ($error !== null) {
    $_SESSION["admin_pesan"] = $error;
    header("Location: /PWL-SAS/views/admin/booking.php");
    exit;
}

// Ambil data lama agar status teknisi bisa disinkronkan.
$teknisi_lama = 0;
$status_lama = "";
// Prepared statement dipakai karena booking_id berasal dari form; "i" menandai tipe integer.
$stmt = mysqli_prepare($conn, "SELECT technician_id, status FROM bookings WHERE booking_id = ?");
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($res)) {
        $teknisi_lama = (int)($row["technician_id"] ?? 0);
        $status_lama = $row["status"] ?? "";
    }
    mysqli_stmt_close($stmt);
}

// Update teknisi hanya bila admin memilih teknisi baru.
if ($technician_id > 0) {
    $stmt = mysqli_prepare($conn, "UPDATE bookings SET technician_id=? WHERE booking_id=?");
    mysqli_stmt_bind_param($stmt, "ii", $technician_id, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Update status hanya bila dropdown status diisi.
if ($status != "") {
    $stmt = mysqli_prepare($conn, "UPDATE bookings SET status=? WHERE booking_id=?");
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Tentukan kondisi akhir booking setelah update.
$teknisi_akhir = $technician_id > 0 ? $technician_id : $teknisi_lama;
$status_akhir = $status != "" ? $status : $status_lama;

// Status akhir "Selesai"/"Dibatalkan" berarti tidak ada pekerjaan aktif untuk teknisi tersebut.
if (in_array($status_akhir, ["Selesai", "Dibatalkan"], true)) {
    // Booking selesai/batal -> bebaskan teknisi jika tidak ada booking aktif lain.
    if ($teknisi_akhir > 0) {
        // Helper mengecek booking aktif lain dulu, jadi teknisi tidak dilepas jika masih punya pekerjaan.
        bebaskan_teknisi_jika_nganggur($conn, $teknisi_akhir, $id);
    }
    // Jika teknisi diganti saat penyelesaian, teknisi lama juga perlu dicek.
    if ($technician_id > 0 && $teknisi_lama > 0 && $teknisi_lama !== $teknisi_akhir) {
        bebaskan_teknisi_jika_nganggur($conn, $teknisi_lama, $id);
    }
} else {
    // Booking masih aktif -> teknisi yang dipakai harus sibuk.
    if ($teknisi_akhir > 0) {
        // Penugasan baru langsung menandai teknisi sibuk agar tidak dipilih booking lain.
        teknisi_sibuk($conn, $teknisi_akhir);
    }
    // Jika teknisi diganti, bebaskan teknisi lama jika sudah tidak ada booking aktif.
    if ($technician_id > 0 && $teknisi_lama > 0 && $teknisi_lama !== $teknisi_akhir) {
        bebaskan_teknisi_jika_nganggur($conn, $teknisi_lama, $id);
    }
}

$_SESSION["admin_pesan"] = "Booking berhasil diubah.";
header("Location: /PWL-SAS/views/admin/booking.php");
exit;
