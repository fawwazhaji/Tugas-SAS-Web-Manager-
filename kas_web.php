<?php
require_once 'koneksi.php';

// Pastikan hanya Manager yang bisa akses fungsi ini
if ($_SESSION['role'] !== 'Manager') {
    echo "<script>alert('Akses Ditolak! Anda bukan Manager.'); window.location.href='index.php';</script>";
    exit;
}

$id_tim = $_SESSION['id_tim'];

// --- LOGIKA TAMBAH TRANSAKSI ---
if (isset($_POST['simpan_kas'])) {
    $tipe = $_POST['tipe_transaksi'];
    $jumlah = preg_replace('/[^0-9]/', '', $_POST['jumlah']); // Bersihkan input dari titik/koma
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    mysqli_query($koneksi, "INSERT INTO keuangan (id_tim, tipe_transaksi, jumlah, keterangan) 
                            VALUES ('$id_tim', '$tipe', '$jumlah', '$keterangan')");
    echo "<script>window.location.href='index.php?menu=kas';</script>";
}

// --- LOGIKA HAPUS TRANSAKSI ---
if (isset($_GET['hapus_kas'])) {
    $id_h = $_GET['hapus_kas'];
    mysqli_query($koneksi, "DELETE FROM keuangan WHERE id_keuangan = '$id_h' AND id_tim = '$id_tim'");
    echo "<script>window.location.href='index.php?menu=kas';</script>";
}

// --- HITUNG STATISTIK KEUANGAN ---
$q_in = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(jumlah) as t FROM keuangan WHERE id_tim = '$id_tim' AND tipe_transaksi = 'Masuk'"));
$q_out = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(jumlah) as t FROM keuangan WHERE id_tim = '$id_tim' AND tipe_transaksi = 'Keluar'"));

$pemasukan = $q_in['t'] ?? 0;
$pengeluaran = $q_out['t'] ?? 0;
$saldo = $pemasukan - $pengeluaran;

// --- AMBIL RIWAYAT TRANSAKSI ---
$riwayat = mysqli_query($koneksi, "SELECT * FROM keuangan WHERE id_tim = '$id_tim' ORDER BY tanggal_transaksi DESC");
?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-white" style="background: linear-gradient(135deg, #198754, #146c43);">
            <h6 class="opacity-75 mb-1"><i class="fas fa-arrow-down me-2"></i>TOTAL PEMASUKAN</h6>
            <h3 class="fw-bold mb-0">Rp <?= number_format($pemasukan, 0, ',', '.') ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 text-white" style="background: linear-gradient(135deg, #dc3545, #b02a37);">
            <h6 class="opacity-75 mb-1"><i class="fas fa-arrow-up me-2"></i>TOTAL PENGELUARAN</h6>
            <h3 class="fw-bold mb-0">Rp <?= number_format($pengeluaran, 0, ',', '.') ?></h3>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-dark text-warning">
            <h6 class="opacity-75 mb-1"><i class="fas fa-wallet me-2"></i>SALDO AKHIR TIM</h6>
            <h3 class="fw-bold mb-0">Rp <?= number_format($saldo, 0, ',', '.') ?></h3>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold mb-3"><i class="fas fa-plus-circle text-primary me-2"></i>Catat Transaksi</h5>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Jenis Transaksi</label>
                    <select name="tipe_transaksi" class="form-select" required>
                        <option value="Masuk">Uang Masuk (Iuran, Sponsor)</option>
                        <option value="Keluar">Uang Keluar (Beli Alat, Sewa)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Jumlah (Rp)</label>
                    <input type="number" name="jumlah" class="form-control" placeholder="Contoh: 50000" required>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Contoh: Iuran bulan Mei" required></textarea>
                </div>
                <button type="submit" name="simpan_kas" class="btn btn-primary w-100 fw-bold rounded-pill">Simpan Data</button>
            </form>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
            <h5 class="fw-bold mb-3"><i class="fas fa-history text-secondary me-2"></i>Riwayat Kas Tim</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Tipe</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($riwayat) > 0): ?>
                            <?php while($r = mysqli_fetch_assoc($riwayat)): ?>
                            <tr>
                                <td>
                                    <span class="d-block fw-bold"><?= date('d M Y', strtotime($r['tanggal_transaksi'])) ?></span>
                                    <small class="text-muted"><?= date('H:i', strtotime($r['tanggal_transaksi'])) ?> WIB</small>
                                </td>
                                <td><?= $r['keterangan'] ?></td>
                                <td>
                                    <?php if($r['tipe_transaksi'] == 'Masuk'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="fas fa-plus me-1"></i>Masuk</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="fas fa-minus me-1"></i>Keluar</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold <?= $r['tipe_transaksi'] == 'Masuk' ? 'text-success' : 'text-danger' ?>">
                                    Rp <?= number_format($r['jumlah'], 0, ',', '.') ?>
                                </td>
                                <td class="text-center">
                                    <a href="index.php?menu=kas&hapus_kas=<?= $r['id_keuangan'] ?>" class="btn btn-sm btn-outline-danger rounded-circle" onclick="return confirm('Hapus transaksi ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat transaksi keuangan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>