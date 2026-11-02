<?php
// Dashboard HRD: tampilan sama dengan admin, isinya user_vu + tambah / ubah / hapus
require_once __DIR__ . '/layout/auth.php';
/** @var mysqli $conn */

$aksi = $_GET['aksi'] ?? 'daftar';

/* ---------- PROSES POST: simpan / hapus ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $_SESSION['flash'] = ['danger', 'Token tidak valid, coba ulangi.'];
        header('Location: dashboard_hrd.php'); exit;
    }
    try {
        $id = $_POST['id'] ?? '';

        // akun Admin tidak boleh disentuh HRD
        if ($id !== '') {
            $st = $conn->prepare("SELECT idrole FROM akun_form_vu WHERE iduser = ?");
            $st->bind_param('s', $id); $st->execute();
            $lama = $st->get_result()->fetch_assoc();
            if (!$lama)                          throw new Exception('Data tidak ditemukan.');
            if ($lama['idrole'] == IDROLE_ADMIN) throw new Exception('Akun Admin tidak bisa diubah oleh HRD.');
        }

        if (($_POST['aksi'] ?? '') === 'hapus') {
            if ($id == $_SESSION['iduser']) throw new Exception('Anda tidak bisa menghapus akun sendiri.');
            $st = $conn->prepare("DELETE FROM akun_form_vu WHERE iduser = ?");
            $st->bind_param('s', $id); $st->execute();
            $_SESSION['flash'] = ['success', 'Data berhasil dihapus.'];

        } else {
            $username = trim($_POST['username'] ?? '');
            $idrole   = $_POST['idrole'] ?? '';
            $password = $_POST['password'] ?? '';
            if ($username === '' || $idrole === '') throw new Exception('Username dan jabatan wajib diisi.');
            if ($idrole == IDROLE_ADMIN)            throw new Exception('HRD tidak bisa memberi jabatan Admin.');

            if ($id === '') {
                if ($password === '') throw new Exception('Password wajib diisi.');
                $st = $conn->prepare("INSERT INTO akun_form_vu (username, password, idrole) VALUES (?, ?, ?)");
                $hash = hash('sha256', $password);
                $st->bind_param('sss', $username, $hash, $idrole);
            } elseif ($password === '') {
                $st = $conn->prepare("UPDATE akun_form_vu SET username = ?, idrole = ? WHERE iduser = ?");
                $st->bind_param('sss', $username, $idrole, $id);
            } else {
                $st = $conn->prepare("UPDATE akun_form_vu SET username = ?, password = ?, idrole = ? WHERE iduser = ?");
                $hash = hash('sha256', $password);
                $st->bind_param('ssss', $username, $hash, $idrole, $id);
            }
            $st->execute();
            $_SESSION['flash'] = ['success', 'Data berhasil disimpan.'];
        }
    } catch (mysqli_sql_exception $ex) {
        $kode = (int) $ex->getCode();
        if ($kode === 1451)      $pesan = 'Data tidak bisa dihapus karena masih dipakai di tabel lain.';
        elseif ($kode === 1452)  $pesan = 'Jabatan yang dipilih tidak ditemukan.';
        else                     $pesan = 'Gagal: ' . $ex->getMessage();
        $_SESSION['flash'] = ['danger', $pesan];
    } catch (Exception $ex) {
        $_SESSION['flash'] = ['danger', $ex->getMessage()];
    }
    header('Location: dashboard_hrd.php'); exit;
}

$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
$judul = 'User / Pegawai';
require __DIR__ . '/layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fa-solid fa-users me-2 text-warning"></i>User / Pegawai</h2>
    <?php if ($aksi !== 'form'): ?>
        <a href="dashboard_hrd.php?aksi=form" class="btn btn-warning fw-bold text-dark"><i class="fa-solid fa-plus me-1"></i> Tambah Data</a>
    <?php endif; ?>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash[0]); ?> alert-dismissible fade show"><?= e($flash[1]); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php if ($aksi === 'form'):
    $id  = $_GET['id'] ?? '';
    $row = ['username' => '', 'idrole' => ''];
    if ($id !== '') {
        $st = $conn->prepare("SELECT iduser, username, idrole FROM akun_form_vu WHERE iduser = ?");
        $st->bind_param('s', $id); $st->execute();
        $row = $st->get_result()->fetch_assoc() ?: $row;
        if (empty($row['iduser'])) $id = '';
        elseif ($row['idrole'] == IDROLE_ADMIN) {
            $_SESSION['flash'] = ['danger', 'Akun Admin tidak bisa diubah oleh HRD.'];
            header('Location: dashboard_hrd.php'); exit;
        }
    }
    $daftar_role = mysqli_query($conn, "SELECT idrole, nama_role FROM role_form_vu WHERE idrole <> 1 ORDER BY idrole"); ?>
    <div class="card card-custom p-4" style="max-width:640px;">
        <h5 class="mb-3"><?= $id === '' ? 'Tambah' : 'Ubah'; ?> Data User / Pegawai</h5>
        <form method="POST" action="dashboard_hrd.php">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']); ?>">
            <input type="hidden" name="id" value="<?= e($id); ?>">
            <div class="mb-3">
                <label class="form-label text-white-50 fw-bold">Username / Email</label>
                <input type="text" name="username" class="form-control" maxlength="45" value="<?= e($row['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-white-50 fw-bold">Jabatan</label>
                <select name="idrole" class="form-select" required>
                    <option value="" disabled <?= $row['idrole'] === '' ? 'selected' : ''; ?>>Pilih jabatan</option>
                    <?php foreach ($daftar_role as $r): ?>
                        <option value="<?= e($r['idrole']); ?>" <?= (string)$row['idrole'] === (string)$r['idrole'] ? 'selected' : ''; ?>><?= e($r['nama_role']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="form-label text-white-50 fw-bold">Password <?= $id !== '' ? '(kosongkan jika tidak ingin mengganti)' : ''; ?></label>
                <input type="password" name="password" class="form-control" autocomplete="new-password" <?= $id === '' ? 'required' : ''; ?>>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning fw-bold text-dark"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan</button>
                <a href="dashboard_hrd.php" class="btn btn-secondary fw-bold">Batal</a>
            </div>
        </form>
    </div>

<?php else:
    // Daftar = SELECT dari view user_vu
    $data = mysqli_query($conn, "SELECT NIP, Email, Jabatan, idrole
                                 FROM user_vu
                                 ORDER BY NIP DESC"); ?>
    <div class="card card-custom p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr><th style="width:90px;">NIP</th><th>Email</th><th>Jabatan</th><th class="text-center" style="width:110px;">Aksi</th></tr>
                </thead>
                <tbody>
                <?php foreach ($data as $r): ?>
                    <tr>
                        <td><?= e($r['NIP']); ?></td>
                        <td><?= e($r['Email']); ?></td>
                        <td><?= e($r['Jabatan'] ?? '-'); ?></td>
                        <td class="text-center text-nowrap">
                        <?php if ($r['idrole'] == IDROLE_ADMIN): ?>
                            <span class="text-white-50 small">Admin</span>
                        <?php else: ?>
                            <a href="dashboard_hrd.php?aksi=form&id=<?= e($r['NIP']); ?>" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i></a>
                            <?php if ($r['NIP'] != $_SESSION['iduser']): ?>
                            <form method="POST" action="dashboard_hrd.php" class="d-inline" onsubmit="return confirm('Hapus data ini?');">
                                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']); ?>">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id" value="<?= e($r['NIP']); ?>">
                                <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($data->num_rows === 0): ?>
                    <tr><td colspan="4" class="text-center text-white-50 py-4">Belum ada data.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>
