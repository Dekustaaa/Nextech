<?php
/**
 * Query autentikasi dan guard akses.
 *
 * Berisi pencarian user, registrasi, perubahan akun, serta cek_login() dan
 * cek_admin() yang dipakai banyak action. SQL dan aturan akses dikumpulkan di
 * sini agar tidak diduplikasi di handler maupun view.
 */
function ambil_user_by_email($conn, $email) {
    // Email unik, jadi LIMIT 1 cukup. Placeholder "?" membuat input tidak masuk mentah ke SQL.
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email=? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row;
}

function daftar_user($conn, $nama, $email, $password) {
    // role_id = 2 adalah pelanggan. Password disimpan sesuai input karena data contoh belum memakai hashing.
    $stmt = mysqli_prepare($conn, "INSERT INTO users (role_id, full_name, email, password_hash) VALUES (2, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $nama, $email, $password);
    $ok = mysqli_stmt_execute($stmt);
    $id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);
    if ($ok) {
        // Buat baris profil kosong agar update profil nanti cukup UPDATE, bukan INSERT baru.
        $stmt2 = mysqli_prepare($conn, "INSERT INTO user_profiles (user_id) VALUES (?)");
        mysqli_stmt_bind_param($stmt2, "i", $id);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_close($stmt2);
    }
    return $ok;
}

function ambil_user_by_id($conn, $id) {
    $id = (int)$id;
    $query = mysqli_query($conn, "SELECT * FROM users WHERE user_id = $id");
    return mysqli_fetch_assoc($query);
}

function ubah_akun($conn, $id, $nama, $email, $telepon, $password) {
    $id = (int)$id;
    // Password opsional: jika kosong, query tidak menyertakan kolom password_hash.
    if ($password != "") {
        $stmt = mysqli_prepare($conn, "UPDATE users SET full_name=?, email=?, phone_number=?, password_hash=? WHERE user_id=?");
        mysqli_stmt_bind_param($stmt, "ssssi", $nama, $email, $telepon, $password, $id);
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE users SET full_name=?, email=?, phone_number=? WHERE user_id=?");
        mysqli_stmt_bind_param($stmt, "sssi", $nama, $email, $telepon, $id);
    }
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
}

function cek_admin() {
    // Guard admin: session wajib punya user_id dan role_id = 1.
    if (!isset($_SESSION["user_id"]) || ($_SESSION["role_id"] ?? 0) != 1) {
        // Redirect + exit menghentikan action yang memanggil fungsi ini.
        header("Location: /PWL-SAS/views/admin/login.php");
        exit;
    }
}

function cek_login() {
    // Guard login: cukup memastikan user_id ada; role tidak dibatasi di sini.
    if (!isset($_SESSION["user_id"])) {
        // Flash message menjelaskan alasan user diarahkan ke halaman login.
        $_SESSION["auth_pesan"] = "Silakan login dulu untuk mengakses halaman ini.";
        header("Location: /PWL-SAS/views/auth/login.php");
        exit;
    }
}
