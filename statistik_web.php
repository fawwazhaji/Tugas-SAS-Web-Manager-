<?php
require_once 'koneksi.php';
$id_tim = $_SESSION['id_tim'];
$role = $_SESSION['role'];

// --- LOGIKA UPDATE STATISTIK (HANYA MANAGER) ---
if ($role == 'Manager' && isset($_GET['action']) && isset($_GET['type']) && isset($_GET['id_p'])) {
    $id_p = mysqli_real_escape_string($koneksi, $_GET['id_p']);
    $type = mysqli_real_escape_string($koneksi, $_GET['type']); // touchdown, interception, sack, tackle
    $action = $_GET['action'];

    // Pastikan tipe data yang diupdate valid (Keamanan)
    if (in_array($type, ['touchdown', 'interception', 'sack', 'tackle'])) {
        // Cek apakah pemain sudah punya record stat
        $cek = mysqli_query($koneksi, "SELECT id_stat FROM statistik_pemain WHERE id_pemain = '$id_p'");
        if(mysqli_num_rows($cek) == 0) {
            mysqli_query($koneksi, "INSERT INTO statistik_pemain (id_pemain) VALUES ('$id_p')");
        }

        // Tambah atau Kurangi
        if ($action == 'plus') {
            mysqli_query($koneksi, "UPDATE statistik_pemain SET $type = $type + 1 WHERE id_pemain = '$id_p'");
        } else if ($action == 'minus') {
            mysqli_query($koneksi, "UPDATE statistik_pemain SET $type = GREATEST(0, $type - 1) WHERE id_pemain = '$id_p'");
        }
    }
    echo "<script>window.location.href='index.php?menu=statistik';</script>";
    exit;
}

// --- AMBIL DATA STATISTIK SEMUA PEMAIN ---
$q_stats = mysqli_query($koneksi, "
    SELECT p.id_pemain, p.nama_pemain, p.posisi, p.nomor_punggung,
           IFNULL(SUM(s.touchdown), 0) as td, 
           IFNULL(SUM(s.interception), 0) as intc, 
           IFNULL(SUM(s.sack), 0) as sack, 
           IFNULL(SUM(s.tackle), 0) as tackle 
    FROM pemain p 
    LEFT JOIN statistik_pemain s ON p.id_pemain = s.id_pemain 
    WHERE p.id_tim = '$id_tim' 
    GROUP BY p.id_pemain 
    ORDER BY td DESC, intc DESC, sack DESC
");
?>

<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-chart-bar text-danger me-2"></i>Leaderboard Performa</h4>
            <p class="text-muted small mb-0">Statistik pemain selama musim berjalan.</p>
        </div>
        <?php if($role == 'Manager'): ?>
            <span class="badge bg-warning text-dark"><i class="fas fa-edit me-1"></i>Edit Mode: ON</span>
        <?php else: ?>
            <span class="badge bg-primary"><i class="fas fa-eye me-1"></i>View Mode</span>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle border-top">
            <thead class="table-light">
                <tr>
                    <th>Rank</th>
                    <th>Pemain</th>
                    <th class="text-center">Touchdown (TD)</th>
                    <th class="text-center">Interception (INT)</th>
                    <th class="text-center">Sacks</th>
                    <th class="text-center">Tackles</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $rank = 1;
                while($row = mysqli_fetch_assoc($q_stats)): 
                    // Warna highlight untuk Top 3
                    $badge_color = 'bg-secondary';
                    if($rank == 1) $badge_color = 'bg-warning text-dark';
                    if($rank == 2) $badge_color = 'bg-secondary text-white'; // Silver
                    if($rank == 3) $badge_color = 'bg-danger text-white'; // Bronze
                ?>
                <tr>
                    <td style="width: 50px;">
                        <span class="badge <?= $badge_color ?> rounded-pill fs-6 w-100"><?= $rank ?></span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center fw-bold me-3" style="width: 40px; height: 40px;">
                                <?= $row['nomor_punggung'] ?>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark"><?= $row['nama_pemain'] ?></h6>
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;"><?= $row['posisi'] ?></small>
                            </div>
                        </div>
                    </td>
                    
                    <td class="text-center">
                        <h5 class="fw-bold text-primary mb-0"><?= $row['td'] ?></h5>
                        <?php if($role == 'Manager'): ?>
                            <div class="mt-1">
                                <a href="index.php?menu=statistik&action=minus&type=touchdown&id_p=<?= $row['id_pemain'] ?>" class="btn btn-sm btn-light py-0 px-2">-</a>
                                <a href="index.php?menu=statistik&action=plus&type=touchdown&id_p=<?= $row['id_pemain'] ?>" class="btn btn-sm btn-primary py-0 px-2">+</a>
                            </div>
                        <?php endif; ?>
                    </td>

                    <td class="text-center">
                        <h5 class="fw-bold text-success mb-0"><?= $row['intc'] ?></h5>
                        <?php if($role == 'Manager'): ?>
                            <div class="mt-1">
                                <a href="index.php?menu=statistik&action=minus&type=interception&id_p=<?= $row['id_pemain'] ?>" class="btn btn-sm btn-light py-0 px-2">-</a>
                                <a href="index.php?menu=statistik&action=plus&type=interception&id_p=<?= $row['id_pemain'] ?>" class="btn btn-sm btn-success py-0 px-2">+</a>
                            </div>
                        <?php endif; ?>
                    </td>

                    <td class="text-center">
                        <h5 class="fw-bold text-danger mb-0"><?= $row['sack'] ?></h5>
                        <?php if($role == 'Manager'): ?>
                            <div class="mt-1">
                                <a href="index.php?menu=statistik&action=minus&type=sack&id_p=<?= $row['id_pemain'] ?>" class="btn btn-sm btn-light py-0 px-2">-</a>
                                <a href="index.php?menu=statistik&action=plus&type=sack&id_p=<?= $row['id_pemain'] ?>" class="btn btn-sm btn-danger py-0 px-2">+</a>
                            </div>
                        <?php endif; ?>
                    </td>

                    <td class="text-center">
                        <h5 class="fw-bold text-secondary mb-0"><?= $row['tackle'] ?></h5>
                        <?php if($role == 'Manager'): ?>
                            <div class="mt-1">
                                <a href="index.php?menu=statistik&action=minus&type=tackle&id_p=<?= $row['id_pemain'] ?>" class="btn btn-sm btn-light py-0 px-2">-</a>
                                <a href="index.php?menu=statistik&action=plus&type=tackle&id_p=<?= $row['id_pemain'] ?>" class="btn btn-sm btn-secondary py-0 px-2">+</a>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php $rank++; endwhile; ?>
            </tbody>
        </table>
    </div>
</div>