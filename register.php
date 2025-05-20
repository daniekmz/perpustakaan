<?php
session_start();
require_once 'config.php';

// Jika sudah login, redirect
if (isset($_SESSION['user']) || isset($_SESSION['anggota'])) {
    header("Location: index.php");
    exit();
}

// Fungsi untuk generate nomor anggota
function generateMemberNumber($conn) {
    $year = date('Y');
    
    // Cari nomor anggota terakhir dengan prefix tahun ini
    $stmt = $conn->prepare("SELECT MAX(nomor_anggota) as last_number FROM anggota WHERE nomor_anggota LIKE :prefix");
    $prefix = "LIB" . $year;
    $stmt->bindParam(':prefix', $prefix . '%');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result['last_number']) {
        // Ekstrak nomor urut dan tambahkan 1
        $last_number = (int)substr($result['last_number'], -3);
        $new_number = $last_number + 1;
    } else {
        // Jika belum ada anggota dengan prefix tahun ini
        $new_number = 1;
    }
    
    // Format nomor urut dengan leading zeros
    $formatted_number = sprintf("%03d", $new_number);
    return $prefix . $formatted_number;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $nama = trim($_POST['nama']);
    $alamat = trim($_POST['alamat']);
    $telepon = trim($_POST['telepon']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validasi nama
    if (empty($nama)) {
        $errors[] = "Nama tidak boleh kosong";
    }
    
    // Validasi email
    if (empty($email)) {
        $errors[] = "Email tidak boleh kosong";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    } else {
        // Cek apakah email sudah terdaftar
        $stmt = $conn->prepare("SELECT COUNT(*) FROM anggota WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Email sudah terdaftar";
        }
    }
    
    // Validasi telepon
    if (empty($telepon)) {
        $errors[] = "Nomor telepon tidak boleh kosong";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $telepon)) {
        $errors[] = "Format nomor telepon tidak valid";
    }
    
    // Validasi alamat
    if (empty($alamat)) {
        $errors[] = "Alamat tidak boleh kosong";
    }
    
    // Validasi password
    if (empty($password)) {
        $errors[] = "Password tidak boleh kosong";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Password tidak cocok";
    }
    
    // Jika tidak ada error, proses pendaftaran
    if (empty($errors)) {
        try {
            $conn->beginTransaction();
            
            // Generate nomor anggota
            $nomor_anggota = generateMemberNumber($conn);
            
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Tanggal pendaftaran hari ini
            $tanggal_daftar = date('Y-m-d');
            
            // Insert data anggota baru
            $stmt = $conn->prepare("INSERT INTO anggota (nomor_anggota, nama, alamat, telepon, email, tanggal_daftar, password_hash) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nomor_anggota, $nama, $alamat, $telepon, $email, $tanggal_daftar, $password_hash]);
            
            $conn->commit();
            $success = true;
            $nomor_anggota_baru = $nomor_anggota;
            
        } catch (PDOException $e) {
            $conn->rollBack();
            $errors[] = "Terjadi kesalahan sistem: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pendaftaran Anggota - Sistem Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="card-title text-center mb-4">Pendaftaran Anggota Perpustakaan</h3>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <h4>Pendaftaran Berhasil!</h4>
                                <p>Selamat! Anda telah terdaftar sebagai anggota perpustakaan dengan nomor anggota: <strong><?= htmlspecialchars($nomor_anggota_baru) ?></strong></p>
                                <p>Silakan <a href="login.php" class="alert-link">login</a> menggunakan nomor anggota dan password yang telah Anda daftarkan.</p>
                            </div>
                        <?php else: ?>
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <h5>Terdapat kesalahan:</h5>
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?= $error ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap*</label>
                                    <input type="text" class="form-control" name="nama" value="<?= isset($nama) ? htmlspecialchars($nama) : '' ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Alamat*</label>
                                    <textarea class="form-control" name="alamat" rows="2" required><?= isset($alamat) ? htmlspecialchars($alamat) : '' ?></textarea>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nomor Telepon*</label>
                                        <input type="tel" class="form-control" name="telepon" value="<?= isset($telepon) ? htmlspecialchars($telepon) : '' ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email*</label>
                                        <input type="email" class="form-control" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Password*</label>
                                        <input type="password" class="form-control" name="password" required>
                                        <div class="form-text">Minimal 6 karakter</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Konfirmasi Password*</label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3 form-text">
                                    <small>* menandakan field wajib diisi</small>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Daftar</button>
                                    <a href="login.php" class="btn btn-outline-secondary">Sudah punya akun? Login</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
