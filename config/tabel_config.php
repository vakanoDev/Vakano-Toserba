<?php
// Daftar 15 tabel asli beserta kolomnya. Tambah/ubah tabel cukup di file ini.
function c($n, $l, $t = 'text', $o = []) { return ['n' => $n, 'l' => $l, 't' => $t] + $o; }

$AKTIF  = ['1' => 'Aktif', '0' => 'Nonaktif'];
$STATAN = ['A' => 'Aktif', 'N' => 'Nonaktif'];

$TABEL = [
  // ---------- MASTER ----------
  'role' => ['label' => 'Role', 'grup' => 'Master', 'ikon' => 'fa-user-shield', 'pk' => 'idrole', 'cols' => [
    c('nama_role', 'Nama Role', 'text', ['max' => 100]),
  ]],
  'user' => ['label' => 'User / Pegawai', 'grup' => 'Master', 'ikon' => 'fa-users', 'pk' => 'iduser', 'view' => 'akun_vu', 'vpk' => 'ID Akun', 'vcols' => [
    c('Username', 'Username'),
    c('Password Akun', 'Password'),
    c('Jabatan', 'Role'),
  ], 'cols' => [
    c('username', 'Username', 'text', ['max' => 45]),
    c('password', 'Password (SHA-256)', 'password'),
    c('idrole', 'Role', 'fk', ['ref' => 'role', 'lab' => 'nama_role']),
  ]],
  'satuan' => ['label' => 'Satuan', 'grup' => 'Master', 'ikon' => 'fa-scale-balanced', 'pk' => 'idsatuan', 'cols' => [
    c('nama_satuan', 'Nama Satuan', 'text', ['max' => 45]),
    c('status', 'Status', 'select', ['opt' => $AKTIF]),
  ]],
  'vendor' => ['label' => 'Vendor', 'grup' => 'Master', 'ikon' => 'fa-truck-field', 'pk' => 'idvendor', 'cols' => [
    c('nama_vendor', 'Nama Vendor', 'text', ['max' => 100]),
    c('badan_hukum', 'Badan Hukum', 'select', ['opt' => ['Y' => 'Ya', 'T' => 'Tidak']]),
    c('status', 'Status', 'select', ['opt' => $STATAN]),
  ]],
  'barang' => ['label' => 'Barang', 'grup' => 'Master', 'ikon' => 'fa-boxes-stacked', 'pk' => 'idbarang', 'cols' => [
    c('jenis', 'Kode Jenis (1 huruf)', 'text', ['max' => 1]),
    c('nama', 'Nama Barang', 'text', ['max' => 45]),
    c('idsatuan', 'Satuan', 'fk', ['ref' => 'satuan', 'lab' => 'nama_satuan']),
    c('status', 'Status', 'select', ['opt' => $AKTIF]),
    c('harga', 'Harga (Rp)', 'number'),
  ]],
  'margin_penjualan' => ['label' => 'Margin Penjualan', 'grup' => 'Master', 'ikon' => 'fa-percent', 'pk' => 'idmargin_penjualan', 'cols' => [
    c('persen', 'Persen (%)', 'decimal'),
    c('status', 'Status', 'select', ['opt' => $AKTIF]),
    c('iduser', 'Petugas', 'fk', ['ref' => 'user', 'lab' => 'username']),
    c('created_at', 'Dibuat', 'datetime'),
    c('updated_at', 'Diperbarui', 'datetime'),
  ]],

  // ---------- TRANSAKSI ----------
  'pengadaan' => ['label' => 'Pengadaan', 'grup' => 'Transaksi', 'ikon' => 'fa-cart-flatbed', 'pk' => 'idpengadaan', 'cols' => [
    c('timestamp', 'Waktu', 'datetime'),
    c('user_iduser', 'User', 'fk', ['ref' => 'user', 'lab' => 'username']),
    c('status', 'Status (1 huruf)', 'text', ['max' => 1]),
    c('vendor_idvendor', 'Vendor', 'fk', ['ref' => 'vendor', 'lab' => 'nama_vendor']),
    c('subtotal_nilai', 'Subtotal', 'number'),
    c('ppn', 'PPN', 'number'),
    c('total_nilai', 'Total', 'number'),
  ]],
  'detail_pengadaan' => ['label' => 'Detail Pengadaan', 'grup' => 'Transaksi', 'ikon' => 'fa-list-check', 'pk' => 'iddetail_pengadaan', 'cols' => [
    c('idpengadaan', 'Pengadaan', 'fk', ['ref' => 'pengadaan', 'lab' => '']),
    c('idbarang', 'Barang', 'fk', ['ref' => 'barang', 'lab' => 'nama']),
    c('harga_satuan', 'Harga Satuan', 'number'),
    c('jumlah', 'Jumlah', 'number'),
    c('sub_total', 'Sub Total', 'number'),
  ]],
  'penerimaan' => ['label' => 'Penerimaan', 'grup' => 'Transaksi', 'ikon' => 'fa-dolly', 'pk' => 'idpenerimaan', 'cols' => [
    c('created_at', 'Waktu', 'datetime'),
    c('status', 'Status (1 huruf)', 'text', ['max' => 1]),
    c('idpengadaan', 'Pengadaan', 'fk', ['ref' => 'pengadaan', 'lab' => '']),
    c('iduser', 'User', 'fk', ['ref' => 'user', 'lab' => 'username']),
  ]],
  'detail_penerimaan' => ['label' => 'Detail Penerimaan', 'grup' => 'Transaksi', 'ikon' => 'fa-clipboard-list', 'pk' => 'iddetail_penerimaan', 'cols' => [
    c('idpenerimaan', 'Penerimaan', 'fk', ['ref' => 'penerimaan', 'lab' => '']),
    c('barang_idbarang', 'Barang', 'fk', ['ref' => 'barang', 'lab' => 'nama']),
    c('jumlah_terima', 'Jumlah Terima', 'number'),
    c('harga_satuan_terima', 'Harga Satuan', 'number'),
    c('sub_total_terima', 'Sub Total', 'number'),
  ]],
  'retur' => ['label' => 'Retur', 'grup' => 'Transaksi', 'ikon' => 'fa-rotate-left', 'pk' => 'idretur', 'cols' => [
    c('created_at', 'Waktu', 'datetime'),
    c('idpenerimaan', 'Penerimaan', 'fk', ['ref' => 'penerimaan', 'lab' => '']),
    c('iduser', 'User', 'fk', ['ref' => 'user', 'lab' => 'username']),
  ]],
  'detail_retur' => ['label' => 'Detail Retur', 'grup' => 'Transaksi', 'ikon' => 'fa-file-circle-xmark', 'pk' => 'iddetail_retur', 'cols' => [
    c('idretur', 'Retur', 'fk', ['ref' => 'retur', 'lab' => '']),
    c('iddetail_penerimaan', 'Detail Penerimaan', 'fk', ['ref' => 'detail_penerimaan', 'lab' => '']),
    c('jumlah', 'Jumlah', 'number'),
    c('alasan', 'Alasan', 'textarea', ['max' => 200]),
  ]],
  'penjualan' => ['label' => 'Penjualan', 'grup' => 'Transaksi', 'ikon' => 'fa-cash-register', 'pk' => 'idpenjualan', 'cols' => [
    c('created_at', 'Waktu', 'datetime'),
    c('iduser', 'Kasir', 'fk', ['ref' => 'user', 'lab' => 'username']),
    c('idmargin_penjualan', 'Margin', 'fk', ['ref' => 'margin_penjualan', 'lab' => 'persen']),
    c('subtotal_nilai', 'Subtotal', 'number'),
    c('ppn', 'PPN', 'number'),
    c('total_nilai', 'Total', 'number'),
  ]],
  'detail_penjualan' => ['label' => 'Detail Penjualan', 'grup' => 'Transaksi', 'ikon' => 'fa-receipt', 'pk' => 'iddetail_penjualan', 'cols' => [
    c('penjualan_idpenjualan', 'Penjualan', 'fk', ['ref' => 'penjualan', 'lab' => '']),
    c('idbarang', 'Barang', 'fk', ['ref' => 'barang', 'lab' => 'nama']),
    c('harga_satuan', 'Harga Satuan', 'number'),
    c('jumlah', 'Jumlah', 'number'),
    c('subtotal', 'Subtotal', 'number'),
  ]],

  // ---------- STOK ----------
  'kartu_stok' => ['label' => 'Kartu Stok', 'grup' => 'Stok', 'ikon' => 'fa-warehouse', 'pk' => 'idkartu_stok', 'cols' => [
    c('idbarang', 'Barang', 'fk', ['ref' => 'barang', 'lab' => 'nama']),
    c('jenis_transaksi', 'Jenis Transaksi (1 huruf)', 'text', ['max' => 1]),
    c('idtransaksi', 'ID Transaksi', 'number'),
    c('masuk', 'Masuk', 'number'),
    c('keluar', 'Keluar', 'number'),
    c('stock', 'Stok', 'number'),
    c('created_at', 'Waktu', 'datetime'),
  ]],
];