<?php /* butuh: $judul, $aktif ('dashboard' atau nama tabel) */
/** @var array $TABEL */ ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($judul); ?> - Vakano Toserba</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body { background:#0b0f19; font-family:'Poppins',sans-serif; color:#fefefe; }
        .sidebar { background:rgba(15,23,42,.95); min-height:100vh; border-right:1px solid rgba(244,238,238,.1); }
        .nav-link { color:#f7f7f7; border-radius:10px; margin-bottom:3px; font-size:.9rem; transition:all .3s ease; }
        .nav-link:hover,.nav-link.active { background:linear-gradient(135deg,#2563eb 0%,#1d4ed8 100%); color:#fff; }
        .nav-link:hover { background:linear-gradient(135deg,#3b82f6 0%,#2563eb 100%); box-shadow:0 0 14px rgba(96,165,250,.7); }
        .card-custom { background:rgba(30,41,59,.7); backdrop-filter:blur(10px); border:1px solid rgba(244,237,237,.1); border-radius:15px; color:#fefefe; }
        .table { --bs-table-bg:transparent; --bs-table-color:#fefefe; --bs-table-border-color:rgba(255,255,255,.1);
                 --bs-table-hover-color:#fff; --bs-table-hover-bg:rgba(59,130,246,.35); }
        /* efek terang / bercahaya saat baris disentuh */
        .table-hover > tbody > tr { transition:all .25s ease; }
        .table-hover > tbody > tr:hover > * { background-color:rgba(59,130,246,.35); color:#fff; box-shadow:inset 0 0 20px rgba(96,165,250,.35); }
        .table-hover > tbody > tr:hover { filter:drop-shadow(0 0 8px rgba(96,165,250,.6)); }
        .form-control,.form-select { background:#0f172a; color:#fff; border-color:rgba(255,255,255,.2); }
        .form-control:focus,.form-select:focus { background:#0f172a; color:#fff; }
        a.stat { text-decoration:none; color:inherit; transition:transform .2s; display:block; }
        a.stat:hover { transform:translateY(-3px); }
    </style>
</head>
<body>
<div class="container-fluid">
<div class="row">
    <div class="col-md-3 col-lg-2 sidebar p-3 d-flex flex-column justify-content-between">
        <div>
            <h5 class="fw-bold text-warning mb-3"><img src="../assets/img/LogoBerandaUner.png" class="img-fluid me-2" style="max-width:30px;" alt="">Vakano Admin</h5>
            <ul class="nav nav-pills flex-column mb-auto">
                <li><a href="dashboard_admin.php" class="nav-link <?= $aktif === 'dashboard' ? 'active' : ''; ?>"><i class="fa-solid fa-house me-2"></i> Beranda Admin</a></li>
                <?php $grupSekarang = '';
                foreach ($TABEL as $kode => $tb):
                    if ($tb['grup'] !== $grupSekarang): $grupSekarang = $tb['grup']; ?>
                        <li class="mt-3 mb-1"><small class="text-white-50 text-uppercase fw-bold" style="font-size:.7rem;"><?= e($grupSekarang); ?></small></li>
                    <?php endif; ?>
                    <li><a href="tabel.php?t=<?= e($kode); ?>" class="nav-link <?= $aktif === $kode ? 'active' : ''; ?>"><i class="fa-solid <?= e($tb['ikon']); ?> me-2"></i> <?= e($tb['label']); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="pt-3 mt-3 border-top border-secondary border-opacity-25">
            <span class="d-block small text-white-50 mb-2">Admin: <b><?= e($_SESSION['username'] ?? ''); ?></b></span>
            <a href="../logout.php" class="btn btn-danger btn-sm w-100 rounded-pill fw-bold"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a>
        </div>
    </div>
    <div class="col-md-9 col-lg-10 p-4 p-md-5">