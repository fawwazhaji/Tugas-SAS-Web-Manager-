<?php
require_once 'koneksi.php';
$id_tim = $_SESSION['id_tim']; 

if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pemain']);
    $nomor = $_POST['nomor_punggung'];
    $posisi = $_POST['posisi'];

    $query = "INSERT INTO pemain (id_tim, nama_pemain, nomor_punggung, posisi) VALUES ('$id_tim', '$nama', '$nomor', '$posisi')";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Pemain berhasil ditambah!'); window.location.href='index.php?menu=roster';</script>";
    }
}
?>
<div class="card shadow-sm border-0 p-4">
    <h5 class="fw-bold mb-3">Tambah Pemain Baru</h5>
    <form method="POST">
        <div class="mb-3"><label class="form-label">Nama Pemain</label><input type="text" name="nama_pemain" class="form-control" required></div>
        <div class="mb-3"><label class="form-label">Nomor Punggung</label><input type="number" name="nomor_punggung" class="form-control" required></div>
        <div class="mb-3">
            <label class="form-label">Posisi</label>
            <select name="posisi" class="form-select" required>
                <option value="QB">QB</option><option value="Center">Center</option><option value="WR">WR</option><option value="Blitzer">Blitzer</option><option value="DB">DB</option>
            </select>
        </div>
        <button type="submit" name="simpan" class="btn btn-primary w-100">Simpan Pemain</button>
    </form>
</div>