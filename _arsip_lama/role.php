<?php
require '../config/koneksi.php';
/** @var mysqli $conn */
session_start();

if (!isset($_SESSION['iduser']) || $_SESSION['idrole'] != 1) {
    echo "<script>alert('Akses ditolak!'); window.location='../login.php';</script>";
    exit;
}

$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : 'tampil';

if ($aksi == 'hapus') {
    $id = $_GET['id'];
    mysqli_query($conn, "DELETE FROM role WHERE idrole='$id'");
    echo "<script>alert('Data Role berhasil dihapus!'); window.location='role.php';</script>";
    exit;
}

if (isset($_POST['simpan_role'])) {
    $idrole = $_POST['idrole'];
    $nama_role = mysqli_real_escape_string($conn, $_POST['nama_role']);

    if (empty($idrole)) {
        mysqli_query($conn, "INSERT INTO role (nama_role) VALUES ('$nama_role')");
    } else {
        mysqli_query($conn, "UPDATE role SET nama_role='$nama_role' WHERE idrole='$idrole'");
    }
    echo "<script>alert('Data Role berhasil disimpan!'); window.location='role.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Role - Vakano Toserba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #0b0f19; font-family: 'Poppins', sans-serif; color: #fff; }
        .sidebar { background: rgba(15, 23, 42, 0.95); min-height: 100vh; border-right: 1px solid rgba(255, 255, 255, 0.1); }
        .nav-link { color: #94a3b8; border-radius: 10px; margin-bottom: 5px; transition: all 0.3s ease; }
        .nav-link:hover, .nav-link.active { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; }
        .card-custom { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 15px; }
        .table-dark-custom { color: #e2e8f0; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 sidebar p-4 d-flex flex-column justify-content-between">
                <div>
                    <h5 class="fw-bold text-warning mb-4"><i class="fa-solid fa-store me-2"></i>Vakano Admin</h5>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item"><a href="dashboard_admin.php" class="nav-link"><i class="fa-solid fa-house me-2"></i> Beranda Admin</a></li>
                        <li class="nav-item mt-2 mb-1"><small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Menu Master</small></li>
                        <li><a href="role.php" class="nav-link active"><i class="fa-solid fa-user-shield me-2"></i> Manajemen Role</a></li>
                        <li><a href="barang.php" class="nav-link"><i class="fa-solid fa-boxes-stacked me-2"></i> Data Barang</a></li>
                        <li><a href="vendor.php" class="nav-link"><i class="fa-solid fa-truck-field me-2"></i> Data Vendor</a></li>
                        <li><a href="pegawai.php" class="nav-link"><i class="fa-solid fa-users me-2"></i> Data Pegawai</a></li>
                    </ul>
                </div>
                <div class="pt-3 border-top border-secondary border-opacity-25">
                    <span class="d-block small text-muted mb-2">Admin: <b><?= $_SESSION['username']; ?></b></span>
                    <a href="../logout.php" class="btn btn-danger btn-sm w-100 rounded-pill fw-bold"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 p-4 p-md-5">
                <?php if ($aksi == 'tampil') { 
                    $data_role = mysqli_query($conn, "SELECT * FROM role");
                ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Manajemen Role</h2>
                        <a href="role.php?aksi=form" class="btn btn-success fw-bold"><i class="fa-solid fa-plus me-1"></i> Tambah Role</a>
                    </div>
                    <div class="card card-custom p-4">
                        <table class="table table-dark table-hover align-middle">
                            <thead>
                                <tr><th>ID Role</th><th>Nama Role</th><th class="text-center">Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php while ($r = mysqli_fetch_assoc($data_role)) : ?>
                                <tr>
                                    <td><?= $r['idrole']; ?></td>
                                    <td><span class="badge bg-primary fs-6"><?= $r['nama_role']; ?></span></td>
                                    <td class="text-center">
                                        <a href="role.php?aksi=form&id=<?= $r['idrole']; ?>" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i></a>
                                        <a href="role.php?aksi=hapus&id=<?= $r['idrole']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus role ini?');"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php } elseif ($aksi == 'form') { 
                    $idrole = ''; $nama_role = ''; $judul = 'Tambah Role Baru';
                    if (isset($_GET['id'])) {
                        $idrole = $_GET['id']; $judul = 'Edit Data Role';
                        $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM role WHERE idrole = '$idrole'"));
                        $nama_role = $data['nama_role'];
                    }
                ?>
                    <div class="mb-4"><h2><?= $judul; ?></h2></div>
                    <div class="card card-custom p-4" style="max-width: 500px;">
                        <form action="role.php" method="POST">
                            <input type="hidden" name="idrole" value="<?= $idrole; ?>">
                            <div class="mb-3">
                                <label class="form-label text-muted fw-bold">Nama Role</label>
                                <input type="text" name="nama_role" class="form-control" value="<?= $nama_role; ?>" required>
                            </div>
                            <button type="submit" name="simpan_role" class="btn btn-primary fw-bold"><i class="fa-solid fa-save me-1"></i> Simpan</button>
                            <a href="role.php" class="btn btn-secondary fw-bold">Batal</a>
                        </form>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>