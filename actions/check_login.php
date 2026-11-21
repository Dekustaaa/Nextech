<?php
/**
 * Pemeriksaan login berbasis session.
 *
 * File ini di-require oleh halaman yang membutuhkan user login. Jika session
 * user_id belum ada, request dialihkan ke halaman login lalu dihentikan.
 * Dipisahkan agar aturan guard tidak ditulis ulang di setiap view.
 */
session_start();
// user_id hanya diisi oleh actions/login.php setelah kredensial terbukti valid.
if (!isset($_SESSION["user_id"])) {
    header("Location: /PWL-SAS/views/auth/login.php");
    // exit penting: tanpa ini kode halaman pemanggil tetap berjalan setelah redirect.
    exit;
}
