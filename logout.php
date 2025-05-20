<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Logout process
if(isset($_GET["logout"])) {
    // Unset all of the session variables
    $_SESSION = array();
    
    // Destroy the session.
    session_destroy();
    
    // Redirect to login page
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Sistem Perpustakaan</title>
    <link rel="stylesheet" href="style.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include "header.php"; ?>
    
    <div class="container">
        <div class="form-container">
            <h2 class="form-title">Logout</h2>
            <p>Apakah Anda yakin ingin keluar dari sistem?</p>
            <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                <a href="<?php echo $_SESSION['user_type'] === 'staff' ? 'dashboard.php' : 'member_dashboard.php'; ?>" class="btn">Batal</a>
                <a href="logout.php?logout=true" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
    
    <?php include "footer.php"; ?>
</body>
</html>
