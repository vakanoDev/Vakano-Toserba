<?php
require '../config/koneksi.php';
/** @var mysqli $conn */
session_start();

if (!isset($_SESSION['iduser']) || $_SESSION['idrole'] != 1) {
    echo "<script>alert('Akses ditolak!'); window.location='../login.php';</script>";
    exit;
}

// Cek aksi yang diminta (default: tampilkan tabel)
$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : 'tampil';

// =====================================
// LOGIKA HAPUS & SIMPAN BARANG
// =====================================
if ($aksi == 'hapus') {
    $id = $_GET['id'];
    mysqli_query($conn, "DELETE FROM barang WHERE idbarang='$id'");
    echo "<script>alert('Data Barang berhasil dihapus!'); window.location='barang.php';</script>";
    exit;
}

if (isset($_POST['simpan_barang'])) {
    $idbarang = $_POST['idbarang'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $jenis = $_POST['jenis'];
    $idsatuan = $_POST['idsatuan'];
    $harga = $_POST['harga'];
    $status = $_POST['status'];

    if (empty($idbarang)) {
        mysqli_query($conn, "INSERT INTO barang (jenis, nama, idsatuan, status, harga) VALUES ('$jenis', '$nama', '$idsatuan', '$status', '$harga')");
    } else {
        mysqli_query($conn, "UPDATE barang SET jenis='$jenis', nama='$nama', idsatuan='$idsatuan', status='$status', harga='$harga' WHERE idbarang='$idbarang'");
    }
    echo "<script>alert('Data Barang berhasil disimpan!'); window.location='barang.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Barang - Vakano Toserba</title>
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
            <!-- SIDEBAR -->
            <div class="col-md-3 col-lg-2 sidebar p-4 d-flex flex-column justify-content-between">
                <div>
                    <h5 class="fw-bold text-warning mb-4"><i class="fa-solid fa-store me-2"></i>Vakano Admin</h5>
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item"><a href="dashboard_admin.php" class="nav-link"><i class="fa-solid fa-house me-2"></i> Beranda Admin</a></li>
                        <li class="nav-item mt-2 mb-1"><small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Menu Master</small></li>
                        <li><a href="role.php" class="nav-link"><i class="fa-solid fa-user-shield me-2"></i> Manajemen Role</a></li>
                        <li><a href="barang.php" class="nav-link active"><i class="fa-solid fa-boxes-stacked me-2"></i> Data Barang</a></li>
                        <li><a href="vendor.php" class="nav-link"><i class="fa-solid fa-truck-field me-2"></i> Data Vendor</a></li>
                        <li><a href="pegawai.php" class="nav-link"><i class="fa-solid fa-users me-2"></i> Data Pegawai</a></li>
                    </ul>
                </div>
                <div class="pt-3 border-top border-secondary border-opacity-25">
                    <span class="d-block small text-muted mb-2">Admin: <b><?= $_SESSION['username']; ?></b></span>
                    <a href="../logout.php" class="btn btn-danger btn-sm w-100 rounded-pill fw-bold"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
                </div>
            </div>

            <!-- KONTEN DATA BARANG -->
            <div class="col-md-9 col-lg-10 p-4 p-md-5">
                
                <?php if ($aksi == 'tampil') { 
                    $data_barang = mysqli_query($conn, "SELECT barang.*, satuan.nama_satuan FROM barang LEFT JOIN satuan ON barang.idsatuan = satuan.idsatuan");
                ?>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Data Barang</h2>
                        <a href="barang.php?aksi=form" class="btn btn-info text-dark fw-bold"><i class="fa-solid fa-plus me-1"></i> Tambah Barang</a>
                    </div>
                    <div class="card card-custom p-4">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Barang</th>
                                        <th>Jenis</th>
                                        <th>Satuan</th>
                                        <th>Harga</th>
                                        <th>Status</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($b = mysqli_fetch_assoc($data_barang)) : ?>
                                    <tr>
                                        <td><?= $b['idbarang']; ?></td>
                                        <td class="fw-bold"><?= $b['nama']; ?></td>
                                        <td><?= $b['jenis']; ?></td>
                                        <td><?= $b['nama_satuan']; ?></td>
                                        <td>Rp <?= number_format($b['harga'], 0, ',', '.'); ?></td>
                                        <td><span class="badge <?= ($b['status'] == 1) ? 'bg-success' : 'bg-secondary'; ?>"><?= ($b['status'] == 1) ? 'Aktif' : 'Non-Aktif'; ?></span></td>
                                        <td class="text-center">
                                            <a href="barang.php?aksi=form&id=<?= $b['idbarang']; ?>" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i></a>
                                            <a href="barang.php?aksi=hapus&id=<?= $b['idbarang']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus barang ini?');"><i class="fa-solid fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <?php } elseif ($aksi == 'form') { 
                    $idbarang = ''; $nama = ''; $jenis = ''; $idsatuan = ''; $harga = ''; $status = 1;
                    $judul = 'Tambah Data Barang';
                    if (isset($_GET['id'])) {
                        $idbarang = $_GET['id']; $judul = 'Edit Data Barang';
                        $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM barang WHERE idbarang = '$idbarang'"));
                        $nama = $data['nama']; $jenis = $data['jenis']; $idsatuan = $data['idsatuan']; $harga = $data['harga']; $status = $data['status'];
                    }
                ?>
                    <div class="mb-4"><h2><?= $judul; ?></h2></div>
                    <div class="card card-custom p-4" style="max-width: 700px;">
                        <form action="barang.php" method="POST">
                            <input type="hidden" name="idbarang" value="<?= $idbarang; ?>">
                            <div class="row">
                                <div class="col-md-8 mb-3"><label class="form-label text-muted fw-bold">Nama Barang</label><input type="text" name="nama" class="form-control" value="<?= $nama; ?>" required></div>
                                <div class="col-md-4 mb-3"><label class="form-label text-muted fw-bold">Kode Jenis</label><input type="text" name="jenis" class="form-control" value="<?= $jenis; ?>" maxlength="1" required></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted fw-bold">Satuan Barang</label>
                                    <select name="idsatuan" class="form-select" required>
                                        <option value="" disabled <?= empty($idsatuan) ? 'selected' : ''; ?>>Pilih Satuan</option>
                                        <?php 
                                        $satuan_query = mysqli_query($conn, "SELECT * FROM satuan");
                                        while($s = mysqli_fetch_assoc($satuan_query)): 
                                        ?>
                                            <option value="<?= $s['idsatuan']; ?>" <?= ($idsatuan == $s['idsatuan']) ? 'selected' : ''; ?>><?= $s['nama_satuan']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3"><label class="form-label text-muted fw-bold">Harga Satuan (Rp)</label><input type="number" name="harga" class="form-control" value="<?= $harga; ?>" required></div>
                            </div>
                            <div class="mb-4">
                                <label class="form-label text-muted fw-bold">Status Produk</label>
                                <select name="status" class="form-select">
                                    <option value="1" <?= ($status == 1) ? 'selected' : ''; ?>>Aktif (Dijual)</option>
                                    <option value="0" <?= ($status == 0) ? 'selected' : ''; ?>>Non-Aktif (Disembunyikan)</option>
                                </select>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" name="simpan_barang" class="btn btn-info text-dark fw-bold"><i class="fa-solid fa-save me-1"></i> Simpan</button>
                                <a href="barang.php" class="btn btn-secondary fw-bold">Batal</a>
                            </div>
                        </form>
                    </div>
                <?php } ?>

            </div>
        </div>
    </div>
</body>
</html>