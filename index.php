<?php
session_start();
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

require_once 'koneksi.php';
$id_tim = $_SESSION['id_tim'];
$nama_user = $_SESSION['nama'];
$role = $_SESSION['role'];

// --- AMBIL DATA DASHBOARD ---
$q_tim = mysqli_query($koneksi, "SELECT * FROM tim WHERE id_tim = '$id_tim'");
$d_tim = mysqli_fetch_assoc($q_tim);

// Statistik
$res_p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as t FROM pemain WHERE id_tim = '$id_tim'"));
$res_pl = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as t FROM playbook WHERE id_tim = '$id_tim'"));
$res_i = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(jumlah) as t FROM inventaris WHERE id_tim = '$id_tim'"));
$q_rusak = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as t FROM inventaris WHERE id_tim = '$id_tim' AND kondisi = 'Perlu Diganti'"));
$q_kas = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(CASE WHEN tipe_transaksi='Masuk' THEN jumlah ELSE -jumlah END) as total FROM keuangan WHERE id_tim = '$id_tim'"));
$saldo_kas = $q_kas['total'] ?? 0;

// MVP / TOP SCORER
$q_mvp = mysqli_query($koneksi, "
    SELECT p.nama_pemain, IFNULL(SUM(s.touchdown), 0) as total_td 
    FROM pemain p 
    LEFT JOIN statistik_pemain s ON p.id_pemain = s.id_pemain 
    WHERE p.id_tim = '$id_tim' 
    GROUP BY p.id_pemain 
    ORDER BY total_td DESC 
    LIMIT 1
");
$d_mvp = mysqli_fetch_assoc($q_mvp);

// Agenda
$q_hari_ini = mysqli_query($koneksi, "SELECT * FROM jadwal_latihan WHERE id_tim = '$id_tim' AND tanggal = CURDATE() LIMIT 1");
$d_hari_ini = mysqli_fetch_assoc($q_hari_ini);
$is_today = false; $display_latihan = null;
if ($d_hari_ini) { $display_latihan = $d_hari_ini; $is_today = true; } 
else {
    $q_mendatang = mysqli_query($koneksi, "SELECT * FROM jadwal_latihan WHERE id_tim = '$id_tim' AND tanggal > CURDATE() ORDER BY tanggal ASC LIMIT 1");
    $display_latihan = mysqli_fetch_assoc($q_mendatang);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flag Manager SaaS - Dashboard Utama</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root { --primary-color: #0d6efd; --dark-navy: #1a1d20; }
        body { background-color: #f4f7f9; font-family: 'Inter', sans-serif; min-height: 100vh; display: flex; flex-direction: column; }
        .navbar { background: var(--dark-navy) !important; border-bottom: 3px solid #ffc107; padding: 0.8rem 0; }
        .nav-link { color: #adb5bd !important; font-size: 0.9rem; font-weight: 600; transition: 0.3s; margin: 0 5px; }
        .nav-link:hover { color: #fff !important; }
        .nav-link.active { color: #ffc107 !important; border-bottom: 2px solid #ffc107; }
        .card-custom { border: none; border-radius: 20px; transition: 0.3s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .card-custom:hover { transform: translateY(-5px); box-shadow: 0 12px 20px rgba(0,0,0,0.1); }
        .stat-card { text-decoration: none; color: inherit; display: block; position: relative; overflow: hidden; }
        .stat-icon { position: absolute; right: 20px; bottom: 15px; font-size: 3rem; opacity: 0.1; }
        .weather-gradient { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white; }
        .bg-today { background: #ffc107; color: #000; }
        .main-content { flex: 1; padding: 40px 0; }
        footer { background: #fff; border-top: 1px solid #eee; padding: 20px 0; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-football-ball text-warning me-2"></i>FLAG MANAGER</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto align-items-center">
        <li class="nav-item"><a class="nav-link <?= (!isset($_GET['menu']) || $_GET['menu'] == 'dashboard') ? 'active' : '' ?>" href="index.php">DASHBOARD</a></li>
        <li class="nav-item"><a class="nav-link <?= (isset($_GET['menu']) && $_GET['menu'] == 'roster') ? 'active' : '' ?>" href="index.php?menu=roster">ROSTER</a></li>
        <li class="nav-item"><a class="nav-link <?= (isset($_GET['menu']) && $_GET['menu'] == 'playbook') ? 'active' : '' ?>" href="index.php?menu=playbook">PLAYBOOK</a></li>
        <li class="nav-item"><a class="nav-link <?= (isset($_GET['menu']) && $_GET['menu'] == 'statistik') ? 'active' : '' ?>" href="index.php?menu=statistik">STATISTIK</a></li>
        
        <!-- MENU JADWAL DIKELUARKAN DARI IF AGAR SEMUA ROLE BISA LIHAT -->
        <li class="nav-item"><a class="nav-link <?= (isset($_GET['menu']) && $_GET['menu'] == 'jadwal') ? 'active' : '' ?>" href="index.php?menu=jadwal">JADWAL</a></li>

        <!-- MENU KHUSUS MANAGER -->
        <?php if($role == 'Manager'): ?>
            <li class="nav-item"><a class="nav-link <?= (isset($_GET['menu']) && $_GET['menu'] == 'inventaris') ? 'active' : '' ?>" href="index.php?menu=inventaris">EQUIPMENT</a></li>
            <li class="nav-item"><a class="nav-link <?= (isset($_GET['menu']) && $_GET['menu'] == 'kas') ? 'active' : '' ?>" href="index.php?menu=kas">KAS TIM</a></li>
        <?php endif; ?>
        
        <li class="nav-item ms-lg-3"><a class="btn btn-outline-danger btn-sm rounded-pill px-3 mt-1" href="logout.php" onclick="return confirm('Logout?')">KELUAR</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container main-content">
    <?php
    $menu = isset($_GET['menu']) ? $_GET['menu'] : 'dashboard';
    if ($menu == 'dashboard') { ?>
        
        <div class="row align-items-center mb-4">
            <div class="col-md-7">
                <h1 class="fw-bold text-dark mb-0">Halo, <?= explode(' ', $nama_user)[0] ?>! 👋 <span class="badge bg-warning text-dark fs-6 align-middle"><?= $role ?></span></h1>
                <div class="d-flex align-items-center mt-2">
                    <p class="text-muted fs-5 mb-0">Tim <strong><?= $d_tim['nama_tim'] ?></strong></p>
                    <?php if($role == 'Manager'): ?>
                        <div class="ms-3 p-2 px-3 bg-dark text-white rounded-pill shadow-sm" style="font-size: 0.85rem;">
                            <i class="fas fa-key text-warning me-2"></i>KODE GABUNG: <strong class="text-warning"><?= $d_tim['kode_tim'] ?? 'BELUM SET' ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <h4 id="clock" class="fw-bold text-primary mb-0"></h4>
                <p id="date" class="text-muted small fw-bold mb-0 text-uppercase"></p>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-8">
                <div class="card card-custom weather-gradient p-4 h-100 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="fw-bold mb-1 opacity-75"><i class="fas fa-sun me-2 text-warning"></i>CUACA HARI INI</h5>
                            <h1 class="display-4 fw-bold mb-0">31°C</h1>
                            <p class="fs-6 mb-0 text-warning fw-bold">Yogyakarta • Cerah Berawan</p>
                            <small class="opacity-75">Sangat mendukung untuk latihan tactical drill.</small>
                        </div>
                        <i class="fas fa-cloud-sun fa-5x opacity-25"></i>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card card-custom bg-dark text-white p-4 h-100 border-start border-5 border-success">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="fw-bold text-muted small mb-2"><?= $role == 'Manager' ? 'SALDO KAS TIM' : 'TRANSPARANSI KAS' ?></h6>
                            <h2 class="fw-bold mb-0 text-success">Rp <?= number_format($saldo_kas, 0, ',', '.') ?></h2>
                        </div>
                        <i class="fas fa-wallet fa-3x text-success opacity-50"></i>
                    </div>
                    <p class="small mb-2 text-light opacity-75">Keuangan operasional tim.</p>
                    <?php if($role == 'Manager'): ?>
                        <a href="index.php?menu=kas" class="btn btn-sm btn-outline-success mt-auto rounded-pill w-100">Kelola Keuangan &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <a href="index.php?menu=roster" class="stat-card card card-custom bg-white p-3 h-100 shadow-sm border-0">
                    <h6 class="text-muted small fw-bold text-uppercase">Roster</h6>
                    <h3 class="fw-bold mb-0 text-primary"><?= $res_p['t'] ?> <small class="fs-6 text-muted">Atlet</small></h3>
                    <i class="fas fa-users stat-icon"></i>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="index.php?menu=playbook" class="stat-card card card-custom bg-white p-3 h-100 shadow-sm border-0">
                    <h6 class="text-muted small fw-bold text-uppercase">Playbook</h6>
                    <h3 class="fw-bold mb-0 text-success"><?= $res_pl['t'] ?> <small class="fs-6 text-muted">Skema</small></h3>
                    <i class="fas fa-clipboard-list stat-icon"></i>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="index.php?menu=statistik" class="stat-card card card-custom bg-white p-3 h-100 shadow-sm border-0 border-bottom border-4 border-danger">
                    <h6 class="text-muted small fw-bold text-uppercase"><i class="fas fa-fire text-danger me-1"></i>Top Scorer</h6>
                    <?php if($d_mvp && $d_mvp['total_td'] > 0): ?>
                        <h4 class="fw-bold mb-0 text-dark"><?= explode(' ', $d_mvp['nama_pemain'])[0] ?></h4>
                        <p class="small text-danger fw-bold mb-0"><?= $d_mvp['total_td'] ?> TD</p>
                    <?php else: ?>
                        <h4 class="fw-bold mb-0 text-dark">-</h4>
                        <p class="small text-muted mb-0">Belum ada TD</p>
                    <?php endif; ?>
                    <i class="fas fa-trophy stat-icon text-warning opacity-25"></i>
                </a>
            </div>
            <?php if($role == 'Manager'): ?>
                <div class="col-6 col-md-3">
                    <a href="index.php?menu=inventaris" class="stat-card card card-custom <?= ($q_rusak['t'] > 0) ? 'bg-danger text-white' : 'bg-white' ?> p-3 h-100 shadow-sm border-0">
                        <h6 class="<?= ($q_rusak['t'] > 0) ? 'text-white-50' : 'text-muted' ?> small fw-bold text-uppercase">Logistik</h6>
                        <h3 class="fw-bold mb-0 <?= ($q_rusak['t'] > 0) ? '' : 'text-warning' ?>"><?= ($q_rusak['t'] > 0) ? $q_rusak['t'] : ($res_i['t'] ?? 0) ?> <small class="fs-6"><?= ($q_rusak['t'] > 0) ? 'Rusak' : 'Item' ?></small></h3>
                        <i class="fas fa-tools stat-icon"></i>
                    </a>
                </div>
            <?php else: ?>
                <div class="col-6 col-md-3">
                    <div class="stat-card card card-custom bg-white p-3 h-100 shadow-sm border-0">
                        <h6 class="text-muted small fw-bold text-uppercase">Status Tim</h6>
                        <h3 class="fw-bold mb-0 text-info"><i class="fas fa-check-circle"></i></h3>
                        <p class="small text-muted mb-0 mt-1" style="font-size: 0.75rem;">Siap Tanding</p>
                        <i class="fas fa-shield-alt stat-icon"></i>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-12">
            <div class="card card-custom border-0 shadow-sm overflow-hidden bg-white">
                <div class="row g-0">
                    <div class="col-md-3 <?= $is_today ? 'bg-today text-dark' : 'bg-primary text-white' ?> d-flex flex-column align-items-center justify-content-center p-4">
                        <?php if ($display_latihan): ?>
                            <h6 class="fw-bold mb-1 text-uppercase small opacity-75"><?= $is_today ? 'Hari Ini' : 'Mendatang' ?></h6>
                            <h1 class="display-3 fw-bold mb-0"><?= date('d', strtotime($display_latihan['tanggal'])) ?></h1>
                            <h5 class="fw-bold text-uppercase"><?= date('M Y', strtotime($display_latihan['tanggal'])) ?></h5>
                        <?php else: ?>
                            <i class="fas fa-calendar-times fa-3x opacity-50"></i><p class="small mt-2 mb-0">Kosong</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-9 p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                            <h5 class="fw-bold mb-0">AGENDA LATIHAN UTAMA</h5>
                            <?php if($role == 'Manager'): ?>
                                <a href="index.php?menu=jadwal" class="btn btn-sm btn-outline-dark rounded-pill px-3">Kelola Jadwal</a>
                            <?php endif; ?>
                        </div>
                        <?php if ($display_latihan): ?>
                            <h3 class="fw-bold text-primary mb-3"><?= $display_latihan['judul_latihan'] ?></h3>
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light p-2 rounded-3 me-2 text-primary"><i class="fas fa-clock"></i></div>
                                        <div><small class="text-muted d-block">Jam Latihan</small><span class="fw-bold"><?= substr($display_latihan['jam_mulai'], 0, 5) ?> WIB</span></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-light p-2 rounded-3 me-2 text-danger"><i class="fas fa-map-marker-alt"></i></div>
                                        <div><small class="text-muted d-block">Lokasi Lapangan</small><span class="fw-bold"><?= $display_latihan['lokasi'] ?></span></div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 rounded-3 bg-light border-start border-4 border-primary">
                                <small class="fw-bold d-block mb-1">Catatan Pelatih:</small>
                                <p class="small text-muted mb-0"><?= $display_latihan['keterangan'] ?: 'Tidak ada instruksi.' ?></p>
                            </div>
                        <?php else: ?>
                            <div class="py-4 text-center">
                                <p class="text-muted italic mb-2">Belum ada agenda latihan.</p>
                                <?php if($role == 'Manager'): ?>
                                    <a href="index.php?menu=jadwal" class="btn btn-primary btn-sm rounded-pill">Buat Jadwal</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php } else {
        if($menu == 'roster') include 'roster_web.php';
        else if($menu == 'jadwal') include 'jadwal_web.php';
        else if($menu == 'playbook') include 'playbook_web.php';
        else if($menu == 'inventaris') include 'inventaris_web.php';
        else if($menu == 'kas') include 'kas_web.php';
        else if($menu == 'statistik') include 'statistik_web.php';
        else include $menu . '.php';
    } ?>
</div>

<footer class="text-center"><p class="text-muted small mb-0">&copy; 2026 Flag Manager SaaS • Daggers Command Center</p></footer>
<script>
    function updateClock() {
        const now = new Date();
        document.getElementById('clock').innerText = now.toLocaleTimeString('id-ID', { hour12: false });
        document.getElementById('date').innerText = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    }
    setInterval(updateClock, 1000); updateClock();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>