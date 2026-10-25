<?php
require __DIR__ . '/layout/auth.php';
/** @var mysqli $conn */
/** @var array $TABEL */
$judul = 'Dashboard Admin';
$aktif = 'dashboard';

// jumlah baris tiap tabel
$jumlah = [];
foreach ($TABEL as $kode => $tb) {
    $jumlah[$kode] = (int) mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM `$kode`"))[0];
}
require __DIR__ . '/layout/header.php';
?>
<h2 class="mb-1">Dashboard Utama</h2>
<p class="text-mb-4">Selamat datang, <b><?= e($_SESSION['username']); ?></b>. Pilih tabel untuk menambah, mengubah, atau menghapus data.</p>

<?php $grupSekarang = '';
foreach ($TABEL as $kode => $tb):
    if ($tb['grup'] !== $grupSekarang):
        if ($grupSekarang !== '') echo '</div>';
        $grupSekarang = $tb['grup']; ?>
        <h6 class="text-uppercase text-muted fw-bold mt-4 mb-3"><?= e($grupSekarang); ?></h6>
        <div class="row g-3">
    <?php endif; ?>
    <div class="col-6 col-md-4 col-xl-3">
        <a class="stat" href="tabel.php?t=<?= e($kode); ?>">
            <div class="card card-custom p-3">
                <div class="d-flex align-items-center">
                    <i class="fa-solid <?= e($tb['ikon']); ?> fs-3 text-warning me-3"></i>
                    <div>
                        <div class="small text-muted"><?= e($tb['label']); ?></div>
                        <div class="fs-4 fw-bold"><?= $jumlah[$kode]; ?> <small class="fs-6 fw-normal text-muted">data</small></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
<?php endforeach; echo '</div>'; ?>
<?php require __DIR__ . '/layout/footer.php'; ?>
