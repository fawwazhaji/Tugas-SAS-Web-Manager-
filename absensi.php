<?php
require_once 'koneksi.php';
$id_tim = $_SESSION['id_tim'];

// Tangkap ID jadwal dari URL
$id_jadwal = isset($_GET['id_jadwal']) ? intval($_GET['id_jadwal']) : 0;

// Ambil data jadwal
$query_jadwal = mysqli_query($koneksi, "SELECT * FROM jadwal_latihan WHERE id_jadwal = '$id_jadwal' AND id_tim = '$id_tim'");
$data_jadwal = mysqli_fetch_assoc($query_jadwal);

if (!$data_jadwal) {
    echo "<div class='alert alert-danger shadow-sm rounded-4'>Data jadwal tidak ditemukan.</div>";
    echo "<a href='index.php?menu=jadwal' class='btn btn-secondary'>Kembali</a>";
    exit;
}

// ==========================================
// 1. LOGIKA SIMPAN/UPDATE ABSENSI OLEH MANAGER
// ==========================================
if (isset($_POST['simpan_absensi'])) {
    if (isset($_POST['status'])) {
        $status_pemain = $_POST['status']; 

        // Hapus data absensi lama untuk jadwal ini biar ga duplikat
        mysqli_query($koneksi, "DELETE FROM absensi WHERE id_jadwal = '$id_jadwal'");

        // Loop dan simpan absensi baru ke database
        foreach ($status_pemain as $id_p => $status) {
            $id_p = intval($id_p);
            $status = mysqli_real_escape_string($koneksi, $status);
            
            mysqli_query($koneksi, "INSERT INTO absensi (id_jadwal, id_pemain, status) 
                                    VALUES ('$id_jadwal', '$id_p', '$status')");
        }
        echo "<script>
                alert('Data rekap absensi berhasil disimpan!');
                window.location.href='index.php?menu=absensi&id_jadwal=$id_jadwal';
              </script>";
    }
}

// ==========================================
// 2. AMBIL DATA PEMAIN & ABSENSI SAAT INI
// ==========================================
// REVISI FIX: Ganti 'roster' jadi 'pemain'
$query_pemain = mysqli_query($koneksi, "SELECT * FROM pemain WHERE id_tim = '$id_tim' ORDER BY nama_pemain ASC");

$query_absen_sekarang = mysqli_query($koneksi, "SELECT id_pemain, status FROM absensi WHERE id_jadwal = '$id_jadwal'");
$data_absen = [];

// Variabel untuk menghitung rekap otomatis
$total_hadir = 0; $total_izin = 0; $total_sakit = 0; $total_alpa = 0;

while ($row = mysqli_fetch_assoc($query_absen_sekarang)) {
    $data_absen[$row['id_pemain']] = $row['status']; 
    
    // Hitung rekap
    if($row['status'] == 'Hadir') $total_hadir++;
    if($row['status'] == 'Izin') $total_izin++;
    if($row['status'] == 'Sakit') $total_sakit++;
    if($row['status'] == 'Alpa') $total_alpa++;
}
?>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm p-4 rounded-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1">📋 Rekap Absensi: <?= htmlspecialchars($data_jadwal['judul_latihan']) ?></h4>
                    <p class="text-muted mb-0">
                        <i class="far fa-calendar-alt me-1"></i> <?= date('d M Y', strtotime($data_jadwal['tanggal'])) ?> | 
                        <i class="far fa-clock me-1"></i> <?= date('H:i', strtotime($data_jadwal['jam_mulai'])) ?> WIB |
                        <i class="fas fa-map-marker-alt me-1 ms-2"></i> <?= htmlspecialchars($data_jadwal['lokasi'] ?: '-') ?>
                    </p>
                </div>
                <a href="index.php?menu=jadwal" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <div class="row mb-4 text-center">
                <div class="col-3">
                    <div class="p-3 bg-success bg-opacity-10 rounded-3 border border-success">
                        <h3 class="fw-bold text-success mb-0"><?= $total_hadir ?></h3>
                        <small class="text-success fw-semibold">HADIR</small>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-3 bg-warning bg-opacity-10 rounded-3 border border-warning">
                        <h3 class="fw-bold text-warning mb-0"><?= $total_izin ?></h3>
                        <small class="text-warning fw-semibold">IZIN</small>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-3 bg-info bg-opacity-10 rounded-3 border border-info">
                        <h3 class="fw-bold text-info mb-0"><?= $total_sakit ?></h3>
                        <small class="text-info fw-semibold">SAKIT</small>
                    </div>
                </div>
                <div class="col-3">
                    <div class="p-3 bg-danger bg-opacity-10 rounded-3 border border-danger">
                        <h3 class="fw-bold text-danger mb-0"><?= $total_alpa ?></h3>
                        <small class="text-danger fw-semibold">ALPA / BELUM</small>
                    </div>
                </div>
            </div>
            
            <form method="POST">
                <div class="table-responsive">
                    <table class="table align-middle table-hover border">
                        <thead class="table-light">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th width="35%">Nama Pemain</th>
                                <th width="60%" class="text-center">Status Kehadiran (Manager Override)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            while ($pemain = mysqli_fetch_assoc($query_pemain)): 
                                $id_p = $pemain['id_pemain'];
                                $status_saat_ini = isset($data_absen[$id_p]) ? $data_absen[$id_p] : 'Alpa'; 
                            ?>
                            <tr>
                                <td class="text-center text-muted"><?= $no++ ?></td>
                                <td class="fw-bold"><?= htmlspecialchars($pemain['nama_pemain']) ?></td>
                                <td class="text-center">
                                    <div class="btn-group w-100 shadow-sm" role="group">
                                        <input type="radio" class="btn-check" name="status[<?= $id_p ?>]" id="hadir_<?= $id_p ?>" value="Hadir" <?= ($status_saat_ini == 'Hadir') ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-success" for="hadir_<?= $id_p ?>">Hadir</label>

                                        <input type="radio" class="btn-check" name="status[<?= $id_p ?>]" id="izin_<?= $id_p ?>" value="Izin" <?= ($status_saat_ini == 'Izin') ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-warning" for="izin_<?= $id_p ?>">Izin</label>

                                        <input type="radio" class="btn-check" name="status[<?= $id_p ?>]" id="sakit_<?= $id_p ?>" value="Sakit" <?= ($status_saat_ini == 'Sakit') ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-info" for="sakit_<?= $id_p ?>">Sakit</label>

                                        <input type="radio" class="btn-check" name="status[<?= $id_p ?>]" id="alpa_<?= $id_p ?>" value="Alpa" <?= ($status_saat_ini == 'Alpa') ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-danger" for="alpa_<?= $id_p ?>">Alpa</label>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>

                            <?php if(mysqli_num_rows($query_pemain) == 0): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">
                                    <i class="fas fa-users-slash fs-4 d-block mb-2"></i>
                                    Belum ada data pemain di tabel tim. Silakan tambah pemain terlebih dahulu.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if(mysqli_num_rows($query_pemain) > 0): ?>
                <div class="d-flex justify-content-end mt-3">
                    <button type="submit" name="simpan_absensi" class="btn btn-primary btn-lg px-5 fw-bold rounded-pill shadow-sm">
                        <i class="fas fa-save me-2"></i> Simpan Rekap Absensi
                    </button>
                </div>
                <?php endif; ?>
            </form>
            
        </div>
    </div>
</div>