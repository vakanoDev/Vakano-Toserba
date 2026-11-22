<?php
/**
 * Vakano Toserba - AJAX Endpoint for POS
 */
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi login telah berakhir']);
    exit;
}

if (!isSuperAdmin() && !isKasir()) {
    echo json_encode(['status' => 'error', 'message' => 'Anda tidak memiliki hak akses kasir']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. Ambil daftar barang aktif beserta stok & harga jual terbaru
if ($action === 'get_items') {
    try {
        $stmt = $pdo->query("
            SELECT 
                b.idbarang,
                b.nama,
                b.jenis,
                s.nama_satuan,
                b.harga AS harga_beli,
                fn_stok_barang(b.idbarang) AS stok,
                fn_harga_jual(b.idbarang) AS harga_jual
            FROM barang b
            JOIN satuan s ON b.idsatuan = s.idsatuan
            WHERE b.status = 1
            ORDER BY b.nama ASC
        ");
        $items = $stmt->fetchAll();
        echo json_encode(['status' => 'success', 'data' => $items]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

// 2. Checkout Transaksi Penjualan
if ($action === 'checkout') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || empty($input['items'])) {
        echo json_encode(['status' => 'error', 'message' => 'Keranjang belanja masih kosong!']);
        exit;
    }

    $items       = $input['items'];
    $bayar_tunai = (int)($input['bayar_tunai'] ?? 0);
    $iduser      = getUserId();

    // Dapatkan margin aktif
    $margin = getActiveMargin($pdo);
    $idmargin = $margin['idmargin_penjualan'];

    $pdo->beginTransaction();
    try {
        // Buat record penjualan induk
        $stmt = $pdo->prepare("
            INSERT INTO penjualan (iduser, idmargin_penjualan, subtotal_nilai, ppn, total_nilai)
            VALUES (?, ?, 0, 0, 0)
        ");
        $stmt->execute([$iduser, $idmargin]);
        $idpenjualan = $pdo->lastInsertId();

        // Masukkan setiap item via Stored Procedure sp_tambah_detail_penjualan
        foreach ($items as $item) {
            $idbarang = (int)$item['idbarang'];
            $jumlah   = (int)$item['qty'];

            if ($jumlah <= 0) continue;

            $sp = $pdo->prepare("CALL sp_tambah_detail_penjualan(?, ?, ?)");
            $sp->execute([$idpenjualan, $idbarang, $jumlah]);
            $res = $sp->fetch();
            $sp->closeCursor();

            if ($res && isset($res['pesan']) && strpos($res['pesan'], 'DITOLAK') !== false) {
                // Batalkan transaksi
                $pdo->rollBack();
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Transaksi gagal: ' . $res['pesan'] . ' (Barang: ' . ($item['nama'] ?? "ID $idbarang") . ')'
                ]);
                exit;
            }
        }

        // Ambil data penjualan final yang sudah di-update oleh trigger
        $stmtCheck = $pdo->prepare("SELECT * FROM penjualan WHERE idpenjualan = ?");
        $stmtCheck->execute([$idpenjualan]);
        $sale = $stmtCheck->fetch();

        if (!$sale || $sale['total_nilai'] <= 0) {
            $pdo->rollBack();
            echo json_encode(['status' => 'error', 'message' => 'Total belanja tidak valid!']);
            exit;
        }

        if ($bayar_tunai < $sale['total_nilai']) {
            $pdo->rollBack();
            echo json_encode([
                'status' => 'error',
                'message' => 'Uang pembayaran kurang! Total belanja: ' . formatRupiah($sale['total_nilai']) . ', Pembayaran: ' . formatRupiah($bayar_tunai)
            ]);
            exit;
        }

        $pdo->commit();

        // Ambil detail penjualan untuk struk
        $stmtDetail = $pdo->prepare("
            SELECT dp.*, b.nama, s.nama_satuan
            FROM detail_penjualan dp
            JOIN barang b ON dp.idbarang = b.idbarang
            JOIN satuan s ON b.idsatuan = s.idsatuan
            WHERE dp.penjualan_idpenjualan = ?
        ");
        $stmtDetail->execute([$idpenjualan]);
        $details = $stmtDetail->fetchAll();

        echo json_encode([
            'status' => 'success',
            'message' => 'Transaksi berhasil diproses!',
            'data' => [
                'idpenjualan'   => $idpenjualan,
                'created_at'    => $sale['created_at'],
                'subtotal'      => (int)$sale['subtotal_nilai'],
                'ppn'           => (int)$sale['ppn'],
                'total'         => (int)$sale['total_nilai'],
                'bayar'         => $bayar_tunai,
                'kembali'       => $bayar_tunai - (int)$sale['total_nilai'],
                'kasir'         => getUsername(),
                'items'         => $details
            ]
        ]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Aksi tidak dikenali']);
