<?php
// Halaman CRUD generik untuk semua tabel di config/tabel_config.php
require __DIR__ . '/layout/auth.php';

$t = $_GET['t'] ?? 'barang';
if (!isset($TABEL[$t])) $t = 'barang';      // hanya tabel dalam daftar yang boleh diakses
$cfg  = $TABEL[$t];
$pk   = $cfg['pk'];
$cols = $cfg['cols'];

/* ---------- PROSES POST: simpan / hapus ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $_SESSION['flash'] = ['danger', 'Token tidak valid, coba ulangi.'];
        header("Location: tabel.php?t=$t"); exit;
    }
    try {
        if (($_POST['aksi'] ?? '') === 'hapus') {
            $id = $_POST['id'] ?? '';
            $st = $conn->prepare("DELETE FROM `$t` WHERE `$pk` = ?");
            $st->bind_param('s', $id);
            $st->execute();
            $_SESSION['flash'] = ['success', 'Data berhasil dihapus.'];
        } else {
            $id = $_POST['id'] ?? '';
            $fields = []; $vals = [];
            foreach ($cols as $c) {
                $v = $_POST[$c['n']] ?? '';
                if ($c['t'] === 'password') {
                    if ($v === '') continue;            // kosong = password lama dipertahankan
                    $v = hash('sha256', $v);
                } elseif ($v === '') {
                    $v = null;
                } elseif ($c['t'] === 'datetime') {
                    $v = str_replace('T', ' ', $v);
                }
                $fields[] = $c['n'];
                $vals[]   = $v;
            }
            if ($id === '') {
                $sql = "INSERT INTO `$t` (`" . implode('`,`', $fields) . "`) VALUES (" . implode(',', array_fill(0, count($fields), '?')) . ")";
            } else {
                $sql = "UPDATE `$t` SET `" . implode('`=?, `', $fields) . "`=? WHERE `$pk` = ?";
                $vals[] = $id;
            }
            $st = $conn->prepare($sql);
            $st->bind_param(str_repeat('s', count($vals)), ...$vals);
            $st->execute();
            $_SESSION['flash'] = ['success', 'Data berhasil disimpan.'];
        }
    } catch (mysqli_sql_exception $ex) {
        $kode = (int) $ex->getCode();
        if ($kode === 1451)      $pesan = 'Data tidak bisa dihapus karena masih dipakai di tabel lain.';
        elseif ($kode === 1452)  $pesan = 'Data induk (pilihan relasi) tidak ditemukan.';
        else                     $pesan = 'Gagal: ' . $ex->getMessage();
        $_SESSION['flash'] = ['danger', $pesan];
    }
    header("Location: tabel.php?t=$t"); exit;
}

/* ---------- Data pendukung (pilihan relasi/FK) ---------- */
$lookup = [];
foreach ($cols as $c) {
    if ($c['t'] !== 'fk') continue;
    $ref = $c['ref']; $rpk = $TABEL[$ref]['pk']; $lab = $c['lab'];
    $q = $lab !== '' ? "SELECT `$rpk` AS id, `$lab` AS lab FROM `$ref` ORDER BY `$rpk`"
                     : "SELECT `$rpk` AS id, NULL AS lab FROM `$ref` ORDER BY `$rpk`";
    $lookup[$c['n']] = [];
    foreach (mysqli_query($conn, $q) as $r) {
        $lookup[$c['n']][$r['id']] = $r['lab'] !== null ? $r['id'] . ' - ' . $r['lab'] : '#' . $r['id'];
    }
}

$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
$form  = ($_GET['aksi'] ?? '') === 'form';
$judul = $cfg['label'];
$aktif = $t;
require __DIR__ . '/layout/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fa-solid <?= e($cfg['ikon']); ?> me-2 text-warning"></i><?= e($cfg['label']); ?></h2>
    <?php if (!$form): ?>
        <a href="tabel.php?t=<?= e($t); ?>&aksi=form" class="btn btn-warning fw-bold text-dark"><i class="fa-solid fa-plus me-1"></i> Tambah Data</a>
    <?php endif; ?>
</div>

