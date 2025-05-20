<?php
// Database connection
$server = "localhost";
$username = "root";
$password = "";
$database = "perpustakaan_db";

// Create connection
$conn = new mysqli($server, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
