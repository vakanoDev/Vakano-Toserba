<?php
$page_title = 'Pengadaan Barang (PO)';
require_once __DIR__ . '/../includes/header.php';

requireSuperAdmin();

$msg = '';
$err = '';

// Buat Transaksi Pengadaan Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_po') {
    $idvendor = (int)$_POST['idvendor'];
    $items    = $_POST['items'] ?? [];
    $iduser   = getUserId();

    if (empty($idvendor) || empty($items)) {
        $err = 'Harap pilih vendor dan minimal satu barang untuk dipesan!';
    } else {
        $pdo->beginTransaction();
        try {
            // Insert header pengadaan
            $stmt = $pdo->prepare("
                INSERT INTO pengadaan (user_iduser, vendor_idvendor, status, subtotal_nilai, ppn, total_nilai)
                VALUES (?, ?, 'A', 0, 0, 0)
            ");
            $stmt->execute([$iduser, $idvendor]);
            $idpengadaan = $pdo->lastInsertId();

            // Insert detail pengadaan (trigger akan otomatis menghitung harga satuan & total)
            $stmtDet = $pdo->prepare("
                INSERT INTO detail_pengadaan (idpengadaan, idbarang, jumlah, harga_satuan, sub_total)
                VALUES (?, ?, ?, 0, 0)
            ");

            $hasItem = false;
            foreach ($items as $it) {
                $idb = (int)($it['idbarang'] ?? 0);
                $qty = (int)($it['jumlah'] ?? 0);
                if ($idb > 0 && $qty > 0) {
                    $stmtDet->execute([$idpengadaan, $idb, $qty]);
                    $hasItem = true;
                }
            }

            if (!$hasItem) {
                $pdo->rollBack();
                $err = 'Harap tentukan jumlah barang yang valid!';
            } else {
                $pdo->commit();
                $msg = "Pengadaan PO #$idpengadaan berhasil dibuat!";
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $err = 'Gagal membuat pengadaan: ' . $e->getMessage();
        }
    }
}

// Ambil list vendor aktif
$vendors = $pdo->query("SELECT * FROM vendor WHERE status = 'A' ORDER BY nama_vendor ASC")->fetchAll();

// Ambil list barang aktif
$barang_list = $pdo->query("SELECT b.*, s.nama_satuan FROM barang b JOIN satuan s ON b.idsatuan = s.idsatuan WHERE b.status = 1 ORDER BY b.nama ASC")->fetchAll();

// Ambil daftar pengadaan dari view vw_pengadaan
$pengadaan_list = $pdo->query("
    SELECT * FROM vw_pengadaan ORDER BY idpengadaan DESC
")->fetchAll();
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Pengadaan Barang (Purchase Order)</h1>
            <p class="text-xs text-slate-500 mt-1">Pemesanan barang ke vendor pemasok untuk menambah stok toko.</p>
        </div>
        <button type="button" onclick="openAddPoModal()"
            class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2 self-start">
            <i class="fa-solid fa-cart-plus"></i>
            <span>Buat PO Baru</span>
        </button>
    </div>

    <?php if ($msg): ?>
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            <span><?= $msg ?></span>
        </div>
    <?php endif; ?>

    <?php if ($err): ?>
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
            <span><?= $err ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200">
                        <th class="py-3 px-4">No. PO</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Vendor Pemasok</th>
                        <th class="py-3 px-4">Petugas</th>
                        <th class="py-3 px-4">Subtotal</th>
                        <th class="py-3 px-4">PPN (11%)</th>
                        <th class="py-3 px-4">Total Nilai</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($pengadaan_list)): ?>
                        <tr><td colspan="9" class="py-8 text-center text-slate-400">Belum ada pengadaan barang.</td></tr>
                    <?php else: ?>
                        <?php foreach ($pengadaan_list as $po): ?>
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">#<?= $po['idpengadaan'] ?></td>
                                <td class="py-3 px-4 text-slate-500"><?= date('d M Y, H:i', strtotime($po['tanggal'])) ?></td>
                                <td class="py-3 px-4 font-bold text-slate-800"><?= htmlspecialchars($po['vendor']) ?></td>
                                <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($po['petugas']) ?></td>
                                <td class="py-3 px-4 font-mono text-slate-600"><?= formatRupiah($po['subtotal']) ?></td>
                                <td class="py-3 px-4 font-mono text-slate-600"><?= formatRupiah($po['ppn']) ?></td>
                                <td class="py-3 px-4 font-mono font-bold text-emerald-700"><?= formatRupiah($po['total']) ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $po['status'] === 'A' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>">
                                        <?= $po['status'] === 'A' ? 'Aktif (Dipesan)' : 'Selesai' ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="penerimaan.php?po=<?= $po['idpengadaan'] ?>"
                                        class="px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-[11px] border border-emerald-200 transition">
                                        Proses Terima &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah PO -->
<div id="modalPo" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl relative max-h-[90vh] flex flex-col">
        <button onclick="closePoModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <h3 class="text-base font-extrabold text-slate-800 mb-4">Buat Pengadaan Barang Baru</h3>

        <form method="POST" class="space-y-4 text-xs flex-1 flex flex-col overflow-hidden">
            <input type="hidden" name="action" value="create_po">

            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih Vendor / Pemasok</label>
                <select name="idvendor" required
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Pilih Vendor --</option>
                    <?php foreach ($vendors as $v): ?>
                        <option value="<?= $v['idvendor'] ?>"><?= htmlspecialchars($v['nama_vendor']) ?> (<?= $v['badan_hukum'] === 'Y' ? 'PT/CV' : 'Perorangan' ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- List Barang yang Dipesan -->
            <div class="flex-1 overflow-y-auto space-y-2 border border-slate-100 p-3 rounded-2xl bg-slate-50">
                <div class="font-bold text-slate-700 mb-1 flex justify-between">
                    <span>Pilih Barang & Jumlah Dipesan:</span>
                </div>
                <?php foreach ($barang_list as $idx => $b): ?>
                    <div class="p-2.5 rounded-xl bg-white border border-slate-200 flex items-center justify-between gap-3">
                        <div class="flex-1">
                            <div class="font-bold text-slate-800"><?= htmlspecialchars($b['nama']) ?></div>
                            <div class="text-[10px] text-slate-400">Harga Beli: <?= formatRupiah($b['harga']) ?> / <?= htmlspecialchars($b['nama_satuan']) ?></div>
                        </div>
                        <input type="hidden" name="items[<?= $idx ?>][idbarang]" value="<?= $b['idbarang'] ?>">
                        <div class="w-24">
                            <input type="number" min="0" value="0" name="items[<?= $idx ?>][jumlah]"
                                placeholder="Qty"
                                class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-center font-bold focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pt-3 flex gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md">
                    Proses Simpan PO
                </button>
                <button type="button" onclick="closePoModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddPoModal() {
    document.getElementById('modalPo').classList.remove('hidden');
}
function closePoModal() {
    document.getElementById('modalPo').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
