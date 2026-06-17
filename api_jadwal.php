<?php
header("Content-Type: application/json");
require_once 'koneksi.php';

$id_tim = isset($_POST['id_tim']) ? $_POST['id_tim'] : '';

if (empty($id_tim)) {
    echo json_encode(["status" => "error", "message" => "ID Tim tidak ditemukan"]);
    exit;
}

// Mengambil jadwal yang tanggalnya mulai hari ini dan ke depannya (diurutkan dari yang terdekat)
$query = mysqli_query($koneksi, "SELECT * FROM jadwal_tim WHERE id_tim = '$id_tim' ORDER BY tanggal ASC, waktu ASC");

$jadwal_data = [];
while ($row = mysqli_fetch_assoc($query)) {
    $jadwal_data[] = [
        "id_jadwal" => $row['id_jadwal'],
        "judul" => $row['judul'],
        "tanggal" => $row['tanggal'],
        "waktu" => $row['waktu'],
        "lokasi" => $row['lokasi'],
        "jenis_kegiatan" => $row['jenis_kegiatan'] // Contoh: 'Latihan Rutin', 'Scrimmage', 'Turnamen'
    ];
}

if (count($jadwal_data) > 0) {
    echo json_encode(["status" => "success", "data" => $jadwal_data]);
} else {
    echo json_encode(["status" => "empty", "message" => "Belum ada jadwal dalam waktu dekat"]);
}
?>