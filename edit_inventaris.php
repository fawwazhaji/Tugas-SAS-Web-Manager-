<?php
require_once 'koneksi.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $res = mysqli_query($koneksi, "SELECT * FROM inventaris WHERE id_barang = '$id'");
    $data = mysqli_fetch_assoc($res);
}

if (isset($_POST['update_alat'])) {
    $nama = $_POST['nama_barang'];
    $jumlah = $_POST['jumlah'];
    $kondisi = $_POST['kondisi'];

    $query = "UPDATE inventaris SET nama_barang='$nama', jumlah='$jumlah', kondisi='$kondisi' WHERE id_barang='$id'";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data alat berhasil diupdate!'); window.location.href='index.php?menu=inventaris';</script>";
    }
}
?>

<div class="card shadow-sm">
    <div class="card-body">
        <h5>Edit Perlengkapan</h5>
        <form method="POST">
            <div class="mb-3">
                <label>Nama Barang</label>
                <input type="text" name="nama_barang" class="form-control" value="<?= $data['nama_barang'] ?>" required>
            </div>
            <div class="mb-3">
                <label>Jumlah</label>
                <input type="number" name="jumlah" class="form-control" value="<?= $data['jumlah'] ?>" required>
            </div>
            <div class="mb-3">
                <label>Kondisi</label>
                <select name="kondisi" class="form-select">
                    <option value="Bagus" <?= $data['kondisi'] == 'Bagus' ? 'selected' : '' ?>>Bagus</option>
                    <option value="Perlu Diganti" <?= $data['kondisi'] == 'Perlu Diganti' ? 'selected' : '' ?>>Perlu Diganti</option>
                    <option value="Hilang" <?= $data['kondisi'] == 'Hilang' ? 'selected' : '' ?>>Hilang</option>
                </select>
            </div>
            <button type="submit" name="update_alat" class="btn btn-primary">Simpan Perubahan</button>
            <a href="index.php?menu=inventaris" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>