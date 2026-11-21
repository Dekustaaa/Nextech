<?php
/**
 * Handler checkout alternatif.
 *
 * File ini merespons submit pembayaran secara langsung: mengambil metode dari
 * POST, mengosongkan kedua keranjang di session, lalu menampilkan konfirmasi.
 * Tidak ada penulisan ke tabel orders/payments di sini; alur utama saat ini
 * memakai actions/payment.php. Tetap dipisah dari view agar view checkout hanya
 * menampilkan ringkasan dan form.
 */
session_start();

// htmlspecialchars dipakai karena nilai metode ikut dicetak ke dalam JavaScript di bawah.
$metode = htmlspecialchars($_POST['metode'] ?? 'Kartu kredit');

// Kosongkan keranjang setelah bayar
$_SESSION['cart']    = [];
$_SESSION['layanan'] = [];

// Karena tidak memakai header(), konfirmasi dikirim sebagai script alert lalu memindahkan browser.
echo "<script>
    alert('Pembayaran dengan $metode berhasil! Terima kasih.');
    window.location = '../mainpage.php';
</script>";