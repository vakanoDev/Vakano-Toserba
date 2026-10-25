<?php
require '../config/koneksi.php';
/** @var mysqli $conn */


if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $idrole = $_POST['idrole'];
    $password = $_POST['password'];
    $verifpw = $_POST['verifpw'];

    // 1. Validasi konfirmasi password
    if ($password !== $verifpw) {
        echo "<script>alert('Konfirmasi password tidak sesuai!'); window.location='../registrasi.php';</script>";
        exit;
    }

    // 2. Cek apakah email/username sudah terdaftar di database
    $cek_user = mysqli_query($conn, "SELECT * FROM user WHERE username = '$email'");
    if (mysqli_num_rows($cek_user) > 0) {
        echo "<script>alert('Email/Username sudah terdaftar! Gunakan yang lain.'); window.location='../registrasi.php';</script>";
        exit;
    }

    // 3. Enkripsi password menggunakan SHA256 (sesuai format database kamu)
    $hashed_password = hash('sha256', $password);

    // 4. Masukkan data ke tabel user
    $sql = "INSERT INTO user (username, password, idrole) VALUES ('$email', '$hashed_password', '$idrole')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Registrasi berhasil! Data telah tercatat pada sistem. Silakan Login.'); window.location='../login.php';</script>";
    } else {
        echo "<script>alert('Gagal registrasi: " . mysqli_error($conn) . "'); window.location='../registrasi.php';</script>";
    }
} else {
    // Jika diakses secara langsung tanpa tombol submit, kembalikan ke halaman registrasi
    header("Location: ../registrasi.php");
    exit;
}
?>