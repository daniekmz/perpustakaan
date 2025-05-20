<?php
// Initialize the session
session_start();
 
// Check if the user is already logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include database connection
require_once "connection.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Perpustakaan</title>
    <link rel="stylesheet" href="style.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Additional styles for dashboard */
        .dashboard-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .dashboard-card {
            background-color: white;
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
            padding: 20px;
            flex: 1;
            min-width: 300px;
        }
        
        .dashboard-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background-color: white;
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
            padding: 20px;
            flex: 1;
            min-width: 200px;
            text-align: center;
        }
        
        .stat-card i {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--dark-color);
        }
        
        .stat-card .stat-label {
            color: #777;
        }
        
        .tab-navigation {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        
        .tab-button {
            padding: 10px 20px;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            color: #777;
        }
        
        .tab-button.active {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .dashboard-table th,
        .dashboard-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        
        .dashboard-table th {
            background-color: #f5f5f5;
        }
        
        .dashboard-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .action-buttons a {
            margin-right: 10px;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <?php include "header.php"; ?>
    
    <div class="container">
        <h1>Dashboard</h1>
        <p>Selamat datang, <b><?php echo htmlspecialchars($_SESSION["name"]); ?></b>! (<?php echo htmlspecialchars($_SESSION["role"]); ?>)</p>
        
        <div class="dashboard-stats">
            <?php
            // Get total books
            $sql_books = "SELECT COUNT(*) as total FROM Buku";
            $result_books = $conn->query($sql_books);
            $total_books = $result_books->fetch_assoc()['total'];
            
            // Get total members
            $sql_members = "SELECT COUNT(*) as total FROM Anggota WHERE status_keanggotaan = 'Aktif'";
            $result_members = $conn->query($sql_members);
            $total_members = $result_members->fetch_assoc()['total'];
            
            // Get active loans
            $sql_loans = "SELECT COUNT(*) as total FROM Peminjaman WHERE status = 'Dipinjam'";
            $result_loans = $conn->query($sql_loans);
            $total_loans = $result_loans->fetch_assoc()['total'];
            
            // Get overdue loans
            $sql_overdue = "SELECT COUNT(*) as total FROM Peminjaman WHERE status = 'Terlambat'";
            $result_overdue = $conn->query($sql_overdue);
            $total_overdue = $result_overdue->fetch_assoc()['total'];
            ?>
            
            <div class="stat-card">
                <i class="fas fa-book"></i>
                <div class="stat-value"><?php echo $total_books; ?></div>
                <div class="stat-label">Total Buku</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-value"><?php echo $total_members; ?></div>
                <div class="stat-label">Anggota Aktif</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-hand-holding-usd"></i>
                <div class="stat-value"><?php echo $total_loans; ?></div>
                <div class="stat-label">Peminjaman Aktif</div>
            </div>
            
            <div class="stat-card">
                <i class="fas fa-exclamation-triangle"></i>
                <div class="stat-value"><?php echo $total_overdue; ?></div>
                <div class="stat-label">Keterlambatan</div>
            </div>
        </div>
        
        <div class="dashboard-container">
            <div class="dashboard-card">
                <div class="tab-navigation">
                    <button class="tab-button active" data-tab="recent-loans">Peminjaman Terbaru</button>
                    <button class="tab-button" data-tab="overdue-loans">Keterlambatan</button>
                </div>
                
                <div id="recent-loans" class="tab-content active">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Anggota</th>
                                <th>Buku</th>
                                <th>Tanggal Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT p.peminjaman_id, a.nama as anggota_nama, b.judul as buku_judul, 
                                    p.tanggal_pinjam, p.tanggal_jatuh_tempo, p.status
                                    FROM Peminjaman p
                                    JOIN Anggota a ON p.anggota_id = a.anggota_id
                                    JOIN Buku b ON p.buku_id = b.buku_id
                                    ORDER BY p.tanggal_pinjam DESC
                                    LIMIT 10";
                            $result = $conn->query($sql);
                            
                            if($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['anggota_nama']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['buku_judul']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tanggal_pinjam']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tanggal_jatuh_tempo']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>Tidak ada data peminjaman terbaru.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                
                <div id="overdue-loans" class="tab-content">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Anggota</th>
                                <th>Buku</th>
                                <th>Tanggal Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Keterlambatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT p.peminjaman_id, a.nama as anggota_nama, b.judul as buku_judul, 
                                    p.tanggal_pinjam, p.tanggal_jatuh_tempo, 
                                    DATEDIFF(CURDATE(), p.tanggal_jatuh_tempo) as days_overdue
                                    FROM Peminjaman p
                                    JOIN Anggota a ON p.anggota_id = a.anggota_id
                                    JOIN Buku b ON p.buku_id = b.buku_id
                                    WHERE p.status = 'Terlambat'
                                    ORDER BY days_overdue DESC
                                    LIMIT 10";
                            $result = $conn->query($sql);
                            
                            if($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['anggota_nama']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['buku_judul']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tanggal_pinjam']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['tanggal_jatuh_tempo']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['days_overdue']) . " hari</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5'>Tidak ada keterlambatan peminjaman.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="dashboard-card">
                <h3>Menu Cepat</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px;">
                    <a href="add_book.php" class="btn btn-block"><i class="fas fa-plus"></i> Tambah Buku</a>
                    <a href="add_member.php" class="btn btn-block"><i class="fas fa-user-plus"></i> Tambah Anggota</a>
                    <a href="new_loan.php" class="btn btn-block"><i class="fas fa-hand-holding-usd"></i> Peminjaman Baru</a>
                    <a href="returns.php" class="btn btn-block"><i class="fas fa-undo"></i> Proses Pengembalian</a>
                    <a href="catalog.php" class="btn btn-block"><i class="fas fa-search"></i> Katalog Buku</a>
                    <a href="reports.php" class="btn btn-block"><i class="fas fa-chart-bar"></i> Laporan</a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include "footer.php"; ?>
    
    <script>
        // Tab navigation
        document.querySelectorAll('.tab-button').forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all buttons and contents
                document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked button and corresponding content
                button.classList.add('active');
                document.getElementById(button.getAttribute('data-tab')).classList.add('active');
            });
        });
    </script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>
