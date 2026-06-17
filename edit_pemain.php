<?php
require_once 'koneksi.php';

// Cek apakah ada ID yang dikirim melalui URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    // Ambil data pemain berdasarkan ID
    $res = mysqli_query($koneksi, "SELECT * FROM pemain WHERE id_pemain = '$id'");
    $data = mysqli_fetch_assoc($res);

    // Jika data tidak ditemukan, balikkan ke halaman roster
    if (!$data) {
        echo "<script>window.location.href='index.php?menu=roster';</script>";
        exit;
    }
}

// Logika saat tombol Update diklik
if (isset($_POST['update_pemain'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pemain']);
    $nomor = mysqli_real_escape_string($koneksi, $_POST['nomor_punggung']);
    $posisi = mysqli_real_escape_string($koneksi, $_POST['posisi']);

    $query = "UPDATE pemain SET nama_pemain='$nama', nomor_punggung='$nomor', posisi='$posisi' WHERE id_pemain='$id'";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data pemain berhasil diperbarui!'); window.location.href='index.php?menu=roster';</script>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . mysqli_error($koneksi) . "</div>";
    }
}
?>

<div class="card shadow-sm border-0">
    <div class="card-header bg-primary text-white py-3">
        <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit Informasi Pemain</h5>
    </div>
    <div class="card-body p-4">
        <form method="POST">
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label fw-bold">Nama Lengkap Pemain</label>
                    <input type="text" name="nama_pemain" class="form-control form-control-lg" value="<?= $data['nama_pemain'] ?>" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold">Nomor Punggung</label>
                    <input type="number" name="nomor_punggung" class="form-control form-control-lg" value="<?= $data['nomor_punggung'] ?>" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Posisi Lapangan (IFAF 5v5)</label>
                <select name="posisi" class="form-select form-control-lg" required>
                    <option value="QB" <?= $data['posisi'] == 'QB' ? 'selected' : '' ?>>Quarterback (QB)</option>
                    <option value="Center" <?= $data['posisi'] == 'Center' ? 'selected' : '' ?>>Center (C)</option>
                    <option value="WR" <?= $data['posisi'] == 'WR' ? 'selected' : '' ?>>Wide Receiver (WR)</option>
                    <option value="Blitzer" <?= $data['posisi'] == 'Blitzer' ? 'selected' : '' ?>>Blitzer (Rusher)</option>
                    <option value="DB" <?= $data['posisi'] == 'DB' ? 'selected' : '' ?>>Defensive Back (DB)</option>
                </select>
                <div class="form-text text-muted">Pilih posisi utama pemain dalam skema 5 lawan 5.</div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="update_pemain" class="btn btn-primary px-4 py-2 fw-bold">
                    <i class="fas fa-save me-2"></i>Simpan Perubahan
                </button>
                <a href="index.php?menu=roster" class="btn btn-light px-4 py-2 border">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>