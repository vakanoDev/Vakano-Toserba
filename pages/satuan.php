<?php
$page_title = 'Data Satuan';
require_once __DIR__ . '/../includes/header.php';

requireSuperAdmin();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $nama   = clean($_POST['nama_satuan']);
        $status = (int)($_POST['status'] ?? 1);

        if (empty($nama)) {
            $err = 'Nama satuan tidak boleh kosong!';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO satuan (nama_satuan, status) VALUES (?, ?)");
                $stmt->execute([$nama, $status]);
                $msg = 'Satuan baru berhasil ditambahkan!';
            } catch (Exception $e) {
                $err = 'Gagal menambah satuan: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'update') {
        $idsatuan = (int)$_POST['idsatuan'];
        $nama     = clean($_POST['nama_satuan']);
        $status   = (int)($_POST['status'] ?? 1);

        try {
            $stmt = $pdo->prepare("UPDATE satuan SET nama_satuan = ?, status = ? WHERE idsatuan = ?");
            $stmt->execute([$nama, $status, $idsatuan]);
            $msg = 'Data satuan berhasil diperbarui!';
        } catch (Exception $e) {
            $err = 'Gagal memperbarui satuan: ' . $e->getMessage();
        }
    } elseif ($action === 'toggle_status') {
        $idsatuan   = (int)$_POST['idsatuan'];
        $new_status = (int)$_POST['new_status'];
        try {
            $stmt = $pdo->prepare("UPDATE satuan SET status = ? WHERE idsatuan = ?");
            $stmt->execute([$new_status, $idsatuan]);
            $msg = 'Status satuan berhasil diubah!';
        } catch (Exception $e) {
            $err = 'Gagal mengubah status: ' . $e->getMessage();
        }
    }
}

// Ambil list satuan dari VIEW vw_satuan_total_barang
$satuan_list = $pdo->query("SELECT * FROM vw_satuan_total_barang ORDER BY idsatuan ASC")->fetchAll();
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Master Satuan Barang</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar satuan unit barang yang digunakan di Vakano Toserba (pcs, kg, pack, dll).</p>
        </div>
        <button type="button" onclick="openAddModal()"
            class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2 self-start">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Satuan</span>
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
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3 px-4">Nama Satuan</th>
                        <th class="py-3 px-4">Jumlah Barang Terkait</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($satuan_list)): ?>
                        <tr><td colspan="5" class="py-8 text-center text-slate-400">Belum ada satuan terdaftar.</td></tr>
                    <?php else: ?>
                        <?php foreach ($satuan_list as $s): ?>
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-400">#<?= $s['idsatuan'] ?></td>
                                <td class="py-3 px-4 font-bold text-slate-800"><?= htmlspecialchars($s['nama_satuan']) ?></td>
                                <td class="py-3 px-4 text-slate-600"><?= $s['total_barang'] ?> Barang</td>
                                <td class="py-3 px-4">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="idsatuan" value="<?= $s['idsatuan'] ?>">
                                        <input type="hidden" name="new_status" value="<?= $s['status'] == 1 ? 0 : 1 ?>">
                                        <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $s['status'] == 1 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' ?>">
                                            <?= $s['status'] == 1 ? 'Aktif' : 'Non-Aktif' ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button type="button" onclick='openEditModal(<?= json_encode($s) ?>)'
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

<!-- Modal Satuan -->
<div id="modalSatuan" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl relative">
        <button onclick="closeModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <h3 id="modalTitle" class="text-base font-extrabold text-slate-800 mb-4">Tambah Satuan Baru</h3>

        <form method="POST" id="formSatuan" class="space-y-4 text-xs">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="idsatuan" id="formIdsatuan" value="">

            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Satuan</label>
                <input type="text" name="nama_satuan" id="formNama" required
                    placeholder="Contoh: rim, botol, sachet"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Status</label>
                <select name="status" id="formStatus"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="1">Aktif</option>
                    <option value="0">Non-Aktif</option>
                </select>
            </div>

            <div class="pt-3 flex gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md">
                    Simpan
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
    document.getElementById('modalTitle').innerText = 'Tambah Satuan Baru';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formIdsatuan').value = '';
    document.getElementById('formNama').value = '';
    document.getElementById('formStatus').value = '1';
    document.getElementById('modalSatuan').classList.remove('hidden');
}

function openEditModal(s) {
    document.getElementById('modalTitle').innerText = 'Edit Satuan #' + s.idsatuan;
    document.getElementById('formAction').value = 'update';
    document.getElementById('formIdsatuan').value = s.idsatuan;
    document.getElementById('formNama').value = s.nama_satuan;
    document.getElementById('formStatus').value = s.status;
    document.getElementById('modalSatuan').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modalSatuan').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>