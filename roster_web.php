<?php
require_once 'koneksi.php';
// MENGAMBIL ID TIM DARI SESSION LOGIN
$id_tim = $_SESSION['id_tim']; 

$query = "SELECT * FROM pemain WHERE id_tim = '$id_tim' ORDER BY nomor_punggung ASC";
$result = mysqli_query($koneksi, $query);
?>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center">No. Punggung</th>
                        <th>Nama Pemain</th>
                        <th class="text-center">Posisi</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if(mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td class='text-center fw-bold fs-5'>" . $row['nomor_punggung'] . "</td>";
                            echo "<td>" . $row['nama_pemain'] . "</td>";
                            echo "<td class='text-center'><span class='badge bg-light text-dark border'>" . $row['posisi'] . "</span></td>";
                            echo "<td class='text-center'>
                                    <div class='btn-group'>
                                        <a href='index.php?menu=edit_pemain&id=" . $row['id_pemain'] . "' class='btn btn-sm btn-info text-white'><i class='fas fa-edit'></i></a>
                                        <a href='hapus_pemain.php?id=" . $row['id_pemain'] . "' class='btn btn-sm btn-danger' onclick=\"return confirm('Hapus pemain?');\"><i class='fas fa-trash'></i></a>
                                    </div>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='text-center text-muted py-4'>Belum ada pemain di tim ini.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>