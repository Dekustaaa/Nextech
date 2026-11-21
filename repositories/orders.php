<?php
/**
 * Query pesanan produk untuk panel admin.
 *
 * Mengambil daftar pesanan, detail satu pesanan, item di dalamnya, serta
 * fungsi update status pesanan dan status pembayaran. Dengan memusatkan query
 * di sini, action admin cukup memanggil fungsi dan view tinggal mencetak data.
 */
function get_orders($conn) {
    // JOIN users untuk identitas pemesan; LEFT JOIN payments karena pembayaran bisa belum dibuat.
    $sql = "SELECT o.order_id, o.order_date, o.total_amount, o.status, u.full_name, u.email, p.payment_method, p.payment_status
            FROM orders o
            JOIN users u ON o.user_id = u.user_id
            LEFT JOIN payments p ON o.order_id = p.order_id
            ORDER BY o.order_date DESC";
    $query = mysqli_query($conn, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    return $data;
}

function get_order_by_id($conn, $id) {
    // $id sudah di-cast ke int, jadi aman disisipkan langsung ke WHERE.
    $id = (int)$id;
    $sql = "SELECT o.order_id, o.order_date, o.total_amount, o.status, u.full_name, u.email, u.phone_number, p.payment_method, p.payment_status
            FROM orders o
            JOIN users u ON o.user_id = u.user_id
            LEFT JOIN payments p ON o.order_id = p.order_id
            WHERE o.order_id = $id";
    $query = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($query);
}

function get_order_items($conn, $id) {
    // Ambil item beserta nama produk untuk halaman detail pesanan admin.
    $id = (int)$id;
    $sql = "SELECT oi.quantity, oi.unit_price, p.product_name
            FROM order_items oi
            JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = $id";
    $query = mysqli_query($conn, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    return $data;
}

function update_order_status($conn, $id, $status) {
    // Prepared statement: status berasal dari form admin dan diperlakukan sebagai string.
    $id = (int)$id;
    $stmt = mysqli_prepare($conn, "UPDATE orders SET status=? WHERE order_id=?");
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function update_payment_status($conn, $id, $status) {
    $id = (int)$id;
    $stmt = mysqli_prepare($conn, "UPDATE payments SET payment_status=? WHERE order_id=?");
    mysqli_stmt_bind_param($stmt, "si", $status, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    // Saat ditandai Lunas, catat waktu pembayaran; $id sudah di-cast int jadi aman untuk query ini.
    if ($status == "Lunas") {
        mysqli_query($conn, "UPDATE payments SET paid_at = NOW() WHERE order_id = $id");
    }
    return $ok;
}
