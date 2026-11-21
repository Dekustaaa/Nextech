<?php
/**
 * Query dan helper tampilan untuk dashboard admin.
 *
 * Mengumpulkan agregasi KPI (jumlah pesanan, pendapatan, teknisi, pelanggan),
 * daftar pesanan terbaru dari orders + bookings, layanan populer, serta format
 * angka dan warna status. View dashboard cukup memanggil fungsi ini tanpa
 * menulis query atau aturan format sendiri.
 *
 * Catatan: query di file ini disusun internal tanpa input user, sehingga aman
 * memakai mysqli_query langsung; nilai yang berasal dari URL/form tetap di-cast
 * sebelum masuk ke SQL.
 */
function get_scalar($conn, $sql) {
    // Ambil kolom pertama dari query agregat; fallback 0 bila hasilnya kosong.
    $query = mysqli_query($conn, $sql);
    $row   = mysqli_fetch_row($query);
    return $row ? (float) $row[0] : 0;
}

function format_rupiah($angka) {
    // Format rupiah penuh dengan pemisah ribuan titik, tanpa desimal.
    return "Rp " . number_format($angka, 0, ',', '.');
}

function format_compact($angka) {
    // Versi ringkas untuk kartu KPI: miliar menjadi "M", juta menjadi "jt".
    if ($angka >= 1000000000) {
        return "Rp " . number_format($angka / 1000000000, 1, ',', '.') . " M";
    }
    if ($angka >= 1000000) {
        return "Rp " . number_format($angka / 1000000, 1, ',', '.') . " jt";
    }
    return "Rp " . number_format($angka, 0, ',', '.');
}

function get_status_color($status) {
    // Kelas Tailwind dipetakan dari teks status agar badge konsisten di seluruh dashboard.
    if ($status == 'Pending' || $status == 'Menunggu') {
        return 'bg-amber-100 text-amber-700';
    }
    if ($status == 'Dikonfirmasi' || $status == 'Diproses') {
        return 'bg-blue-100 text-blue-700';
    }
    if ($status == 'Dikirim' || $status == 'Selesai') {
        return 'bg-emerald-100 text-emerald-700';
    }
    if ($status == 'Dibatalkan') {
        return 'bg-red-100 text-red-600';
    }
    return 'bg-slate-100 text-slate-600';
}

function total_orders($conn) {
    // KPI menghitung pesanan produk (orders) dan booking jasa (bookings) sekaligus.
    return get_scalar($conn, "SELECT (SELECT COUNT(*) FROM orders) + (SELECT COUNT(*) FROM bookings)");
}

function todays_orders($conn) {
    // CURDATE() mengikuti tanggal server agar angka "hari ini" konsisten dengan database.
    return get_scalar($conn, "SELECT
        (SELECT COUNT(*) FROM orders   WHERE DATE(order_date) = CURDATE()) +
        (SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = CURDATE())");
}

function monthly_revenue($conn) {
    // Pendapatan hanya dari orders dan mengabaikan pesanan Dibatalkan; COALESCE mencegah null saat belum ada data.
    return get_scalar($conn, "SELECT COALESCE(SUM(total_amount), 0) FROM orders
                                    WHERE MONTH(order_date) = MONTH(CURDATE())
                                    AND YEAR(order_date) = YEAR(CURDATE())
                                    AND status <> 'Dibatalkan'");
}

function total_technicians($conn) {
    return get_scalar($conn, "SELECT COUNT(*) FROM technicians");
}

function available_technicians($conn) {
    // Status teknisi disimpan sebagai teks "tersedia"/"sibuk" di tabel technicians.
    return get_scalar($conn, "SELECT COUNT(*) FROM technicians WHERE status = 'tersedia'");
}

function total_customers($conn) {
    // role_id = 2 adalah pelanggan; admin (role 1) tidak dihitung di KPI ini.
    return get_scalar($conn, "SELECT COUNT(*) FROM users WHERE role_id = 2");
}

function total_products($conn) {
    return get_scalar($conn, "SELECT COUNT(*) FROM products");
}

function top_products($conn) {
    // GROUP BY produk lalu urutkan total quantity terjual untuk mendapat produk nomor satu.
    $sql = "SELECT p.product_name, SUM(oi.quantity) AS terjual
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            GROUP BY p.product_id
            ORDER BY terjual DESC
            LIMIT 1";
    $query = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($query);
}

function latest_orders($conn) {
    // UNION ALL menggabungkan orders dan bookings dengan bentuk kolom sama,
    // supaya keduanya bisa diurutkan bersama berdasarkan waktu. LIMIT 5 untuk widget ringkas.
    $sql = "(SELECT CONCAT('ORD-', o.order_id) AS kode,
                    u.full_name AS pelanggan,
                    COALESCE((SELECT p.product_name
                              FROM order_items oi
                              JOIN products p ON oi.product_id = p.product_id
                              WHERE oi.order_id = o.order_id
                              LIMIT 1), '-') AS item,
                    o.status AS status,
                    o.total_amount AS total,
                    o.order_date AS waktu
             FROM orders o
             JOIN users u ON o.user_id = u.user_id)
            UNION ALL
            (SELECT b.no_booking,
                    b.customer_name,
                    s.service_name,
                    b.status,
                    s.base_price,
                    b.created_at
             FROM bookings b
             JOIN services s ON b.service_id = s.service_id)
            ORDER BY waktu DESC
            LIMIT 5";

    $query = mysqli_query($conn, $sql);
    $data  = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    return $data;
}

function popular_services($conn) {
    // LEFT JOIN agar layanan yang belum pernah dibooking tetap muncul dengan jumlah 0.
    $sql = "SELECT s.service_name, s.base_price, COUNT(b.booking_id) AS jumlah
            FROM services s
            LEFT JOIN bookings b ON s.service_id = b.service_id
            GROUP BY s.service_id
            ORDER BY jumlah DESC
            LIMIT 5";
    $query = mysqli_query($conn, $sql);
    $data  = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    return $data;
}

function get_admin($conn) {
    // Ambil satu akun admin (role 1) untuk ditampilkan di header/sidebar dashboard.
    $query = mysqli_query($conn, "SELECT full_name, email FROM users WHERE role_id = 1 LIMIT 1");
    return mysqli_fetch_assoc($query);
}