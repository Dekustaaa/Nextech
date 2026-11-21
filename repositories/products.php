<?php
/**
 * Query produk untuk katalog dan panel admin.
 *
 * Berisi pembacaan produk (semua, per id, per keyword, per kategori/brand,
 * per budget), pencarian produk termurah, serta tambah/ubah/hapus. Semua query
 * produk dikumpulkan di sini agar halaman katalog, rekomendasi, dan admin
 * memakai aturan data yang sama.
 */
function ambil_produk($conn) {
    // LEFT JOIN dipakai agar produk tetap tampil walau kategori/brand-nya kosong.
    $sql = "SELECT products.*, categories.category_name, categories.image, categories.cat_description,
                   brands.brand_name, brands.brand_slug
            FROM products
            LEFT JOIN categories ON products.category_id = categories.category_id
            LEFT JOIN brands ON products.brand_id = brands.brand_id
            ORDER BY products.product_id ASC";
    $result = mysqli_query($conn, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

function ambil_produk_by_id($conn, $id) {
    // ID hasil cast dipakai untuk halaman detail produk dan pengambilan item cart.
    $id = (int)$id;
    $sql = "SELECT products.*, categories.category_name, categories.image, categories.cat_description,
                   brands.brand_name, brands.brand_slug
            FROM products
            LEFT JOIN categories ON products.category_id = categories.category_id
            LEFT JOIN brands ON products.brand_id = brands.brand_id
            WHERE products.product_id = $id";
    $query = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($query);
}

function ambil_produk_by_keyword($conn, $keyword) {
    $keyword = trim($keyword);
    if ($keyword === "") {
        return ambil_produk($conn);
    }
    // %...% membuat pencarian bersifat "mengandung"; empat placeholder mewakili nama, deskripsi, kategori, dan brand.
    $like = "%" . $keyword . "%";
    $stmt = mysqli_prepare($conn, "SELECT products.*, categories.category_name, categories.image, categories.cat_description,
                   brands.brand_name, brands.brand_slug
            FROM products
            LEFT JOIN categories ON products.category_id = categories.category_id
            LEFT JOIN brands ON products.brand_id = brands.brand_id
            WHERE products.product_name LIKE ? OR products.description LIKE ? OR categories.category_name LIKE ? OR brands.brand_name LIKE ?
            ORDER BY products.product_id ASC");
    mysqli_stmt_bind_param($stmt, "ssss", $like, $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $data;
}

function ambil_produk_by_kategori($conn, $kategori, $brand_slug = "") {
    $kategori = (int)$kategori;
    $brand_slug = trim($brand_slug);

    // Filter brand opsional, jadi SQL disusun bertahap dan tipe bind menyesuaikan ("i" saja atau "is").
    $sql = "SELECT products.*, categories.category_name, categories.image, categories.cat_description,
                   brands.brand_name, brands.brand_slug
            FROM products
            LEFT JOIN categories ON products.category_id = categories.category_id
            LEFT JOIN brands ON products.brand_id = brands.brand_id
            WHERE products.category_id = ?";
    if ($brand_slug !== "") {
        $sql .= " AND brands.brand_slug = ?";
    }
    $sql .= " ORDER BY products.product_id ASC";

    $stmt = mysqli_prepare($conn, $sql);
    if ($brand_slug !== "") {
        mysqli_stmt_bind_param($stmt, "is", $kategori, $brand_slug);
    } else {
        mysqli_stmt_bind_param($stmt, "i", $kategori);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $data;
}

function hapus_produk($conn, $id) {
    $id = (int)$id;
    $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE product_id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function ambil_produk_by_budget($conn, $budget, $kategori) {
    // Filter harga maksimum; kategori 0 berarti semua kategori. Urutkan termahal agar rekomendasi mendekati budget.
    $budget = (int)$budget;
    $kategori = (int)$kategori;
    if ($kategori > 0) {
        $sql = "SELECT * FROM products WHERE price <= $budget AND category_id = $kategori ORDER BY price DESC";
    } else {
        $sql = "SELECT * FROM products WHERE price <= $budget ORDER BY price DESC";
    }
    $query = mysqli_query($conn, $sql);
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    return $data;
}

function ambil_produk_termurah($conn, $kategori) {
    // LIMIT 1 setelah ORDER BY price ASC mengambil produk paling murah, dengan filter kategori opsional.
    $kategori = (int)$kategori;
    if ($kategori > 0) {
        $sql = "SELECT * FROM products WHERE category_id = $kategori ORDER BY price ASC LIMIT 1";
    } else {
        $sql = "SELECT * FROM products ORDER BY price ASC LIMIT 1";
    }
    $query = mysqli_query($conn, $sql);
    return mysqli_fetch_assoc($query);
}

function simpan_produk($conn, $nama, $kategori, $harga, $stok, $deskripsi, $url, $badge) {
    // Tipe bind "isiisss" mengikuti urutan kolom: int, string, int, int, string, string, string.
    $kategori = (int)$kategori;
    $harga = (int)$harga;
    $stok = (int)$stok;
    $stmt = mysqli_prepare($conn, "INSERT INTO products (category_id, product_name, price, stock, description, url, badge) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isiisss", $kategori, $nama, $harga, $stok, $deskripsi, $url, $badge);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function ubah_produk($conn, $id, $nama, $kategori, $harga, $stok, $deskripsi, $url, $badge) {
    // Sama seperti simpan_produk, tetapi ada tambahan id integer di akhir untuk WHERE.
    $id = (int)$id;
    $kategori = (int)$kategori;
    $harga = (int)$harga;
    $stok = (int)$stok;
    $stmt = mysqli_prepare($conn, "UPDATE products SET category_id=?, product_name=?, price=?, stock=?, description=?, url=?, badge=? WHERE product_id=?");
    mysqli_stmt_bind_param($stmt, "isiisssi", $kategori, $nama, $harga, $stok, $deskripsi, $url, $badge, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
