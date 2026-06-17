<?php
require_once 'koneksi.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $res = mysqli_query($koneksi, "SELECT * FROM playbook WHERE id_play = '$id'");
    $data = mysqli_fetch_assoc($res);
}

if (isset($_POST['update_play'])) {
    $nama = $_POST['nama_strategi'];
    $kat = $_POST['kategori'];
    $desc = $_POST['deskripsi'];
    $gambar_lama = $data['url_gambar_skema'];

    if ($_FILES['gambar_skema']['name'] != "") {
        $nama_file = $_FILES['gambar_skema']['name'];
        move_uploaded_file($_FILES['gambar_skema']['tmp_name'], 'uploads/' . $nama_file);
        $gambar_final = $nama_file;
    } else {
        $gambar_final = $gambar_lama;
    }

    $query = "UPDATE playbook SET nama_strategi='$nama', kategori='$kat', deskripsi='$desc', url_gambar_skema='$gambar_final' WHERE id_play='$id'";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Playbook diupdate!'); window.location.href='index.php?menu=playbook';</script>";
    }
}
?>

<div class="card shadow-sm">
    <div class="card-body">
        <h5>Edit Strategi Playbook</h5>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label>Nama Strategi</label>
                <input type="text" name="nama_strategi" class="form-control" value="<?= $data['nama_strategi'] ?>" required>
            </div>
            <div class="mb-3">
                <label>Kategori</label>
                <select name="kategori" class="form-select">
                    <option value="Offense" <?= $data['kategori'] == 'Offense' ? 'selected' : '' ?>>Offense</option>
                    <option value="Defense" <?= $data['kategori'] == 'Defense' ? 'selected' : '' ?>>Defense</option>
                </select>
            </div>
            <div class="mb-3">
                <label>Deskripsi</label>
                <textarea name="deskripsi" class="form-control" rows="3"><?= $data['deskripsi'] ?></textarea>
            </div>
            <div class="mb-3">
                <label>Ganti Gambar (Kosongkan jika tidak ingin ganti)</label>
                <input type="file" name="gambar_skema" class="form-control">
                <p class="small text-muted mt-1">File saat ini: <?= $data['url_gambar_skema'] ?></p>
            </div>
            <button type="submit" name="update_play" class="btn btn-success">Update Strategi</button>
            <a href="index.php?menu=playbook" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>