<?php if ($flash): ?>
    <div class="alert alert-<?= e($flash[0]); ?> alert-dismissible fade show"><?= e($flash[1]); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php if ($form):
    $id  = $_GET['id'] ?? '';
    $row = [];
    if ($id !== '') {
        $st = $conn->prepare("SELECT * FROM `$t` WHERE `$pk` = ?");
        $st->bind_param('s', $id); $st->execute();
        $row = $st->get_result()->fetch_assoc() ?: [];
        if (!$row) $id = '';
    } ?>
    <div class="card card-custom p-4">
        <h5 class="mb-3"><?= $id === '' ? 'Tambah' : 'Ubah'; ?> Data <?= e($cfg['label']); ?></h5>
        <form method="POST" action="tabel.php?t=<?= e($t); ?>">
            <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']); ?>">
            <input type="hidden" name="id" value="<?= e($id); ?>">
            <div class="row">
            <?php foreach ($cols as $c):
                $n = $c['n']; $val = $row[$n] ?? ''; ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label text-muted fw-bold"><?= e($c['l']); ?></label>
                    <?php if ($c['t'] === 'fk'): ?>
                        <select name="<?= e($n); ?>" class="form-select">
                            <option value="">-- kosong --</option>
                            <?php foreach ($lookup[$n] as $k => $lab): ?>
                                <option value="<?= e($k); ?>" <?= (string)$val === (string)$k ? 'selected' : ''; ?>><?= e($lab); ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($c['t'] === 'select'): ?>
                        <select name="<?= e($n); ?>" class="form-select">
                            <option value="">-- kosong --</option>
                            <?php foreach ($c['opt'] as $k => $lab): ?>
                                <option value="<?= e($k); ?>" <?= (string)$val === (string)$k ? 'selected' : ''; ?>><?= e($lab); ?> (<?= e($k); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    <?php elseif ($c['t'] === 'textarea'): ?>
                        <textarea name="<?= e($n); ?>" class="form-control" rows="3" maxlength="<?= (int)($c['max'] ?? 255); ?>"><?= e($val); ?></textarea>
                    <?php elseif ($c['t'] === 'password'): ?>
                        <input type="password" name="<?= e($n); ?>" class="form-control" autocomplete="new-password" <?= $id === '' ? 'required' : ''; ?>>
                        <?php if ($id !== ''): ?><small class="text-muted">Kosongkan jika tidak ingin mengganti password.</small><?php endif; ?>
                    <?php elseif ($c['t'] === 'datetime'): ?>
                        <input type="datetime-local" step="1" name="<?= e($n); ?>" class="form-control" value="<?= e($val !== '' ? str_replace(' ', 'T', $val) : ''); ?>">
                    <?php elseif ($c['t'] === 'number' || $c['t'] === 'decimal'): ?>
                        <input type="number" step="<?= $c['t'] === 'decimal' ? 'any' : '1'; ?>" name="<?= e($n); ?>" class="form-control" value="<?= e($val); ?>">
                    <?php else: ?>
                        <input type="text" name="<?= e($n); ?>" class="form-control" maxlength="<?= (int)($c['max'] ?? 255); ?>" value="<?= e($val); ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning fw-bold text-dark"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan</button>
                <a href="tabel.php?t=<?= e($t); ?>" class="btn btn-secondary fw-bold">Batal</a>
            </div>
        </form>
    </div>

<?php else:
    $data = mysqli_query($conn, "SELECT * FROM `$t` ORDER BY `$pk` DESC LIMIT 500"); ?>
    <div class="card card-custom p-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#<?= e($pk); ?></th>
                        <?php foreach ($cols as $c): ?><th><?= e($c['l']); ?></th><?php endforeach; ?>
                        <th class="text-center" style="width:110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php $ada = false; foreach ($data as $r): $ada = true; ?>
                    <tr>
                        <td><?= e($r[$pk]); ?></td>
                        <?php foreach ($cols as $c):
                            $v = $r[$c['n']];
                            if ($c['t'] === 'password')      $tampil = '••••••••';
                            elseif ($v === null)             $tampil = '-';
                            elseif ($c['t'] === 'fk')        $tampil = $lookup[$c['n']][$v] ?? $v;
                            elseif ($c['t'] === 'select')    $tampil = ($c['opt'][$v] ?? $v);
                            else                             $tampil = $v; ?>
                            <td><?= e($tampil); ?></td>
                        <?php endforeach; ?>
                        <td class="text-center text-nowrap">
                            <a href="tabel.php?t=<?= e($t); ?>&aksi=form&id=<?= e($r[$pk]); ?>" class="btn btn-sm btn-warning"><i class="fa-solid fa-pen"></i></a>
                            <form method="POST" action="tabel.php?t=<?= e($t); ?>" class="d-inline" onsubmit="return confirm('Hapus data ini?');">
                                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']); ?>">
                                <input type="hidden" name="aksi" value="hapus">
                                <input type="hidden" name="id" value="<?= e($r[$pk]); ?>">
                                <button class="btn btn-sm btn-danger"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$ada): ?>
                    <tr><td colspan="<?= count($cols) + 2; ?>" class="text-center text-muted py-4">Belum ada data.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>
