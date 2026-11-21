<?php
/**
 * Query kategori produk.
 *
 * Berisi pembacaan daftar kategori dan operasi tambah/ubah/hapus yang dipakai
 * action admin. Query dikumpulkan di sini agar halaman kategori tidak menulis
 * SQL sendiri dan aturan data tetap konsisten.
 */
function ambil_kategori($conn) {
    $query = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_id ASC");
    $data = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $data[] = $row;
    }
    return $data;
}

function ambil_kategori_by_id($conn, $id) {
    // Cast ke int membuat nilai aman disisipkan ke query untuk pencarian satu baris.
    $id = (int)$id;
    $query = mysqli_query($conn, "SELECT * FROM categories WHERE category_id = $id");
    return mysqli_fetch_assoc($query);
}

function simpan_kategori($conn, $nama, $deskripsi, $image) {
    // Prepared statement karena ketiga nilai berasal dari form admin.
    $stmt = mysqli_prepare($conn, "INSERT INTO categories (category_name, cat_description, image) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $nama, $deskripsi, $image);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function ubah_kategori($conn, $id, $nama, $deskripsi, $image) {
    // Urutan bind: tiga string lalu satu integer (id) sesuai urutan placeholder.
    $id = (int)$id;
    $stmt = mysqli_prepare($conn, "UPDATE categories SET category_name=?, cat_description=?, image=? WHERE category_id=?");
    mysqli_stmt_bind_param($stmt, "sssi", $nama, $deskripsi, $image, $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function hapus_kategori($conn, $id) {
    // Hapus berdasarkan satu ID yang sudah dipastikan integer oleh action pemanggil.
    $id = (int)$id;
    $stmt = mysqli_prepare($conn, "DELETE FROM categories WHERE category_id=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}
