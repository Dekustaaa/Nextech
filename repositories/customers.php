<?php
/**
 * Query data pelanggan untuk panel admin.
 *
 * Menyediakan daftar pelanggan beserta jumlah pesanannya, detail satu
 * pelanggan, dan riwayat pesanan pelanggan tersebut. Semua query dikumpulkan
 * di sini agar halaman admin tidak menulis SQL dan relasi users/orders tetap
 * konsisten.
 */
function get_customers($conn) {
    // role_id = 2 membatasi hasil ke pelanggan; subquery menghitung order per user tanpa GROUP BY.
    $sql = "SELECT u.user_id, u.full_name, u.email, u.phone_number, u.created_at, up.address, up.date_of_birth,
                   (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.user_id) AS jml_pesanan
            FROM users u
            LEFT JOIN user_profiles up ON u.user_id = up.user_id
            WHERE u.role_id = 2
            ORDER BY u.user_id DESC";
    $query = mysqli_query($conn, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    return $data;
}

function get_customer_by_id($conn, $id) {
    // LEFT JOIN profil karena baris user_profiles bisa saja belum lengkap.
    $id = (int)$id;
    $sql = "SELECT u.user_id, u.full_name, u.email, u.phone_number, u.created_at, up.address, up.date_of_birth
            FROM users u
            LEFT JOIN user_profiles up ON u.user_id = up.user_id
            WHERE u.user_id = $id";
    $query = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($query);
}

function get_customer_orders($conn, $id) {
    // LEFT JOIN payments supaya pesanan yang belum punya baris pembayaran tetap tampil.
    $id = (int)$id;
    $sql = "SELECT o.order_id, o.order_date, o.total_amount, o.status, p.payment_status
            FROM orders o
            LEFT JOIN payments p ON o.order_id = p.order_id
            WHERE o.user_id = $id
            ORDER BY o.order_date DESC";
    $query = mysqli_query($conn, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    return $data;
}
