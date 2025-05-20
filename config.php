<?php
$host = 'localhost';
$dbname = 'perpustakaan_db';
$username = 'root';
$password = ''; // Default XAMPP password kosong

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}
?>