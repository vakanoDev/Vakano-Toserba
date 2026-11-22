<?php
/**
 * Vakano Toserba - Database Configuration & Helper Functions
 */

$db_host = '127.0.0.1';
$db_name = 'db_fast_mart';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Koneksi Database Gagal: " . htmlspecialchars($e->getMessage()));
}

// Helper format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

// Helper sanitize input
function clean($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

// Ambil Margin Aktif
function getActiveMargin($pdo) {
    $stmt = $pdo->query("SELECT * FROM margin_penjualan WHERE status = 1 ORDER BY idmargin_penjualan DESC LIMIT 1");
    return $stmt->fetch() ?: ['idmargin_penjualan' => 1, 'persen' => 10];
}
