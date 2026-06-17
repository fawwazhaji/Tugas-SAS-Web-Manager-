<?php
require_once 'koneksi.php';
$id_tim = $_SESSION['id_tim']; 

// ==========================================
// 1. LOGIKA UNTUK MENGHAPUS BARANG
// ==========================================
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']); // intval untuk keamanan tambahan
    
    // Query hapus, pastikan id_tim dicocokkan agar tidak bisa menghapus barang tim lain
    mysqli_query($koneksi, "DELETE FROM inventaris WHERE id_barang = '$id_hapus' AND id_tim = '$id_tim'");
    
    echo "<script>
            alert('Barang berhasil dihapus!');
            window.location.href='index.php?menu=inventaris';
          </script>";
}

// ==========================================
// 2. LOGIKA UNTUK MENYIMPAN BARANG
// ==========================================
if (isset($_POST['simpan_alat'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $jml = $_POST['jumlah'];
    $kon = $_POST['kondisi'];
    
    mysqli_query($koneksi, "INSERT INTO inventaris (id_tim, nama_barang, jumlah, kondisi) VALUES ('$id_tim', '$nama', '$jml', '$kon')");
    echo "<script>window.location.href='index.php?menu=inventaris';</script>";
}

// Menampilkan data inventaris
$result_alat = mysqli_query($koneksi, "SELECT * FROM inventaris WHERE id_tim = '$id_tim'");
?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm p-3">
            <h5 class="fw-bold">Tambah Alat</h5>
            <form method="POST">
                <input type="text" name="nama_barang" class="form-control mb-2" placeholder="Nama Barang" required>
                <input type="number" name="jumlah" class="form-control mb-2" placeholder="Jumlah" required>
                <select name="kondisi" class="form-select mb-3">
                    <option value="Bagus">Bagus</option>
                    <option value="Perlu Diganti">Rusak</option>
                </select>
                <button name="simpan_alat" class="btn btn-warning w-100 fw-bold">Tambah</button>
            </form>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="table-responsive bg-white p-3 rounded shadow-sm">
            <table class="table">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>Jumlah</th>
                        <th>Kondisi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result_alat)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                            <td><?= $row['jumlah'] ?></td>
                            <td><?= $row['kondisi'] ?></td>
                            <td>
                                <a href="index.php?menu=edit_inventaris&id=<?= $row['id_barang'] ?>" class="text-info me-2" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <a href="index.php?menu=inventaris&hapus=<?= $row['id_barang'] ?>" class="text-danger" title="Hapus" onclick="return confirm('Yakin ingin menghapus barang <?= $row['nama_barang'] ?> ini?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>