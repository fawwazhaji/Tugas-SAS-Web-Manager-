<?php
session_start();
require_once 'koneksi.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['id_user'] = $row['id_user'];
            $_SESSION['nama'] = $row['nama_lengkap'];
            $_SESSION['id_tim'] = $row['id_tim'];
            $_SESSION['role'] = $row['role']; // MENYIMPAN ROLE

            header("Location: index.php");
            exit;
        }
    }
    $error = true;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Flag Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark d-flex align-items-center" style="height: 100vh;">
<div class="container text-center">
    <div class="card p-4 mx-auto shadow border-0 rounded-4" style="max-width: 400px;">
        <h4 class="fw-bold mb-3">FLAG MANAGER</h4>
        <?php if(isset($error)) : ?><div class="alert alert-danger py-2">Email/Password Salah</div><?php endif; ?>
        <form method="POST">
            <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
            <input type="password" name="password" class="form-control mb-4" placeholder="Password" required>
            <button type="submit" name="login" class="btn btn-warning w-100 fw-bold shadow-sm">MASUK</button>
        </form>
        <p class="mt-3 small">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
    </div>
</div>
</body>
</html>