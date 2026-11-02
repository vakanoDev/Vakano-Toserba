<?php
// Daftar 15 tabel. Setiap tabel punya query SQL apa adanya:
//   'sql'   = daftar (kolom pertama = ID), 'ambil' = isi form ubah,
//   'tambah' / 'ubah' / 'hapus' = simpan data. Tanda ? diisi nilai dari form berurutan sesuai 'cols'.
// 'cols' hanya menentukan isian form (label & jenis input).
function c(string $n, string $l, string $t = 'text', array $o = []): array { return ['n' => $n, 'l' => $l, 't' => $t] + $o; }

$AKTIF  = ['1' => 'Aktif', '0' => 'Nonaktif'];
$STATAN = ['A' => 'Aktif', 'N' => 'Nonaktif'];

// Query isi dropdown relasi (kolom 'id' dan 'lab')
$PILIH_SATUAN = "SELECT idsatuan AS id, nama_satuan AS lab FROM satuan_form_vu ORDER BY idsatuan";
$PILIH_USER = "SELECT iduser AS id, username AS lab FROM akun_form_vu ORDER BY iduser";
$PILIH_ROLE = "SELECT idrole AS id, nama_role AS lab FROM role_form_vu ORDER BY idrole";
$PILIH_VENDOR = "SELECT idvendor AS id, nama_vendor AS lab FROM vendor_form_vu ORDER BY idvendor";
$PILIH_BARANG = "SELECT idbarang AS id, nama AS lab FROM barang_form_vu ORDER BY idbarang";
$PILIH_MARGIN = "SELECT idmargin_penjualan AS id, persen AS lab FROM margin_form_vu ORDER BY idmargin_penjualan";
$PILIH_PENGADAAN = "SELECT idpengadaan AS id, NULL AS lab FROM pengadaan ORDER BY idpengadaan";
$PILIH_PENERIMAAN = "SELECT idpenerimaan AS id, NULL AS lab FROM penerimaan ORDER BY idpenerimaan";
$PILIH_DETAIL_PENERIMAAN = "SELECT iddetail_penerimaan AS id, NULL AS lab FROM detail_penerimaan ORDER BY iddetail_penerimaan";
$PILIH_RETUR = "SELECT idretur AS id, NULL AS lab FROM retur ORDER BY idretur";
$PILIH_PENJUALAN = "SELECT idpenjualan AS id, NULL AS lab FROM penjualan ORDER BY idpenjualan";

