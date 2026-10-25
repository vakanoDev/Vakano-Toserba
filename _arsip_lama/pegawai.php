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
    mysqli_query($conn, "DELETE FROM user WHERE iduser='$id'");
    echo "<script>alert('Data Pegawai berhasil dihapus!'); window.location='pegawai.php';</script>";
    exit;
}

if (isset($_POST['simpan_pegawai'])) {
    $iduser = $_POST['iduser'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $idrole = $_POST['idrole'];
    $password_input = $_POST['password'];

    if (empty($iduser)) {
        // Mode Tambah
        $hashed_password = hash('sha256', $password_input);
        mysqli_query($conn, "INSERT INTO user (username, password, idrole) VALUES ('$username', '$hashed_password', '$idrole')");
    } else {
        // Mode Edit
        if (empty($password_input)) {
            // Jika password kosong, jangan update passwordnya
            mysqli_query($conn, "UPDATE user SET username='$username', idrole='$idrole' WHERE iduser='$iduser'");
        } else {
            // Jika password diisi, update passwordnya
            $hashed_password = hash('sha256', $password_input);
            mysqli_query($conn, "UPDATE user SET username='$username', password='$hashed_password', idrole='$idrole' WHERE iduser='$iduser'");
        }
    }
    echo "<script>alert('Data Pegawai berhasil disimpan!'); window.location='pegawai.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pegawai - Vakano Toserba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background: #0b0f19; font-family: 'Poppins', sans-serif; color: #fff; }
        .sidebar { background: rgba(15, 23, 42, 0.95); min-height: 100vh; border-right: 1px solid rgba(255, 255, 255, 0.1); }
        .nav-link { color: #94a3b8; border-radius: 10px; margin-bottom: 5px; transition: all 0.3s ease; }
        .nav-link:hover, .nav-link.active { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; }
        .card-custom { background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 15px; }
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
                        <li><a href="role.php" class="nav-link"><i class="fa-solid fa-user-shield me-2"></i> Manajemen Role</a></li>
                        <li><a href="barang.php" class="nav-link"><i class="fa-solid fa-boxes-stacked me-2"></i> Data Barang</a></li>
                        <li><a href="vendor.php" class="nav-link"><i class="fa-solid fa-truck-field me-2"></i> Data Vendor</a></li>
                        <li><a href="pegawai.php" class="nav-link active"><i class="fa-solid fa-users me-2"></i> Data Pegawai</a></li>
                    </ul>
                </div>
                <div class="pt-3 border-top border-secondary border-opacity-25">
                    <span class="d-block small text-muted mb-2">Admin: <b><?= $_SESSION['username']; ?></b></span>
                    <a href="../logout.php" class="btn btn-danger btn-sm w-100 rounded-pill fw-bold"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 p-4 p-md-5">
                <?php if ($aksi == 'tampil') { 
                    $data_pegawai = mysqli_query($conn, "SELECT user.*, role.nama_role FROM user LEFT JOIN role ON user.idrole = role.idrole");
                ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Data Pegawai / Akun User</h2>
                        <a href="pegawai.php?aksi=form" class="btn btn-warning fw-bold text-dark"><i class="fa-solid fa-user-plus me-1"></i> Tambah Akun</a>
                    </div>
                    <div class="card card-custom p-4">
                        <table class="table table-dark table-hover align-middle">
                            <thead>
                                <tr><th>ID</th><th>Username / Email</th><th>Role Akses</th><th class="text-center">Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php while ($p = mysqli_fetch_assoc($data_pegawai)) : ?>
                                <tr>
                                    <td><?= $p['iduser']; ?></td>
                                    <td class="fw-bold"><?= $p['username']; ?></td>
                                    <td><span class="badge bg-info text-dark"><?= $p['nama_role']; ?></span></td>
                                    <td class="text-center">
                                        <a href="pegawai.php?aksi=form&id=<?= $p['iduser']; ?>" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i></a>
                                        <a href="pegawai.php?aksi=hapus&id=<?= $p['iduser']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus akun ini?');"><i class="fa-solid fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php } elseif ($aksi == 'form') { 
                    $iduser = ''; $username = ''; $idrole = ''; $judul = 'Tambah Akun Pegawai';
                    if (isset($_GET['id'])) {
                        $iduser = $_GET['id']; $judul = 'Edit Data Pegawai';
                        $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM user WHERE iduser = '$iduser'"));
                        $username = $data['username']; $idrole = $data['idrole'];
                    }
                ?>
                    <div class="mb-4"><h2><?= $judul; ?></h2></div>
                    <div class="card card-custom p-4" style="max-width: 600px;">
                        <form action="pegawai.php" method="POST">
                            <input type="hidden" name="iduser" value="<?= $iduser; ?>">
                            <div class="mb-3">
                                <label class="form-label text-muted fw-bold">Username / Email</label>
                                <input type="text" name="username" class="form-control" value="<?= $username; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted fw-bold">Role Hak Akses</label>
                                <select name="idrole" class="form-select" required>
                                    <option value="" disabled <?= empty($idrole) ? 'selected' : ''; ?>>Pilih Role</option>
                                    <?php 
                                    $role_query = mysqli_query($conn, "SELECT * FROM role");
                                    while($r = mysqli_fetch_assoc($role_query)): 
                                    ?>
                                        <option value="<?= $r['idrole']; ?>" <?= ($idrole == $r['idrole']) ? 'selected' : ''; ?>><?= $r['nama_role']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted fw-bold">Password <?= !empty($iduser) ? '(Kosongkan jika tidak ingin ganti)' : ''; ?></label>
                                <input type="password" name="password" class="form-control" <?= empty($iduser) ? 'required' : ''; ?>>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" name="simpan_pegawai" class="btn btn-warning text-dark fw-bold"><i class="fa-solid fa-save me-1"></i> Simpan</button>
                                <a href="pegawai.php" class="btn btn-secondary fw-bold">Batal</a>
                            </div>
                        </form>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
</html>