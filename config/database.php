<?php
/**
 * Koneksi database Nexatech.
 *
 * Action dan view yang membutuhkan database memakai require_once file ini agar
 * kredensial hanya ditulis di satu tempat.
 */
$host = "localhost";
$root = "root";
$pass = "";
$dbname = "techhub";

$conn = mysqli_connect($host, $root, $pass, $dbname);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
