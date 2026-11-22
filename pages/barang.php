<?php
$page_title = 'Data Master Barang';
require_once __DIR__ . '/../includes/header.php';

requireSuperAdmin();

$msg = '';
$err = '';

// Handle Create / Update / Toggle Status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $nama     = clean($_POST['nama']);
        $jenis    = clean($_POST['jenis']);
        $idsatuan = (int)$_POST['idsatuan'];
        $harga    = (int)$_POST['harga'];
        $status   = (int)($_POST['status'] ?? 1);

        if (empty($nama) || empty($idsatuan) || $harga <= 0) {
            $err = 'Harap isi nama, satuan, dan harga beli dengan benar!';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO barang (nama, jenis, idsatuan, status, harga) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nama, $jenis, $idsatuan, $status, $harga]);
                $msg = 'Barang baru berhasil ditambahkan!';
            } catch (Exception $e) {
                $err = 'Gagal menambah barang: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'update') {
        $idbarang = (int)$_POST['idbarang'];
        $nama     = clean($_POST['nama']);
        $jenis    = clean($_POST['jenis']);
        $idsatuan = (int)$_POST['idsatuan'];
        $harga    = (int)$_POST['harga'];
        $status   = (int)($_POST['status'] ?? 1);

        try {
            $stmt = $pdo->prepare("UPDATE barang SET nama = ?, jenis = ?, idsatuan = ?, status = ?, harga = ? WHERE idbarang = ?");
            $stmt->execute([$nama, $jenis, $idsatuan, $status, $harga, $idbarang]);
            $msg = 'Data barang berhasil diperbarui!';
        } catch (Exception $e) {
            $err = 'Gagal memperbarui barang: ' . $e->getMessage();
        }
    } elseif ($action === 'toggle_status') {
        $idbarang   = (int)$_POST['idbarang'];
        $new_status = (int)$_POST['new_status'];
        try {
            $stmt = $pdo->prepare("UPDATE barang SET status = ? WHERE idbarang = ?");
            $stmt->execute([$new_status, $idbarang]);
            $msg = 'Status barang berhasil diubah!';
        } catch (Exception $e) {
            $err = 'Gagal mengubah status: ' . $e->getMessage();
        }
    }
}

// Ambil list satuan untuk dropdown
$satuan_list = $pdo->query("SELECT * FROM satuan WHERE status = 1 ORDER BY nama_satuan ASC")->fetchAll();

// Ambil list barang lengkap beserta stok dan harga jual
$barang_list = $pdo->query("
    SELECT 
        b.idbarang,
        b.nama,
        b.jenis,
        b.status,
        b.harga AS harga_beli,
        s.idsatuan,
        s.nama_satuan,
        fn_stok_barang(b.idbarang) AS stok,
        fn_harga_jual(b.idbarang) AS harga_jual
    FROM barang b
    JOIN satuan s ON b.idsatuan = s.idsatuan
    ORDER BY b.idbarang ASC
")->fetchAll();

$margin_aktif = getActiveMargin($pdo);
?>

<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Katalog Master Barang</h1>
            <p class="text-xs text-slate-500 mt-1">
                Kelola daftar produk, harga modal, dan estimasi harga jual (Margin Aktif: <strong><?= $margin_aktif['persen'] ?>%</strong>).
            </p>
        </div>
        <button type="button" onclick="openAddModal()"
            class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2 self-start">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Barang</span>
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

    <!-- Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200">
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Satuan</th>
                        <th class="py-3 px-4">Harga Beli</th>
                        <th class="py-3 px-4">Harga Jual (POS)</th>
                        <th class="py-3 px-4">Stok Saat Ini</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($barang_list)): ?>
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">Belum ada barang yang terdaftar.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($barang_list as $b): ?>
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-400">#<?= $b['idbarang'] ?></td>
                                <td class="py-3 px-4 font-bold text-slate-800"><?= htmlspecialchars($b['nama']) ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-md font-semibold text-[10px] <?= $b['jenis'] === 'M' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200' ?>">
                                        <?= $b['jenis'] === 'M' ? 'Makanan' : 'Non-Makanan' ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-medium text-slate-600"><?= htmlspecialchars($b['nama_satuan']) ?></td>
                                <td class="py-3 px-4 font-mono text-slate-600"><?= formatRupiah($b['harga_beli']) ?></td>
                                <td class="py-3 px-4 font-mono font-bold text-emerald-700"><?= formatRupiah($b['harga_jual']) ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 rounded-lg font-bold text-[11px] <?= $b['stok'] <= 0 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-800' ?>">
                                        <?= $b['stok'] ?> <?= htmlspecialchars($b['nama_satuan']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="idbarang" value="<?= $b['idbarang'] ?>">
                                        <input type="hidden" name="new_status" value="<?= $b['status'] == 1 ? 0 : 1 ?>">
                                        <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $b['status'] == 1 ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-200 text-slate-600 hover:bg-slate-300' ?>">
                                            <?= $b['status'] == 1 ? 'Aktif' : 'Non-Aktif' ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button type="button" onclick='openEditModal(<?= json_encode($b) ?>)'
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 transition" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL FORM BARANG -->
<div id="modalBarang" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl relative">
        <button onclick="closeModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <h3 id="modalTitle" class="text-base font-extrabold text-slate-800 mb-4">Tambah Barang Baru</h3>

        <form method="POST" id="formBarang" class="space-y-4 text-xs">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="idbarang" id="formIdbarang" value="">

            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Barang</label>
                <input type="text" name="nama" id="formNama" required
                    placeholder="Contoh: Beras Ramos 5kg"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kategori Jenis</label>
                    <select name="jenis" id="formJenis" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="M">Makanan (M)</option>
                        <option value="N">Non-Makanan (N)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Satuan</label>
                    <select name="idsatuan" id="formSatuan" required
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <?php foreach ($satuan_list as $s): ?>
                            <option value="<?= $s['idsatuan'] ?>"><?= htmlspecialchars($s['nama_satuan']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Harga Beli Dasar (Rp)</label>
                    <input type="number" name="harga" id="formHarga" required min="100"
                        placeholder="Contoh: 12000"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status</label>
                    <select name="status" id="formStatus"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="1">Aktif</option>
                        <option value="0">Non-Aktif</option>
                    </select>
                </div>
            </div>

            <div class="pt-3 flex gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md">
                    Simpan Data
                </button>
                <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').innerText = 'Tambah Barang Baru';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formIdbarang').value = '';
    document.getElementById('formNama').value = '';
    document.getElementById('formJenis').value = 'M';
    document.getElementById('formHarga').value = '';
    document.getElementById('formStatus').value = '1';
    document.getElementById('modalBarang').classList.remove('hidden');
}

function openEditModal(b) {
    document.getElementById('modalTitle').innerText = 'Edit Data Barang #' + b.idbarang;
    document.getElementById('formAction').value = 'update';
    document.getElementById('formIdbarang').value = b.idbarang;
    document.getElementById('formNama').value = b.nama;
    document.getElementById('formJenis').value = b.jenis;
    document.getElementById('formSatuan').value = b.idsatuan;
    document.getElementById('formHarga').value = b.harga_beli;
    document.getElementById('formStatus').value = b.status;
    document.getElementById('modalBarang').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modalBarang').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
