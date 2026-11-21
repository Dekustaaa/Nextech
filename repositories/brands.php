<?php
/**
 * Query kategori dan brand.
 *
 * Menyediakan daftar brand, brand per kategori, pencarian brand berdasarkan
 * slug, serta pengelompokan kategori beserta brand-nya untuk halaman katalog.
 * Relasi banyak-ke-banyak disimpan di tabel pivot category_brands.
 */

function ambil_brands($conn) {
    $query = mysqli_query($conn, "SELECT * FROM brands ORDER BY brand_name ASC");
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    return $data;
}

function ambil_brands_by_category($conn, $category_id) {
    $category_id = (int)$category_id;
    // JOIN ke pivot category_brands karena satu brand bisa dimiliki beberapa kategori.
    $sql = "SELECT b.*
            FROM brands b
            INNER JOIN category_brands cb ON b.brand_id = cb.brand_id
            WHERE cb.category_id = $category_id
            ORDER BY b.brand_id ASC";
    $query = mysqli_query($conn, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    return $data;
}

function ambil_brand_by_slug($conn, $slug) {
    $slug = trim($slug);
    if ($slug === "") {
        return null;
    }
    // Slug dipakai di URL filter; prepared statement mencegah input URL masuk mentah ke SQL.
    $stmt = mysqli_prepare($conn, "SELECT * FROM brands WHERE brand_slug = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $slug);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

/**
 * Mengambil semua kategori beserta brand-nya.
 * Hasil: [ ['category_id' => 1, ..., 'brands' => [ ['brand_id'=>...], ... ]], ... ]
 */
function ambil_kategori_dengan_brand($conn) {
    $sql = "SELECT c.*, b.brand_id, b.brand_name, b.brand_slug
            FROM categories c
            LEFT JOIN category_brands cb ON c.category_id = cb.category_id
            LEFT JOIN brands b ON cb.brand_id = b.brand_id
            ORDER BY c.category_id ASC, b.brand_id ASC";
    $query = mysqli_query($conn, $sql);
    $data = [];

    // Hasil JOIN berisi satu baris per brand; baris pertama sebuah kategori membuat entri kategori,
    // baris berikutnya menambahkan brand ke array "brands" milik kategori tersebut.
    while ($row = mysqli_fetch_assoc($query)) {
        $id = (int)$row['category_id'];
        if (!isset($data[$id])) {
            $data[$id] = [
                'category_id'    => $id,
                'category_name'  => $row['category_name'],
                'cat_description'=> $row['cat_description'],
                'image'          => $row['image'],
                'brands'         => [],
            ];
        }
        if (!empty($row['brand_id'])) {
            $data[$id]['brands'][] = [
                'brand_id'   => (int)$row['brand_id'],
                'brand_name' => $row['brand_name'],
                'brand_slug' => $row['brand_slug'],
            ];
        }
    }

    return array_values($data);
}

/**
 * Mengambil kategori yang memiliki brand tertentu.
 */
function ambil_kategori_by_brand($conn, $slug) {
    // Cari brand lebih dulu untuk mendapatkan brand_id, lalu ambil kategori yang terhubung di pivot.
    $brand = ambil_brand_by_slug($conn, $slug);
    if (!$brand) {
        return [];
    }
    $brand_id = (int)$brand['brand_id'];
    $sql = "SELECT c.*
            FROM categories c
            INNER JOIN category_brands cb ON c.category_id = cb.category_id
            WHERE cb.brand_id = $brand_id
            ORDER BY c.category_id ASC";
    $query = mysqli_query($conn, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    return $data;
}
