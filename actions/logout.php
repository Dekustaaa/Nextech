<?php
/**
 * Handler logout.
 *
 * Menghapus seluruh isi session lalu menghancurkannya di server, kemudian
 * mengembalikan user ke halaman login. Dipisahkan dari view agar tombol logout
 * cukup berupa link ke file ini dan tidak ada HTML yang perlu dirender.
 */
session_start();
// Kosongkan array session dan hancurkan session server agar cookie lama tidak lagi valid.
$_SESSION = [];
session_destroy();
// Redirect + exit mencegah proses logout diulang saat halaman di-refresh.
header("Location: /PWL-SAS/views/auth/login.php");
exit;
