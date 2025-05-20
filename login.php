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
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username or member number.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($username_err) && empty($password_err)){
        // Check if the user is a staff member
        $sql = "SELECT staf_id, username, password_hash, role, nama_lengkap FROM Staf WHERE username = ? AND status = 'Aktif'";
        
        if($stmt = $conn->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bind_param("s", $param_username);
            
            // Set parameters
            $param_username = $username;
            
            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Store result
                $stmt->store_result();
                
                // Check if username exists, if yes then verify password
                if($stmt->num_rows == 1){                    
                    // Bind result variables
                    $stmt->bind_result($id, $username, $hashed_password, $role, $nama);
                    if($stmt->fetch()){
                        // Verify password
                        if(password_verify($password, $hashed_password)){
                            // Password is correct, start a new session
                            session_start();
                            
                            // Store data in session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $role;
                            $_SESSION["name"] = $nama;
                            $_SESSION["user_type"] = "staff";
                            
                            // Update last login time
                            $update_sql = "UPDATE Staf SET terakhir_login = NOW() WHERE staf_id = ?";
                            if($update_stmt = $conn->prepare($update_sql)){
                                $update_stmt->bind_param("i", $id);
                                $update_stmt->execute();
                                $update_stmt->close();
                            }
                            
                            // Redirect user to dashboard page
                            header("location: dashboard.php");
                        } else{
                            // Password is not valid
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    // Check if the user is a library member
                    $sql_member = "SELECT anggota_id, nomor_anggota, password_hash, nama FROM Anggota WHERE (nomor_anggota = ? OR email = ?) AND status_keanggotaan = 'Aktif'";
                    
                    if($stmt_member = $conn->prepare($sql_member)){
                        // Bind variables to the prepared statement as parameters
                        $stmt_member->bind_param("ss", $param_username, $param_username);
                        
                        // Attempt to execute the prepared statement
                        if($stmt_member->execute()){
                            // Store result
                            $stmt_member->store_result();
                            
                            // Check if username exists, if yes then verify password
                            if($stmt_member->num_rows == 1){                    
                                // Bind result variables
                                $stmt_member->bind_result($id, $nomor_anggota, $hashed_password, $nama);
                                if($stmt_member->fetch()){
                                    // Verify password
                                    if(password_verify($password, $hashed_password)){
                                        // Password is correct, start a new session
                                        session_start();
                                        
                                        // Store data in session variables
                                        $_SESSION["loggedin"] = true;
                                        $_SESSION["id"] = $id;
                                        $_SESSION["member_id"] = $nomor_anggota;
                                        $_SESSION["name"] = $nama;
                                        $_SESSION["user_type"] = "member";
                                        
                                        // Redirect user to member dashboard page
                                        header("location: member_dashboard.php");
                                    } else{
                                        // Password is not valid
                                        $login_err = "Invalid username or password.";
                                    }
                                }
                            } else{
                                // Username doesn't exist
                                $login_err = "Invalid username or password.";
                            }
                        } else{
                            echo "Oops! Something went wrong. Please try again later.";
                        }
                        
                        // Close statement
                        $stmt_member->close();
                    }
                }
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
    <title>Login - Sistem Perpustakaan</title>
    <link rel="stylesheet" href="style.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include "header.php"; ?>
    
    <div class="container">
        <div class="form-container">
            <h2 class="form-title">Masuk ke Sistem Perpustakaan</h2>
            
            <?php 
            if(!empty($login_err)){
                echo '<div class="alert alert-danger">' . $login_err . '</div>';
            }        
            ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Username / Nomor Anggota / Email</label>
                    <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                    <span class="invalid-feedback"><?php echo $username_err; ?></span>
                </div>    
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                    <span class="invalid-feedback"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-block">Login</button>
                </div>
                <p class="text-center">Belum punya akun? <a href="register.php">Daftar sekarang</a>.</p>
            </form>
        </div>
    </div>
    
    <?php include "footer.php"; ?>
</body>
</html>
