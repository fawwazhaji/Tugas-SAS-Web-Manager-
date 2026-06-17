<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

// Kita coba tarik data pemain khusus untuk tim 'Yogyakarta Daggers' (id_tim = 1)
$id_tim = 1; 

$query = "SELECT * FROM pemain WHERE id_tim = '$id_tim'";
$result = mysqli_query($koneksi, $query);

$response = array();

if (mysqli_num_rows($result) > 0) {
    $response['sukses'] = true;
    $response['pesan'] = "Data pemain berhasil diambil";
    $response['data'] = array();

    while ($row = mysqli_fetch_assoc($result)) {
        array_push($response['data'], $row);
    }
} else {
    $response['sukses'] = false;
    $response['pesan'] = "Tidak ada data pemain";
    $response['data'] = null;
}

// Ubah data menjadi format JSON
echo json_encode($response);
?>