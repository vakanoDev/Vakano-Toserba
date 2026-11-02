<?php
require '../config/koneksi.php';
/** @var mysqli $conn */

if (isset($_POST['register'])) {
    $email = trim($_POST['email']);
    $idrole = $_POST['idrole'];
    $password = $_POST['password'];
    $verifpw = $_POST['verifpw'];

    // 1. Validasi konfirmasi password
    if ($password !== $verifpw) {
        echo "<script>alert('Konfirmasi password tidak sesuai!'); window.location='../registrasi.php';</script>";
        exit;
    }

    // 2. Jabatan Admin tidak boleh didaftarkan lewat form ini
    if ($idrole == 1) {
        echo "<script>alert('Jabatan yang dipilih tidak valid!'); window.location='../registrasi.php';</script>";
        exit;
    }

    // 3. Cek apakah email/username sudah terdaftar
    $st = $conn->prepare("SELECT iduser FROM akun_form_vu WHERE username = ?");
    $st->bind_param('s', $email);
    $st->execute();
    if ($st->get_result()->num_rows > 0) {
        echo "<script>alert('Email/Username sudah terdaftar! Gunakan yang lain.'); window.location='../registrasi.php';</script>";
        exit;
    }

    // 4. Enkripsi password menggunakan SHA256 (sesuai format database kamu)
    $hashed_password = hash('sha256', $password);

    // 5. Masukkan data ke tabel user (lewat view akun_form_vu)
    $st = $conn->prepare("INSERT INTO akun_form_vu (username, password, idrole) VALUES (?, ?, ?)");
    $st->bind_param('sss', $email, $hashed_password, $idrole);

    if ($st->execute()) {
        echo "<script>alert('Registrasi berhasil! Data telah tercatat pada sistem. Silakan Login.'); window.location='../login.php';</script>";
    } else {
        echo "<script>alert('Gagal registrasi: " . addslashes($st->error) . "'); window.location='../registrasi.php';</script>";
    }
} else {
    // Jika diakses secara langsung tanpa tombol submit, kembalikan ke halaman registrasi
    header("Location: ../registrasi.php");
    exit;
}
?>