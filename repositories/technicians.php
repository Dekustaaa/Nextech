<?php
/**
 * Query teknisi dan booking jasa.
 *
 * Menyediakan daftar teknisi tersedia, penugasan otomatis, pengecekan status,
 * perubahan status sibuk/tersedia, penyimpanan booking, serta data booking
 * terbaru untuk dashboard. Logika sinkronisasi status teknisi dipusatkan di
 * sini agar action booking tidak menulis query sendiri.
 */
function ambil_teknisi($conn) {
    // Hanya teknisi tersedia, diurutkan rating tertinggi supaya rekomendasi terbaik muncul lebih dulu.
    $query = mysqli_query($conn, "SELECT * FROM technicians WHERE status = 'tersedia' ORDER BY rating DESC");
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    return $data;
}

function ambil_teknisi_otomatis($conn) {
    // LIMIT 1 mengambil kandidat teratas untuk penugasan otomatis saat user tidak memilih teknisi.
    $query = mysqli_query($conn, "SELECT * FROM technicians WHERE status = 'tersedia' ORDER BY rating DESC LIMIT 1");
    return mysqli_fetch_assoc($query);
}

function cek_teknisi_tersedia($conn, $id) {
    // Dipakai tepat sebelum booking disimpan agar teknisi yang sudah sibuk tidak dipilih.
    $id = (int)$id;
    $query = mysqli_query($conn, "SELECT * FROM technicians WHERE technician_id = $id AND status = 'tersedia'");
    return mysqli_fetch_assoc($query);
}

function teknisi_sibuk($conn, $id) {
    // Status disimpan sebagai teks "sibuk"/"tersedia" dan dibaca oleh filter di fungsi-fungsi di atas.
    $id = (int)$id;
    mysqli_query($conn, "UPDATE technicians SET status = 'sibuk' WHERE technician_id = $id");
}

function teknisi_tersedia($conn, $id) {
    // Dipanggil setelah booking selesai/dibatalkan dan tidak ada pekerjaan aktif lain.
    $id = (int)$id;
    mysqli_query($conn, "UPDATE technicians SET status = 'tersedia' WHERE technician_id = $id");
}

function bebaskan_teknisi_jika_nganggur($conn, $id, $kecuali_booking_id = 0) {
    $id = (int)$id;
    $kecuali_booking_id = (int)$kecuali_booking_id;
    if ($id <= 0) {
        return;
    }
    // Hitung booking lain yang masih aktif (selain booking yang sedang diproses).
    // Teknisi hanya dilepas ke "tersedia" kalau tidak ada pekerjaan tersisa.
    $sql = "SELECT COUNT(*) AS jml FROM bookings
            WHERE technician_id = $id
            AND booking_id != $kecuali_booking_id
            AND status NOT IN ('Selesai', 'Dibatalkan')";
    $query = mysqli_query($conn, $sql);
    if (!$query) {
        return;
    }
    $row = mysqli_fetch_assoc($query);
    if ((int)($row["jml"] ?? 0) === 0) {
        teknisi_tersedia($conn, $id);
    }
}

function ambil_booking_terakhir($conn) {
    // LEFT JOIN agar booking tanpa layanan/teknisi tetap tampil di widget; LIMIT 5 untuk ringkasan dashboard.
    $sql = "SELECT b.no_booking, b.status, b.created_at, s.service_name, t.technician_name
            FROM bookings b
            LEFT JOIN services s ON b.service_id = s.service_id
            LEFT JOIN technicians t ON b.technician_id = t.technician_id
            ORDER BY b.created_at DESC
            LIMIT 5";
    $query = mysqli_query($conn, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    return $data;
}

function simpan_booking($conn, $no_booking, $nama, $telepon, $service_id, $technician_id, $alamat, $tanggal, $jam, $keluhan) {
    // Dua versi INSERT karena technician_id NULL butuh jumlah placeholder berbeda.
    // Tipe bind mengikuti urutan kolom (s=string, i=integer).
    $service_id = (int)$service_id;
    $technician_id = (int)$technician_id;
    if ($technician_id > 0) {
        $stmt = mysqli_prepare($conn, "INSERT INTO bookings (no_booking, customer_name, phone, service_id, technician_id, address, tanggal, jam, keluhan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssiissss", $no_booking, $nama, $telepon, $service_id, $technician_id, $alamat, $tanggal, $jam, $keluhan);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO bookings (no_booking, customer_name, phone, service_id, technician_id, address, tanggal, jam, keluhan) VALUES (?, ?, ?, ?, NULL, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssissss", $no_booking, $nama, $telepon, $service_id, $alamat, $tanggal, $jam, $keluhan);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $ok;
    }
}

function waktu_lalu($waktu) {
    // Ubah selisih detik menjadi teks relatif untuk daftar aktivitas booking terbaru.
    $selisih = time() - strtotime($waktu);
    if ($selisih < 60) {
        return "Baru saja";
    }
    if ($selisih < 3600) {
        return floor($selisih / 60) . " menit lalu";
    }
    if ($selisih < 86400) {
        return floor($selisih / 3600) . " jam lalu";
    }
    return floor($selisih / 86400) . " hari lalu";
}