$TABEL = [

  // ---------- MASTER ----------
  'role' => [
    'label' => 'Role', 'grup' => 'Master', 'ikon' => 'fa-user-shield', 'pk' => 'idrole',

    'sql'    => "SELECT ID_Jabatan AS `ID`,
                  Jabatan AS `Nama Role`,
                  Jumlah_Pegawai AS `Jumlah Pegawai`
           FROM role_vu
           ORDER BY ID_Jabatan DESC",
    'ambil'  => "SELECT * FROM role_form_vu WHERE idrole = ?",
    'tambah' => "INSERT INTO role_form_vu (nama_role) VALUES (?)",
    'ubah'   => "UPDATE role_form_vu SET nama_role = ? WHERE idrole = ?",
    'hapus'  => "DELETE FROM role_form_vu WHERE idrole = ?",

    'cols' => [
    c('nama_role', 'Nama Role', 'text', ['max' => 100]),
    ],
  ],
  'user' => [
    'label' => 'User / Pegawai', 'grup' => 'Master', 'ikon' => 'fa-users', 'pk' => 'iduser',

    'sql'    => "SELECT `ID Akun` AS `ID`,
                  Username AS `Username`,
                  `Password Akun` AS `Password (SHA-256)`,
                  Jabatan AS `Role`
           FROM akun_vu
           ORDER BY `ID Akun` DESC",
    'ambil'  => "SELECT * FROM akun_form_vu WHERE iduser = ?",
    'tambah' => "INSERT INTO akun_form_vu (username, password, idrole) VALUES (?, ?, ?)",
    'ubah'   => "UPDATE akun_form_vu SET username = ?, password = ?, idrole = ? WHERE iduser = ?",
    'ubah_tanpa_password' => "UPDATE akun_form_vu SET username = ?, idrole = ? WHERE iduser = ?",
    'hapus'  => "DELETE FROM akun_form_vu WHERE iduser = ?",

    'cols' => [
    c('username', 'Username', 'text', ['max' => 45]),
    c('password', 'Password (SHA-256)', 'password'),
    c('idrole', 'Role', 'fk', ['pilih' => $PILIH_ROLE]),
    ],
  ],
  'satuan' => [
    'label' => 'Satuan', 'grup' => 'Master', 'ikon' => 'fa-scale-balanced', 'pk' => 'idsatuan',

    'sql'    => "SELECT idsatuan AS `ID`,
                  nama_satuan AS `Nama Satuan`,
                  Status_Satuan AS `Status`
           FROM satuan_vu
           ORDER BY idsatuan DESC",
    'ambil'  => "SELECT * FROM satuan_form_vu WHERE idsatuan = ?",
    'tambah' => "INSERT INTO satuan_form_vu (nama_satuan, status) VALUES (?, ?)",
    'ubah'   => "UPDATE satuan_form_vu SET nama_satuan = ?, status = ? WHERE idsatuan = ?",
    'hapus'  => "DELETE FROM satuan_form_vu WHERE idsatuan = ?",

    'cols' => [
    c('nama_satuan', 'Nama Satuan', 'text', ['max' => 45]),
    c('status', 'Status', 'select', ['opt' => $AKTIF]),
    ],
  ],
  'vendor' => [
    'label' => 'Vendor', 'grup' => 'Master', 'ikon' => 'fa-truck-field', 'pk' => 'idvendor',

    'sql'    => "SELECT idvendor AS `ID`,
                  nama_vendor AS `Nama Vendor`,
                  Badan_Hukum AS `Badan Hukum`,
                  Status_Vendor AS `Status`
           FROM vendor_vu
           ORDER BY idvendor DESC",
    'ambil'  => "SELECT * FROM vendor_form_vu WHERE idvendor = ?",
    'tambah' => "INSERT INTO vendor_form_vu (nama_vendor, badan_hukum, status) VALUES (?, ?, ?)",
    'ubah'   => "UPDATE vendor_form_vu SET nama_vendor = ?, badan_hukum = ?, status = ? WHERE idvendor = ?",
    'hapus'  => "DELETE FROM vendor_form_vu WHERE idvendor = ?",

    'cols' => [
    c('nama_vendor', 'Nama Vendor', 'text', ['max' => 100]),
    c('badan_hukum', 'Badan Hukum', 'select', ['opt' => ['Y' => 'Ya', 'T' => 'Tidak']]),
    c('status', 'Status', 'select', ['opt' => $STATAN]),
    ],
  ],
  'barang' => [
    'label' => 'Barang', 'grup' => 'Master', 'ikon' => 'fa-boxes-stacked', 'pk' => 'idbarang',

    'sql'    => "SELECT idbarang AS `ID`,
                  jenis AS `Kode Jenis`,
                  nama AS `Nama Barang`,
                  nama_satuan AS `Satuan`,
                  Status_Barang AS `Status`,
                  harga AS `Harga (Rp)`
           FROM barang_vu
           ORDER BY idbarang DESC",
    'ambil'  => "SELECT * FROM barang_form_vu WHERE idbarang = ?",
    'tambah' => "INSERT INTO barang_form_vu (jenis, nama, idsatuan, status, harga) VALUES (?, ?, ?, ?, ?)",
    'ubah'   => "UPDATE barang_form_vu SET jenis = ?, nama = ?, idsatuan = ?, status = ?, harga = ? WHERE idbarang = ?",
    'hapus'  => "DELETE FROM barang_form_vu WHERE idbarang = ?",

    'cols' => [
    c('jenis', 'Kode Jenis (1 huruf)', 'text', ['max' => 1]),
    c('nama', 'Nama Barang', 'text', ['max' => 45]),
    c('idsatuan', 'Satuan', 'fk', ['pilih' => $PILIH_SATUAN]),
    c('status', 'Status', 'select', ['opt' => $AKTIF]),
    c('harga', 'Harga (Rp)', 'number'),
    ],
  ],
  'margin_penjualan' => [
    'label' => 'Margin Penjualan', 'grup' => 'Master', 'ikon' => 'fa-percent', 'pk' => 'idmargin_penjualan',

    'sql'    => "SELECT Id_Margin AS `ID`,
                  Profit_Persen AS `Profit`,
                  Status_Margin AS `Status`,
                  Petugas AS `Petugas`,
                  jabatan AS `Jabatan`,
                  Tanggal_Masuk AS `Dibuat`,
                  Tanggal_Update AS `Diperbarui`
           FROM margin_vu
           ORDER BY Id_Margin DESC",
    'ambil'  => "SELECT * FROM margin_form_vu WHERE idmargin_penjualan = ?",
    'tambah' => "INSERT INTO margin_form_vu (persen, status, iduser, created_at, updated_at) VALUES (?, ?, ?, ?, ?)",
    'ubah'   => "UPDATE margin_form_vu SET persen = ?, status = ?, iduser = ?, created_at = ?, updated_at = ? WHERE idmargin_penjualan = ?",
    'hapus'  => "DELETE FROM margin_form_vu WHERE idmargin_penjualan = ?",

    'cols' => [
    c('persen', 'Persen (%)', 'decimal'),
    c('status', 'Status', 'select', ['opt' => $AKTIF]),
    c('iduser', 'Petugas', 'fk', ['pilih' => $PILIH_USER]),
    c('created_at', 'Dibuat', 'datetime'),
    c('updated_at', 'Diperbarui', 'datetime'),
    ],
  ],

  // ---------- TRANSAKSI ----------
  'pengadaan' => [
    'label' => 'Pengadaan', 'grup' => 'Transaksi', 'ikon' => 'fa-cart-flatbed', 'pk' => 'idpengadaan',

    'sql'    => "SELECT p.idpengadaan AS `ID`,
                  p.timestamp AS `Waktu`,
                  u.username AS `User`,
                  p.status AS `Status`,
                  v.nama_vendor AS `Vendor`,
                  p.subtotal_nilai AS `Subtotal`,
                  p.ppn AS `PPN`,
                  p.total_nilai AS `Total`
           FROM pengadaan p
           LEFT JOIN akun_form_vu u ON p.user_iduser = u.iduser
           LEFT JOIN vendor_form_vu v ON p.vendor_idvendor = v.idvendor
           ORDER BY p.idpengadaan DESC LIMIT 500",
    'ambil'  => "SELECT * FROM pengadaan WHERE idpengadaan = ?",
    'tambah' => "INSERT INTO pengadaan (timestamp, user_iduser, status, vendor_idvendor, subtotal_nilai, ppn, total_nilai) VALUES (?, ?, ?, ?, ?, ?, ?)",
    'ubah'   => "UPDATE pengadaan SET timestamp = ?, user_iduser = ?, status = ?, vendor_idvendor = ?, subtotal_nilai = ?, ppn = ?, total_nilai = ? WHERE idpengadaan = ?",
    'hapus'  => "DELETE FROM pengadaan WHERE idpengadaan = ?",

    'cols' => [
    c('timestamp', 'Waktu', 'datetime'),
    c('user_iduser', 'User', 'fk', ['pilih' => $PILIH_USER]),
    c('status', 'Status (1 huruf)', 'text', ['max' => 1]),
    c('vendor_idvendor', 'Vendor', 'fk', ['pilih' => $PILIH_VENDOR]),
    c('subtotal_nilai', 'Subtotal', 'number'),
    c('ppn', 'PPN', 'number'),
    c('total_nilai', 'Total', 'number'),
    ],
  ],
  'detail_pengadaan' => [
    'label' => 'Detail Pengadaan', 'grup' => 'Transaksi', 'ikon' => 'fa-list-check', 'pk' => 'iddetail_pengadaan',

    'sql'    => "SELECT dp.iddetail_pengadaan AS `ID`,
                  dp.idpengadaan AS `Pengadaan`,
                  b.nama AS `Barang`,
                  dp.harga_satuan AS `Harga Satuan`,
                  dp.jumlah AS `Jumlah`,
                  dp.sub_total AS `Sub Total`
           FROM detail_pengadaan dp
           LEFT JOIN barang_form_vu b ON dp.idbarang = b.idbarang
           ORDER BY dp.iddetail_pengadaan DESC LIMIT 500",
    'ambil'  => "SELECT * FROM detail_pengadaan WHERE iddetail_pengadaan = ?",
    'tambah' => "INSERT INTO detail_pengadaan (idpengadaan, idbarang, harga_satuan, jumlah, sub_total) VALUES (?, ?, ?, ?, ?)",
    'ubah'   => "UPDATE detail_pengadaan SET idpengadaan = ?, idbarang = ?, harga_satuan = ?, jumlah = ?, sub_total = ? WHERE iddetail_pengadaan = ?",
    'hapus'  => "DELETE FROM detail_pengadaan WHERE iddetail_pengadaan = ?",

    'cols' => [
    c('idpengadaan', 'Pengadaan', 'fk', ['pilih' => $PILIH_PENGADAAN]),
    c('idbarang', 'Barang', 'fk', ['pilih' => $PILIH_BARANG]),
    c('harga_satuan', 'Harga Satuan', 'number'),
    c('jumlah', 'Jumlah', 'number'),
    c('sub_total', 'Sub Total', 'number'),
    ],
  ],
  'penerimaan' => [
    'label' => 'Penerimaan', 'grup' => 'Transaksi', 'ikon' => 'fa-dolly', 'pk' => 'idpenerimaan',

    'sql'    => "SELECT pn.idpenerimaan AS `ID`,
                  pn.created_at AS `Waktu`,
                  pn.status AS `Status`,
                  pn.idpengadaan AS `Pengadaan`,
                  u.username AS `User`
           FROM penerimaan pn
           LEFT JOIN akun_form_vu u ON pn.iduser = u.iduser
           ORDER BY pn.idpenerimaan DESC LIMIT 500",
    'ambil'  => "SELECT * FROM penerimaan WHERE idpenerimaan = ?",
    'tambah' => "INSERT INTO penerimaan (created_at, status, idpengadaan, iduser) VALUES (?, ?, ?, ?)",
    'ubah'   => "UPDATE penerimaan SET created_at = ?, status = ?, idpengadaan = ?, iduser = ? WHERE idpenerimaan = ?",
    'hapus'  => "DELETE FROM penerimaan WHERE idpenerimaan = ?",

    'cols' => [
    c('created_at', 'Waktu', 'datetime'),
    c('status', 'Status (1 huruf)', 'text', ['max' => 1]),
    c('idpengadaan', 'Pengadaan', 'fk', ['pilih' => $PILIH_PENGADAAN]),
    c('iduser', 'User', 'fk', ['pilih' => $PILIH_USER]),
    ],
  ],
  'detail_penerimaan' => [
    'label' => 'Detail Penerimaan', 'grup' => 'Transaksi', 'ikon' => 'fa-clipboard-list', 'pk' => 'iddetail_penerimaan',

    'sql'    => "SELECT dn.iddetail_penerimaan AS `ID`,
                  dn.idpenerimaan AS `Penerimaan`,
                  b.nama AS `Barang`,
                  dn.jumlah_terima AS `Jumlah Terima`,
                  dn.harga_satuan_terima AS `Harga Satuan`,
                  dn.sub_total_terima AS `Sub Total`
           FROM detail_penerimaan dn
           LEFT JOIN barang_form_vu b ON dn.barang_idbarang = b.idbarang
           ORDER BY dn.iddetail_penerimaan DESC LIMIT 500",
    'ambil'  => "SELECT * FROM detail_penerimaan WHERE iddetail_penerimaan = ?",
    'tambah' => "INSERT INTO detail_penerimaan (idpenerimaan, barang_idbarang, jumlah_terima, harga_satuan_terima, sub_total_terima) VALUES (?, ?, ?, ?, ?)",
    'ubah'   => "UPDATE detail_penerimaan SET idpenerimaan = ?, barang_idbarang = ?, jumlah_terima = ?, harga_satuan_terima = ?, sub_total_terima = ? WHERE iddetail_penerimaan = ?",
    'hapus'  => "DELETE FROM detail_penerimaan WHERE iddetail_penerimaan = ?",

    'cols' => [
    c('idpenerimaan', 'Penerimaan', 'fk', ['pilih' => $PILIH_PENERIMAAN]),
    c('barang_idbarang', 'Barang', 'fk', ['pilih' => $PILIH_BARANG]),
    c('jumlah_terima', 'Jumlah Terima', 'number'),
    c('harga_satuan_terima', 'Harga Satuan', 'number'),
    c('sub_total_terima', 'Sub Total', 'number'),
    ],
  ],
  'retur' => [
    'label' => 'Retur', 'grup' => 'Transaksi', 'ikon' => 'fa-rotate-left', 'pk' => 'idretur',

    'sql'    => "SELECT r.idretur AS `ID`,
                  r.created_at AS `Waktu`,
                  r.idpenerimaan AS `Penerimaan`,
                  u.username AS `User`
           FROM retur r
           LEFT JOIN akun_form_vu u ON r.iduser = u.iduser
           ORDER BY r.idretur DESC LIMIT 500",
    'ambil'  => "SELECT * FROM retur WHERE idretur = ?",
    'tambah' => "INSERT INTO retur (created_at, idpenerimaan, iduser) VALUES (?, ?, ?)",
    'ubah'   => "UPDATE retur SET created_at = ?, idpenerimaan = ?, iduser = ? WHERE idretur = ?",
    'hapus'  => "DELETE FROM retur WHERE idretur = ?",

    'cols' => [
    c('created_at', 'Waktu', 'datetime'),
    c('idpenerimaan', 'Penerimaan', 'fk', ['pilih' => $PILIH_PENERIMAAN]),
    c('iduser', 'User', 'fk', ['pilih' => $PILIH_USER]),
    ],
  ],
  'detail_retur' => [
    'label' => 'Detail Retur', 'grup' => 'Transaksi', 'ikon' => 'fa-file-circle-xmark', 'pk' => 'iddetail_retur',

    'sql'    => "SELECT dr.iddetail_retur AS `ID`,
                  dr.idretur AS `Retur`,
                  dr.iddetail_penerimaan AS `Detail Penerimaan`,
                  dr.jumlah AS `Jumlah`,
                  dr.alasan AS `Alasan`
           FROM detail_retur dr
           ORDER BY dr.iddetail_retur DESC LIMIT 500",
    'ambil'  => "SELECT * FROM detail_retur WHERE iddetail_retur = ?",
    'tambah' => "INSERT INTO detail_retur (idretur, iddetail_penerimaan, jumlah, alasan) VALUES (?, ?, ?, ?)",
    'ubah'   => "UPDATE detail_retur SET idretur = ?, iddetail_penerimaan = ?, jumlah = ?, alasan = ? WHERE iddetail_retur = ?",
    'hapus'  => "DELETE FROM detail_retur WHERE iddetail_retur = ?",

    'cols' => [
    c('idretur', 'Retur', 'fk', ['pilih' => $PILIH_RETUR]),
    c('iddetail_penerimaan', 'Detail Penerimaan', 'fk', ['pilih' => $PILIH_DETAIL_PENERIMAAN]),
    c('jumlah', 'Jumlah', 'number'),
    c('alasan', 'Alasan', 'textarea', ['max' => 200]),
    ],
  ],
  'penjualan' => [
    'label' => 'Penjualan', 'grup' => 'Transaksi', 'ikon' => 'fa-cash-register', 'pk' => 'idpenjualan',

    'sql'    => "SELECT pj.idpenjualan AS `ID`,
                  pj.created_at AS `Waktu`,
                  u.username AS `Kasir`,
                  CONCAT(m.persen, '%') AS `Margin`,
                  pj.subtotal_nilai AS `Subtotal`,
                  pj.ppn AS `PPN`,
                  pj.total_nilai AS `Total`
           FROM penjualan pj
           LEFT JOIN akun_form_vu u ON pj.iduser = u.iduser
           LEFT JOIN margin_form_vu m ON pj.idmargin_penjualan = m.idmargin_penjualan
           ORDER BY pj.idpenjualan DESC LIMIT 500",
    'ambil'  => "SELECT * FROM penjualan WHERE idpenjualan = ?",
    'tambah' => "INSERT INTO penjualan (created_at, iduser, idmargin_penjualan, subtotal_nilai, ppn, total_nilai) VALUES (?, ?, ?, ?, ?, ?)",
    'ubah'   => "UPDATE penjualan SET created_at = ?, iduser = ?, idmargin_penjualan = ?, subtotal_nilai = ?, ppn = ?, total_nilai = ? WHERE idpenjualan = ?",
    'hapus'  => "DELETE FROM penjualan WHERE idpenjualan = ?",

    'cols' => [
    c('created_at', 'Waktu', 'datetime'),
    c('iduser', 'Kasir', 'fk', ['pilih' => $PILIH_USER]),
    c('idmargin_penjualan', 'Margin', 'fk', ['pilih' => $PILIH_MARGIN]),
    c('subtotal_nilai', 'Subtotal', 'number'),
    c('ppn', 'PPN', 'number'),
    c('total_nilai', 'Total', 'number'),
    ],
  ],
  'detail_penjualan' => [
    'label' => 'Detail Penjualan', 'grup' => 'Transaksi', 'ikon' => 'fa-receipt', 'pk' => 'iddetail_penjualan',

    'sql'    => "SELECT dj.iddetail_penjualan AS `ID`,
                  dj.penjualan_idpenjualan AS `Penjualan`,
                  b.nama AS `Barang`,
                  dj.harga_satuan AS `Harga Satuan`,
                  dj.jumlah AS `Jumlah`,
                  dj.subtotal AS `Subtotal`
           FROM detail_penjualan dj
           LEFT JOIN barang_form_vu b ON dj.idbarang = b.idbarang
           ORDER BY dj.iddetail_penjualan DESC LIMIT 500",
    'ambil'  => "SELECT * FROM detail_penjualan WHERE iddetail_penjualan = ?",
    'tambah' => "INSERT INTO detail_penjualan (penjualan_idpenjualan, idbarang, harga_satuan, jumlah, subtotal) VALUES (?, ?, ?, ?, ?)",
    'ubah'   => "UPDATE detail_penjualan SET penjualan_idpenjualan = ?, idbarang = ?, harga_satuan = ?, jumlah = ?, subtotal = ? WHERE iddetail_penjualan = ?",
    'hapus'  => "DELETE FROM detail_penjualan WHERE iddetail_penjualan = ?",

    'cols' => [
    c('penjualan_idpenjualan', 'Penjualan', 'fk', ['pilih' => $PILIH_PENJUALAN]),
    c('idbarang', 'Barang', 'fk', ['pilih' => $PILIH_BARANG]),
    c('harga_satuan', 'Harga Satuan', 'number'),
    c('jumlah', 'Jumlah', 'number'),
    c('subtotal', 'Subtotal', 'number'),
    ],
  ],

  // ---------- STOK ----------
  'kartu_stok' => [
    'label' => 'Kartu Stok', 'grup' => 'Stok', 'ikon' => 'fa-warehouse', 'pk' => 'idkartu_stok',

    'sql'    => "SELECT k.idkartu_stok AS `ID`,
                  b.nama AS `Barang`,
                  k.jenis_transaksi AS `Jenis Transaksi`,
                  k.idtransaksi AS `ID Transaksi`,
                  k.masuk AS `Masuk`,
                  k.keluar AS `Keluar`,
                  k.stock AS `Stok`,
                  k.created_at AS `Waktu`
           FROM kartu_stok k
           LEFT JOIN barang_form_vu b ON k.idbarang = b.idbarang
           ORDER BY k.idkartu_stok DESC LIMIT 500",
    'ambil'  => "SELECT * FROM kartu_stok WHERE idkartu_stok = ?",
    'tambah' => "INSERT INTO kartu_stok (idbarang, jenis_transaksi, idtransaksi, masuk, keluar, stock, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
    'ubah'   => "UPDATE kartu_stok SET idbarang = ?, jenis_transaksi = ?, idtransaksi = ?, masuk = ?, keluar = ?, stock = ?, created_at = ? WHERE idkartu_stok = ?",
    'hapus'  => "DELETE FROM kartu_stok WHERE idkartu_stok = ?",

    'cols' => [
    c('idbarang', 'Barang', 'fk', ['pilih' => $PILIH_BARANG]),
    c('jenis_transaksi', 'Jenis Transaksi (1 huruf)', 'text', ['max' => 1]),
    c('idtransaksi', 'ID Transaksi', 'number'),
    c('masuk', 'Masuk', 'number'),
    c('keluar', 'Keluar', 'number'),
    c('stock', 'Stok', 'number'),
    c('created_at', 'Waktu', 'datetime'),
    ],
  ],
];