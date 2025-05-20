<?php
// Start the session to maintain login status
session_start();

// If user is already logged in, redirect to dashboard
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.php");
    exit;
}

// Include database connection
require_once "connection.php";

// Define variables and initialize with empty values
$nomor_anggota = $nama = $alamat = $telepon = $email = $password = $confirm_password = "";
$nomor_anggota_err = $nama_err = $alamat_err = $telepon_err = $email_err = $password_err = $confirm_password_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Validate nomor anggota (will be auto-generated)
    $prefix = "LIB" . date("Y");
    
    // Get the latest member number
    $sql = "SELECT MAX(nomor_anggota) as max_id FROM Anggota WHERE nomor_anggota LIKE ?";
    if($stmt = $conn->prepare($sql)){
        $param_prefix = $prefix . "%";
        $stmt->bind_param("s", $param_prefix);
        
        if($stmt->execute()){
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $last_id = $row['max_id'];
            
            if($last_id){
                $num = intval(substr($last_id, -3));
                $num++;
            } else {
                $num = 1;
            }
            
            $nomor_anggota = $prefix . str_pad($num, 3, "0", STR_PAD_LEFT);
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }
        
        $stmt->close();
    }
    
    // Validate nama
    if(empty(trim($_POST["nama"]))){
        $nama_err = "Please enter your name.";
    } else{
        $nama = trim($_POST["nama"]);
    }
    
    // Validate alamat
    if(empty(trim($_POST["alamat"]))){
        $alamat_err = "Please enter your address.";
    } else{
        $alamat = trim($_POST["alamat"]);
    }
    
    // Validate telepon (optional)
    $telepon = trim($_POST["telepon"]);
    
    // Validate email
    if(empty(trim($_POST["email"]))){
        $email_err = "Please enter your email.";
    } else{
        // Check if email exists
        $sql = "SELECT anggota_id FROM Anggota WHERE email = ?";
        
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $param_email);
            $param_email = trim($_POST["email"]);
            
            if($stmt->execute()){
                $stmt->store_result();
                
                if($stmt->num_rows > 0){
                    $email_err = "This email is already taken.";
                } else{
                    $email = trim($_POST["email"]);
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            
            $stmt->close();
        }
    }
    
    // Validate password
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter a password.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Password must have at least 6 characters.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm password.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Check input errors before inserting in database
    if(empty($nama_err) && empty($alamat_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)){
        
        // Prepare an insert statement
        $sql = "INSERT INTO Anggota (nomor_anggota, nama, alamat, telepon, email, tanggal_daftar, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?)";
         
        if($stmt = $conn->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("sssssss", $param_nomor, $param_nama, $param_alamat, $param_telepon, $param_email, $param_tanggal, $param_password);
            
            // Set parameters
            $param_nomor = $nomor_anggota;
            $param_nama = $nama;
            $param_alamat = $alamat;
            $param_telepon = $telepon;
            $param_email = $email;
            $param_tanggal = date("Y-m-d");
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Redirect to login page
                echo "<script>alert('Pendaftaran berhasil! Nomor anggota Anda adalah: " . $nomor_anggota . ". Harap catat nomor ini untuk keperluan login.');</script>";
                echo "<script>window.location.href='login.php';</script>";
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
            
            // Close statement
            $stmt->close();
        }
    }
    
    // Close connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Anggota - Sistem Perpustakaan</title>
    <link rel="stylesheet" href="style.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include "header.php"; ?>
    
    <div class="container">
        <div class="form-container">
            <h2 class="form-title">Pendaftaran Anggota Perpustakaan</h2>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control <?php echo (!empty($nama_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $nama; ?>">
                    <span class="invalid-feedback"><?php echo $nama_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Alamat</label>
                    <textarea name="alamat" class="form-control <?php echo (!empty($alamat_err)) ? 'is-invalid' : ''; ?>" rows="3"><?php echo $alamat; ?></textarea>
                    <span class="invalid-feedback"><?php echo $alamat_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="telepon" class="form-control" value="<?php echo $telepon; ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>">
                    <span class="invalid-feedback"><?php echo $email_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                    <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-block">Daftar</button>
                </div>
                <p class="text-center">Sudah punya akun? <a href="login.php">Login disini</a>.</p>
            </form>
        </div>
    </div>
    
    <?php include "footer.php"; ?>
</body>
</html>
