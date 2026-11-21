<?php
/**
 * Handler operasi keranjang berbasis session.
 *
 * Dipanggil dari link +, -, dan hapus di halaman katalog, detail produk, serta
 * cart. File ini hanya mengubah jumlah di session lalu mengembalikan user ke
 * halaman asal, sehingga view tidak perlu memproses aksi keranjang sendiri.
 */
session_start();
require_once __DIR__ . "/../repositories/auth.php";
require_once __DIR__ . "/../validations.php";

// Keranjang hanya untuk user login; require_login() akan redirect jika session kosong.
require_login();

// Pastikan wadah session sudah ada sebelum diubah, supaya tidak muncul notice.
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}
if (!isset($_SESSION["layanan"])) {
    $_SESSION["layanan"] = [];
}

// Aksi dan jenis datang dari query string link di halaman katalog/cart.
$aksi = input_text($_GET, "aksi");
$jenis = input_text($_GET, "jenis");
$id = input_number($_GET, "id");

// Satu session menampung dua keranjang terpisah: "cart" untuk produk, "layanan" untuk jasa.
if ($jenis == "layanan") {
    $key = "layanan";
} else {
    $key = "cart";
}

if (validate_id($id)) {
    // Session hanya menyimpan id => jumlah, bukan harga; total dihitung ulang saat cart dibaca.
    if ($aksi == "tambah") {
        if (isset($_SESSION[$key][$id])) {
            $_SESSION[$key][$id]++;
        } else {
            $_SESSION[$key][$id] = 1;
        }
    }
    if ($aksi == "kurang") {
        if (isset($_SESSION[$key][$id])) {
            // Jumlah yang turun sampai 0 atau kurang langsung dibuang agar tidak ada baris qty 0.
            $_SESSION[$key][$id]--;
            if ($_SESSION[$key][$id] <= 0) {
                unset($_SESSION[$key][$id]);
            }
        }
    }
    if ($aksi == "hapus") {
        unset($_SESSION[$key][$id]);
    }
}

// Kembali ke halaman asal agar user tidak kehilangan konteks; fallback ke mainpage bila referer tidak ada.
$kembali = $_SERVER["HTTP_REFERER"] ?? "/PWL-SAS/mainpage.php";
header("Location: " . $kembali);
exit;
