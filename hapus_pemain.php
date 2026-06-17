<?php
require_once 'koneksi.php';

// Mengecek apakah ada ID yang dikirim lewat URL
if (isset($_GET['id'])) {
    $id_pemain = $_GET['id'];

    // Query untuk menghapus data berdasarkan ID
    $query = "DELETE FROM pemain WHERE id_pemain = '$id_pemain'";

    if (mysqli_query($koneksi, $query)) {
        // Jika berhasil, arahkan kembali ke halaman roster
        echo "<script>
                alert('Pemain berhasil dihapus dari roster!');
                window.location.href = 'index.php?menu=roster';
              </script>";
    } else {
        echo "Gagal menghapus data: " . mysqli_error($koneksi);
    }
} else {
    // Jika tidak ada ID, kembalikan ke dashboard
    header("Location: index.php");
}
?>