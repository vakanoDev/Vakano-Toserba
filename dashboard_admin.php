<?php
require 'koneksi.php';
session_start();

if (!isset($_SESSION['iduser']) || $_SESSION['idrole'] != 1) {
    echo "<script>alert('Akses ditolak!'); window.location='login.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Vakano Toserba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #0b0f19; font-family: 'Poppins', sans-serif; color: #fefefe; }
        .sidebar { background: rgba(15, 23, 42, 0.95); min-height: 100vh; border-right: 1px solid rgba(244, 238, 238, 0.1); }
        .nav-link { color: #f7f7f7; border-radius: 10px; margin-bottom: 5px; transition: all 0.3s ease; }
        .nav-link:hover, .nav-link.active { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; }
        .card-custom { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(244, 237, 237, 0.1); border-radius: 15px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- SIDEBAR -->
            <div class="col-md-3 col-lg-2 sidebar p-4 d-flex flex-column justify-content-between">
                <div>
                    <h5 class="fw-bold text-warning mb-4"><img src="LogoBerandaUner.png" class="img-fluid me-2" style="max-width: 30px;">Vakano Admin</h5>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="dashboard_admin.php" class="nav-link active"><i class="fa-solid fa-house me-2"></i> Beranda Admin</a>
                        </li>
                        <li class="nav-item mt-2 mb-1"><small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Menu Master</small></li>
                        <li><a href="role.php" class="nav-link"><i class="fa-solid fa-user-shield me-2"></i> Manajemen Role</a></li>
                        <li><a href="barang.php" class="nav-link"><i class="fa-solid fa-boxes-stacked me-2"></i> Data Barang</a></li>
                        <li><a href="vendor.php" class="nav-link"><i class="fa-solid fa-truck-field me-2"></i> Data Vendor</a></li>
                        <li><a href="pegawai.php" class="nav-link"><i class="fa-solid fa-users me-2"></i> Data Pegawai</a></li>
                    </ul>
                </div>
                <div class="pt-3 border-top border-secondary border-opacity-25">
                    <span class="d-block small text-muted mb-2">Admin: <b><?= $_SESSION['username']; ?></b></span>
                    <a href="logout.php" class="btn btn-danger btn-sm w-100 rounded-pill fw-bold"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
                </div>
            </div>

            <!-- KONTEN -->
            <div class="col-md-9 col-lg-10 p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard Utama</h2>
                </div>
                <div class="card card-custom p-5 text-center">
                    <h3 class="text-primary fw-bold">Selamat Datang, <?= $_SESSION['username']; ?>!</h3>
                    <i class="text-white">Selamat Bertugas dan SEMANGAT!!!!</i>
                    <img src="LogoBerandaUner.png" alt="Logo Vakano" class="img-fluid mt-4 mb-4 mx-auto d-block" style="max-width: 150px;">
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>