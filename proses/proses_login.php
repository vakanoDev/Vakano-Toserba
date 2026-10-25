<?php
require '../config/koneksi.php';
session_start();

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM user WHERE username = '$email'");
    if (mysqli_num_rows($query) === 1) {
        $data = mysqli_fetch_assoc($query);
        
        if (hash('sha256', $password) === $data['password']) {
            $_SESSION['iduser'] = $data['iduser'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['idrole'] = $data['idrole'];

            // Cek apakah yang login adalah Admin (idrole = 1)
            if ($data['idrole'] == 1) {
                echo "<script>alert('Login berhasil! Selamat datang Admin, {$data['username']}'); window.location='../admin/dashboard_admin.php';</script>";
                exit;
            } else {
                // Jika bukan admin, arahkan ke beranda atau halaman lain
                echo "<script>alert('Login berhasil! Selamat datang, {$data['username']}'); window.location='../index.php';</script>";
                exit;
            }
        } else {
            echo "<script>alert('Password salah!'); window.location='../login.php';</script>";
        }
    } else {
        echo "<script>alert('Email/Username tidak ditemukan!'); window.location='../login.php';</script>";
    }
}
?>