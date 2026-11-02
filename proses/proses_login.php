<?php
require '../config/koneksi.php';
/** @var mysqli $conn */
session_start();

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $st = $conn->prepare("SELECT iduser, username, password, idrole FROM akun_form_vu WHERE username = ?");
    $st->bind_param('s', $email);
    $st->execute();
    $query = $st->get_result();
    if ($query->num_rows === 1) {
        $data = $query->fetch_assoc();
        
        if (hash('sha256', $password) === $data['password']) {
            $_SESSION['iduser'] = $data['iduser'];
            $_SESSION['username'] = $data['username'];
            $_SESSION['idrole'] = $data['idrole'];

            // Cek apakah yang login adalah Admin (idrole = 1)
            if ($data['idrole'] == 1) {
                echo "<script>alert('Login berhasil! Selamat datang Admin, {$data['username']}'); window.location='../admin/dashboard_admin.php';</script>";
                exit;
            } elseif ($data['idrole'] == 7) {
                // HRD (idrole = 7) -> dashboard HRD
                echo "<script>alert('Login berhasil! Selamat datang HRD, {$data['username']}'); window.location='../hrd/dashboard_hrd.php';</script>";
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