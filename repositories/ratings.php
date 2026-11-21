<?php
/**
 * Query rating dan ulasan produk.
 *
 * Menyediakan ringkasan rating untuk katalog, rating milik satu user, daftar
 * ulasan produk, dan penyimpanan rating. Query dikumpulkan di sini agar halaman
 * produk tidak menghitung agregat sendiri dan aturan satu-user-satu-rating
 * tetap terpusat.
 */
function get_all_ratings($conn) {
    // Satu query agregat untuk semua produk, lalu di-index per product_id agar view tidak query berulang (N+1).
    $query = mysqli_query($conn, "SELECT product_id, ROUND(AVG(rating), 1) AS rata_rata, COUNT(*) AS jumlah FROM product_ratings GROUP BY product_id");
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[(int)$row["product_id"]] = $row;
    }
    return $data;
}

function get_rating_summary($conn, $product_id) {
    // AVG/COUNT untuk satu produk; hasil kosong dikembalikan sebagai nol agar perhitungan di view aman.
    $product_id = (int)$product_id;
    $stmt = mysqli_prepare($conn, "SELECT ROUND(AVG(rating), 1) AS rata_rata, COUNT(*) AS jumlah FROM product_ratings WHERE product_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if (!$row) {
        return ["rata_rata" => 0, "jumlah" => 0];
    }
    return $row;
}

function get_user_rating($conn, $product_id, $user_id) {
    // Dipakai untuk menentukan update/insert dan menampilkan rating yang sudah pernah diberikan user.
    $product_id = (int)$product_id;
    $user_id = (int)$user_id;
    $stmt = mysqli_prepare($conn, "SELECT * FROM product_ratings WHERE product_id = ? AND user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "ii", $product_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

function get_product_reviews($conn, $product_id) {
    // LEFT JOIN users agar ulasan tetap tampil meskipun akun penulisnya sudah tidak ada.
    $product_id = (int)$product_id;
    $stmt = mysqli_prepare($conn, "SELECT product_ratings.*, users.full_name
            FROM product_ratings
            LEFT JOIN users ON product_ratings.user_id = users.user_id
            WHERE product_ratings.product_id = ?
            ORDER BY product_ratings.created_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $data;
}

function save_rating($conn, $product_id, $user_id, $rating, $review) {
    // Upsert manual: cek rating user untuk produk ini, lalu UPDATE jika sudah ada atau INSERT jika belum.
    // Aturan bisnis: satu user hanya punya satu rating per produk.
    $product_id = (int)$product_id;
    $user_id = (int)$user_id;
    $rating = (int)$rating;
    $cek = get_user_rating($conn, $product_id, $user_id);
    if ($cek) {
        $stmt = mysqli_prepare($conn, "UPDATE product_ratings SET rating = ?, review = ? WHERE rating_id = ?");
        mysqli_stmt_bind_param($stmt, "isi", $rating, $review, $cek["rating_id"]);
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO product_ratings (product_id, user_id, rating, review) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiis", $product_id, $user_id, $rating, $review);
    }
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
