<?php
require __DIR__ . '/layout/auth.php';
/** @var mysqli $conn */
/** @var array $TABEL */
$judul = 'Dashboard Admin';
$aktif = 'dashboard';

// jumlah data tiap tabel / view (satu query; kalau ada tabel baru tinggal tambah satu baris UNION ALL)
$hasil = mysqli_query($conn, "
    SELECT 'role' AS kode, COUNT(*) AS jumlah FROM role_vu
    UNION ALL SELECT 'user',              COUNT(*) FROM akun_vu
    UNION ALL SELECT 'satuan',            COUNT(*) FROM satuan_vu
    UNION ALL SELECT 'vendor',            COUNT(*) FROM vendor_vu
    UNION ALL SELECT 'barang',            COUNT(*) FROM barang_vu
    UNION ALL SELECT 'margin_penjualan',  COUNT(*) FROM margin_vu
    UNION ALL SELECT 'pengadaan',         COUNT(*) FROM pengadaan
    UNION ALL SELECT 'detail_pengadaan',  COUNT(*) FROM detail_pengadaan
    UNION ALL SELECT 'penerimaan',        COUNT(*) FROM penerimaan
    UNION ALL SELECT 'detail_penerimaan', COUNT(*) FROM detail_penerimaan
    UNION ALL SELECT 'retur',             COUNT(*) FROM retur
    UNION ALL SELECT 'detail_retur',      COUNT(*) FROM detail_retur
    UNION ALL SELECT 'penjualan',         COUNT(*) FROM penjualan
    UNION ALL SELECT 'detail_penjualan',  COUNT(*) FROM detail_penjualan
    UNION ALL SELECT 'kartu_stok',        COUNT(*) FROM kartu_stok
");
$jumlah = [];
foreach ($hasil as $r) $jumlah[$r['kode']] = (int) $r['jumlah'];
require __DIR__ . '/layout/header.php';
?>
<h2 class="mb-1">Dashboard Utama</h2>
<p class="text mb-4">Selamat datang, <b><?= e($_SESSION['username']); ?></b>. Pilih tabel untuk menambah, mengubah, atau menghapus data.</p>

<?php $grupSekarang = '';
foreach ($TABEL as $kode => $tb):
    if ($tb['grup'] !== $grupSekarang):
        if ($grupSekarang !== '') echo '</div>';
        $grupSekarang = $tb['grup']; ?>
        <h6 class="text-uppercase text fw-bold mt-4 mb-3"><?= e($grupSekarang); ?></h6>
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