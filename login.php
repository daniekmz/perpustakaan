<?php
session_start();
require_once 'config.php';

// Redirect jika sudah login
if (isset($_SESSION['user'])) {
    header("Location: admin/dashboard.php");
    exit();
} elseif (isset($_SESSION['anggota'])) {
    header("Location: member/pinjam.php");
    exit();
}

$error = '';
$username = '';

// Proses login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validasi input
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Username/Nomor Anggota dan Password harus diisi!";
    } else {
        // Coba login sebagai admin/staf
        $stmt = $conn->prepare("SELECT * FROM staf WHERE username = ? AND status = 'Aktif'");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Set login time
            $updateStmt = $conn->prepare("UPDATE staf SET terakhir_login = NOW() WHERE staf_id = ?");
            $updateStmt->execute([$user['staf_id']]);
            
            // Set session
            $_SESSION['user'] = $user;
            $_SESSION['user_type'] = 'staf';
            $_SESSION['login_time'] = time();
            
            // Redirect ke dashboard admin
            header("Location: admin/dashboard.php");
            exit();
        }
        
        // Coba login sebagai anggota
        $stmt = $conn->prepare("SELECT * FROM anggota WHERE nomor_anggota = ? AND status_keanggotaan = 'Aktif'");
        $stmt->execute([$username]);
        $anggota = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($anggota && password_verify($password, $anggota['password_hash'])) {
            // Set session
            $_SESSION['anggota'] = $anggota;
            $_SESSION['user_type'] = 'anggota';
            $_SESSION['login_time'] = time();
            
            // Redirect ke area anggota
            header("Location: member/pinjam.php");
            exit();
        }
        
        // Jika tidak ada yang cocok
        $error = "Username/Nomor Anggota atau Password salah!";
    }
}

// Load header
$page_title = "Login";
require_once 'header.php';
?>

<div class="row justify-content-center my-5">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0 text-center">Login Perpustakaan</h3>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <form method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username/Nomor Anggota</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword" title="Show/Hide Password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login
                        </button>
                    </div>
                </form>
                
                <div class="mt-4 text-center">
                    <p class="mb-0">Belum punya akun? <a href="register.php" class="fw-medium text-decoration-none">Daftar sekarang</a></p>
                </div>
            </div>
            <div class="card-footer bg-light text-center py-3">
                <small class="text-muted">Sistem Perpustakaan &copy; <?= date('Y') ?></small>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle visibility password
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
});
</script>

<?php require_once 'footer.php'; ?>