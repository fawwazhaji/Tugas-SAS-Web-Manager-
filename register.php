<?php
require_once 'koneksi.php';

if (isset($_POST['register'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $kode_input = $_POST['kode_tim']; // Kode tim yang dimasukkan user
    $id_tim = 0; // Inisialisasi awal

    if ($role == 'Manager') {
        // Manager buat tim baru
        $nama_tim = mysqli_real_escape_string($koneksi, $_POST['nama_tim']);
        $kode_baru = strtoupper(substr($nama_tim, 0, 3)) . rand(100, 999);
        
        mysqli_query($koneksi, "INSERT INTO tim (nama_tim, kode_tim) VALUES ('$nama_tim', '$kode_baru')");
        $id_tim = mysqli_insert_id($koneksi);
        
        $query = "INSERT INTO users (nama_lengkap, email, password, role, id_tim) VALUES ('$nama', '$email', '$pass', 'Manager', '$id_tim')";
    } else {
        // Player cari ID tim berdasarkan kode_tim
        $cek_tim = mysqli_query($koneksi, "SELECT id_tim FROM tim WHERE kode_tim = '$kode_input'");
        if (mysqli_num_rows($cek_tim) > 0) {
            $data_tim = mysqli_fetch_assoc($cek_tim);
            $id_tim = $data_tim['id_tim'];
            $query = "INSERT INTO users (nama_lengkap, email, password, role, id_tim) VALUES ('$nama', '$email', '$pass', 'Player', '$id_tim')";
        } else {
            echo "<script>alert('Kode Tim Tidak Ditemukan! Pastikan kode dari Manager benar.'); window.history.back();</script>";
            exit;
        }
    }

    // Eksekusi Pembuatan Akun Login
    if (mysqli_query($koneksi, $query)) {
        
        // --- FITUR AUTO-ROSTER KHUSUS PLAYER ---
        if ($role == 'Player') {
            // Otomatis masukkan pemain baru ke Roster Tim dengan posisi default 'WR' agar tidak error ENUM
            mysqli_query($koneksi, "INSERT INTO pemain (id_tim, nama_pemain, nomor_punggung, posisi) 
                                    VALUES ('$id_tim', '$nama', 0, 'WR')");
        }
        // ---------------------------------------

        echo "<script>alert('Registrasi Berhasil! Data sudah masuk ke Roster. Silakan Login.'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Gagal registrasi. Email mungkin sudah dipakai.'); window.history.back();</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register - Flag Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow border-0 rounded-4 p-4">
                <h3 class="fw-bold text-center mb-4">Mulai Kelola Tim</h3>
                <form method="POST">
                    <div class="mb-3"><input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required></div>
                    <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                    <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold text-muted small">DAFTAR SEBAGAI:</label>
                        <select name="role" id="roleSelect" class="form-select" onchange="toggleForm()">
                            <option value="Manager">Manager (Bikin Tim Baru)</option>
                            <option value="Player">Player (Gabung Tim)</option>
                        </select>
                    </div>

                    <div id="formManager" class="mb-3"><input type="text" name="nama_tim" class="form-control" placeholder="Nama Tim (Contoh: Yogyakarta Daggers)"></div>
                    <div id="formPlayer" class="mb-3" style="display:none;"><input type="text" name="kode_tim" class="form-control" placeholder="Masukkan Kode Tim dari Manager"></div>

                    <button type="submit" name="register" class="btn btn-primary w-100 fw-bold">Daftar Sekarang</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
function toggleForm() {
    const role = document.getElementById('roleSelect').value;
    document.getElementById('formManager').style.display = role === 'Manager' ? 'block' : 'none';
    document.getElementById('formPlayer').style.display = role === 'Player' ? 'block' : 'none';
    
    // Matikan kewajiban isi form (required) yang sedang disembunyikan
    document.querySelector('input[name="nama_tim"]').required = (role === 'Manager');
    document.querySelector('input[name="kode_tim"]').required = (role === 'Player');
}
// Set awal saat halaman dimuat
toggleForm();
</script>
</body>
</html>