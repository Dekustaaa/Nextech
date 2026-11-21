<?php
/**
 * Query layanan jasa.
 *
 * Menyediakan daftar semua layanan dan pencarian satu layanan berdasarkan ID,
 * dipakai halaman booking, cart layanan, serta handler booking. Query dipisah
 * dari view agar data layanan konsisten di semua halaman.
 */
function ambil_services($conn) {
    // Urut service_id agar daftar layanan tampil stabil di form booking.
    $query = mysqli_query($conn, "SELECT * FROM services ORDER BY service_id ASC");
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    return $data;
}

function ambil_service_by_id($conn, $id) {
    // Cast ke int sebelum masuk ke WHERE; hasil false/null berarti layanan tidak ada.
    $id = (int)$id;
    $query = mysqli_query($conn, "SELECT * FROM services WHERE service_id = $id");
    return mysqli_fetch_assoc($query);
}
