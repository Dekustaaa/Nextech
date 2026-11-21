<?php
/**
 * Handler penyimpanan rating dan ulasan produk.
 *
 * Menerima POST dari halaman detail produk, memastikan user sudah login,
 * memvalidasi nilai bintang lewat validations.php, lalu memakai
 * repositories/ratings.php untuk menyimpan (insert atau update). Setelah itu
 * user dikembalikan ke halaman produk dengan flash message.
 */
session_start();
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../repositories/ratings.php";
require_once __DIR__ . "/../validations.php";

// Rating harus terikat ke akun; cek_login() akan redirect jika session belum ada.
cek_login();

// product_id juga dipakai untuk menyusun URL kembali ke detail produk.
$product_id = input_angka($_POST, "product_id");
$rating = input_angka($_POST, "rating");
$review = input_teks($_POST, "review");
$balik = "/PWL-SAS/views/products/laptop.php?id=" . $product_id;

// Tanpa produk yang valid tidak ada rating yang bisa disimpan.
if (!validasi_id($product_id)) {
    header("Location: /PWL-SAS/mainpage.php");
    exit;
}

// Batasi nilai bintang 1-5 karena angka dari form bisa dimanipulasi lewat browser.
$error = validasi_rating($_POST);
if ($error !== null) {
    $_SESSION["rating_pesan"] = $error;
    $_SESSION["rating_status"] = "error";
    header("Location: " . $balik);
    exit;
}

// simpan_rating() meng-upsert: update bila user sudah pernah menilai produk ini, insert bila belum.
if (simpan_rating($conn, $product_id, $_SESSION["user_id"], $rating, $review)) {
    $_SESSION["rating_pesan"] = "Terima kasih, rating kamu sudah disimpan.";
    $_SESSION["rating_status"] = "ok";
} else {
    $_SESSION["rating_pesan"] = "Rating gagal disimpan.";
    $_SESSION["rating_status"] = "error";
}

header("Location: " . $balik);
exit;
