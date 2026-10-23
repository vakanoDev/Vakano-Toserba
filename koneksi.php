<?php
$host = "localhost";
$user = "root";       // Sesuaikan dengan user database kamu
$pass = "";           // Sesuaikan jika ada password
$db   = "DB_UTS_PBD_DEVA";   // Ganti dengan nama database proyekmu

$conn = mysqli_connect("localhost", "root", "", "db_uts_pbd_deva");

// Tambahkan baris ini untuk mematikan ONLY_FULL_GROUP_BY
mysqli_query($conn, "SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
