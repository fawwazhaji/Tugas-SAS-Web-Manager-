<?php
require_once 'koneksi.php';
$id_tim = $_SESSION['id_tim'];
$id_user = isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 0;
$role_user = $_SESSION['role'];

// Pengaman ganda untuk ngambil nama dari Session
$nama_login = isset($_SESSION['nama']) ? $_SESSION['nama'] : (isset($_SESSION['nama_lengkap']) ? $_SESSION['nama_lengkap'] : 'Pemain');

// ==========================================
// 1. LOGIKA PEMAIN ABSEN MANDIRI
// ==========================================
if (isset($_POST['absen_mandiri'])) {
    $id_j = intval($_POST['id_jadwal']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status_absen']);
    
    // Cari id_pemain di tabel 'pemain'
    $q_pemain = mysqli_query($koneksi, "SELECT id_pemain FROM pemain WHERE nama_pemain = '$nama_login' AND id_tim = '$id_tim' LIMIT 1");
    
    if (mysqli_num_rows($q_pemain) > 0) {
        $d_pemain = mysqli_fetch_assoc($q_pemain);
        $id_p = $d_pemain['id_pemain'];
        
        // Cek apakah sudah pernah absen
        $cek_absen = mysqli_query($koneksi, "SELECT * FROM absensi WHERE id_jadwal = '$id_j' AND id_pemain = '$id_p'");
        
        if (mysqli_num_rows($cek_absen) > 0) {
            mysqli_query($koneksi, "UPDATE absensi SET status = '$status' WHERE id_jadwal = '$id_j' AND id_pemain = '$id_p'");
        } else {
            mysqli_query($koneksi, "INSERT INTO absensi (id_jadwal, id_pemain, status) VALUES ('$id_j', '$id_p', '$status')");
        }
        echo "<script>alert('Status kehadiran kamu berhasil diperbarui!'); window.location.href='index.php?menu=jadwal';</script>";
    } else {
        echo "<script>alert('Akun kamu tidak terdeteksi di tabel pemain tim ini! Pastikan nama akun sama dengan nama di roster.'); window.location.href='index.php?menu=jadwal';</script>";
    }
}

// Logika Tambah Jadwal
if (isset($_POST['simpan_jadwal'])) {
    $judul = mysqli_real_escape_string($koneksi, $_POST['judul']);
    $tgl = $_POST['tanggal'];
    $jam = $_POST['jam'];
    $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $ket = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    mysqli_query($koneksi, "INSERT INTO jadwal_latihan (id_tim, judul_latihan, tanggal, jam_mulai, lokasi, keterangan) 
                            VALUES ('$id_tim', '$judul', '$tgl', '$jam', '$lokasi', '$ket')");
    echo "<script>window.location.href='index.php?menu=jadwal';</script>";
}

// Logika Hapus Jadwal
if (isset($_GET['hapus_jadwal'])) {
    $id_h = intval($_GET['hapus_jadwal']);
    mysqli_query($koneksi, "DELETE FROM jadwal_latihan WHERE id_jadwal = '$id_h' AND id_tim = '$id_tim'");
    echo "<script>window.location.href='index.php?menu=jadwal';</script>";
}

$res_j = mysqli_query($koneksi, "SELECT * FROM jadwal_latihan WHERE id_tim = '$id_tim' ORDER BY tanggal ASC");
?>

<div class="row">
    <?php if ($role_user == 'Manager'): ?>
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm p-4 rounded-4">
            <h5 class="fw-bold mb-1">Buat Jadwal Baru</h5>
            <p class="text-muted small mb-4">Isi form di bawah untuk merencanakan latihan tim.</p>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark small">Agenda Latihan <span class="text-danger">*</span></label>
                    <input type="text" name="judul" class="form-control" placeholder="Contoh: Drill Passing & Taktik" required>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label fw-semibold text-dark small">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold text-dark small">Jam <span class="text-danger">*</span></label>
                        <input type="time" name="jam" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark small">Lokasi Lapangan</label>
                    <input type="text" name="lokasi" class="form-control" placeholder="Contoh: Lapangan A Senayan">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark small">Keterangan Tambahan</label>
                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Wajib bawa jersey gelap dan terang..."></textarea>
                </div>

                <button name="simpan_jadwal" class="btn btn-primary w-100 fw-bold py-2">
                    <i class="fas fa-paper-plane me-2"></i> Posting Jadwal
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="<?= $role_user == 'Manager' ? 'col-md-8' : 'col-12' ?>">
        <div class="card border-0 shadow-sm p-4 rounded-4">
            <h5 class="fw-bold mb-3">Daftar Jadwal Tim</h5>
            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu Pelaksanaan</th>
                            <th>Agenda & Keterangan</th>
                            <th>Lokasi</th>
                            <th class="text-center">Aksi / Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($j = mysqli_fetch_assoc($res_j)): 
                            $id_j_loop = $j['id_jadwal'];
                            
                            // Hitung jumlah yang hadir
                            $q_hitung = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM absensi WHERE id_jadwal = '$id_j_loop' AND status = 'Hadir'");
                            $d_hitung = mysqli_fetch_assoc($q_hitung);
                            $jumlah_hadir = $d_hitung['total'];

                            // Ambil status absen JOIN ke tabel 'pemain'
                            $status_saya = "";
                            $q_status_saya = mysqli_query($koneksi, "SELECT status FROM absensi a JOIN pemain p ON a.id_pemain = p.id_pemain WHERE a.id_jadwal = '$id_j_loop' AND p.nama_pemain = '$nama_login' LIMIT 1");
                            
                            if(mysqli_num_rows($q_status_saya) > 0) {
                                $d_status_saya = mysqli_fetch_assoc($q_status_saya);
                                $status_saya = $d_status_saya['status'];
                            }
                        ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">
                                    <i class="far fa-calendar-alt text-primary me-1"></i> 
                                    <?= date('d M Y', strtotime($j['tanggal'])) ?>
                                </div>
                                <small class="text-muted d-block">
                                    <i class="far fa-clock text-warning me-1"></i> 
                                    <?= date('H:i', strtotime($j['jam_mulai'])) ?> WIB
                                </small>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success mt-1">
                                    <i class="fas fa-check-circle me-1"></i> <?= $jumlah_hadir ?> Player Hadir
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($j['judul_latihan']) ?></div>
                                <?php if(!empty($j['keterangan'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($j['keterangan']) ?></small>
                                <?php else: ?>
                                    <small class="text-muted fst-italic">- Tidak ada catatan -</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <i class="fas fa-map-marker-alt text-danger me-1"></i> 
                                <?= htmlspecialchars($j['lokasi'] ?: 'Belum ditentukan') ?>
                            </td>
                            <td class="text-center">
                                <?php if ($role_user == 'Manager'): ?>
                                    <a href="index.php?menu=absensi&id_jadwal=<?= $j['id_jadwal'] ?>" 
                                       class="btn btn-sm btn-outline-success me-1" 
                                       title="Kelola Kehadiran">
                                        <i class="fas fa-users-cog"></i> Rekap Absen
                                    </a>
                                    
                                    <a href="index.php?menu=jadwal&hapus_jadwal=<?= $j['id_jadwal'] ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       title="Hapus Jadwal" 
                                       onclick="return confirm('Yakin ingin menghapus jadwal <?= $j['judul_latihan'] ?>?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <form method="POST" class="mb-2">
                                        <input type="hidden" name="id_jadwal" value="<?= $j['id_jadwal'] ?>">
                                        <input type="hidden" name="absen_mandiri" value="1">
                                        <select name="status_absen" class="form-select form-select-sm border-primary text-primary fw-bold" onchange="this.form.submit()">
                                            <option value="Alpa" <?= $status_saya == 'Alpa' || $status_saya == '' ? 'selected' : '' ?>>❌ Belum Absen</option>
                                            <option value="Hadir" <?= $status_saya == 'Hadir' ? 'selected' : '' ?>>✅ Hadir</option>
                                            <option value="Izin" <?= $status_saya == 'Izin' ? 'selected' : '' ?>>⚠️ Izin</option>
                                            <option value="Sakit" <?= $status_saya == 'Sakit' ? 'selected' : '' ?>>🏥 Sakit</option>
                                        </select>
                                    </form>
                                    <a href="index.php?menu=absensi&id_jadwal=<?= $j['id_jadwal'] ?>" class="btn btn-sm btn-info text-white fw-bold shadow-sm w-100">
                                        <i class="fas fa-list"></i> Lihat Full Hadir
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if(mysqli_num_rows($res_j) == 0): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Belum ada jadwal latihan yang dibuat.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>