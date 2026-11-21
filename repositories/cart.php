<?php
/**
 * Helper keranjang berbasis session.
 *
 * Session hanya menyimpan product_id/service_id dan jumlahnya. File ini
 * mengambil detail terbaru dari database, menghitung subtotal, lalu merangkum
 * total produk dan layanan untuk halaman cart/checkout. Pemisahan ini menjaga
 * view tetap bebas dari query dan perhitungan.
 */
function siapkan_cart() {
    // Pastikan kedua key session ada supaya loop berikutnya tidak error saat cart masih kosong.
    if (!isset($_SESSION["cart"])) {
        $_SESSION["cart"] = [];
    }
    if (!isset($_SESSION["layanan"])) {
        $_SESSION["layanan"] = [];
    }
}

function ambil_isi_cart($conn) {
    $isi = [];
    foreach ($_SESSION["cart"] as $id => $jumlah) {
        $id = (int)$id;
        // Ambil ulang produk dari database; jika sudah dihapus, lewati agar cart tidak menampilkan data rusak.
        $produk = ambil_produk_by_id($conn, $id);
        if (!$produk) {
            continue;
        }
        // Subtotal dihitung dari harga terbaru, bukan harga saat item dimasukkan ke cart.
        $subtotal = $produk["price"] * $jumlah;
        $isi[] = [
            "jenis" => "produk",
            "id" => $id,
            "nama" => $produk["product_name"],
            "deskripsi" => $produk["description"],
            "gambar" => $produk["url"],
            "harga" => $produk["price"],
            "qty" => $jumlah,
            "subtotal" => $subtotal
        ];
    }
    foreach ($_SESSION["layanan"] as $id => $jumlah) {
        $id = (int)$id;
        // Layanan diperlakukan sama seperti produk: detail dan harga selalu diambil ulang dari database.
        $layanan = ambil_service_by_id($conn, $id);
        if (!$layanan) {
            continue;
        }
        $subtotal = $layanan["base_price"] * $jumlah;
        $isi[] = [
            "jenis" => "layanan",
            "id" => $id,
            "nama" => $layanan["service_name"],
            "deskripsi" => $layanan["description"],
            "gambar" => "",
            "harga" => $layanan["base_price"],
            "qty" => $jumlah,
            "subtotal" => $subtotal
        ];
    }
    return $isi;
}

function hitung_cart($isi) {
    $produk = 0;
    $layanan = 0;
    $jml_produk = 0;
    $jml_layanan = 0;
    // Pisahkan total produk dan layanan agar checkout bisa menampilkan rincian; total adalah gabungan keduanya.
    foreach ($isi as $item) {
        if ($item["jenis"] == "produk") {
            $produk += $item["subtotal"];
            $jml_produk++;
        } else {
            $layanan += $item["subtotal"];
            $jml_layanan++;
        }
    }
    return [
        "produk" => $produk,
        "layanan" => $layanan,
        "total" => $produk + $layanan,
        "jml_produk" => $jml_produk,
        "jml_layanan" => $jml_layanan
    ];
}
