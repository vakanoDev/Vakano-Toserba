<?php
/** @var mysqli $conn */

$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_uts_pbd_deva";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// Matikan ONLY_FULL_GROUP_BY (dibutuhkan role_vu yang GROUP BY nama_role saja)
mysqli_query($conn, "SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");