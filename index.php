<?php
// Start the session to maintain login status
session_start();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Sistem Perpustakaan</title>
    <link rel="stylesheet" href="style.css">
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Additional styles for the homepage */
        .hero {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('library-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-bottom: 50px;
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 30px;
        }
        
        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 50px;
        }
        
        .feature-box {
            flex-basis: 30%;
            background-color: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
            text-align: center;
        }
        
        .feature-box i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .feature-box h3 {
            margin-bottom: 15px;
            color: var(--dark-color);
        }
        
        .recent-books {
            background-color: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 50px;
        }
        
        .recent-books h2 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--dark-color);
        }
        
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 30px;
        }
        
        .book-card {
            border: 1px solid #eee;
            border-radius: var(--border-radius);
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .book-card:hover {
            transform: translateY(-5px);
        }
        
        .book-card img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            background-color: #f0f0f0;
        }
        
        .book-info {
            padding: 15px;
        }
        
        .book-info h3 {
            font-size: 1rem;
            margin-bottom: 5px;
        }
        
        .book-info p {
            color: #777;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .features {
                flex-direction: column;
            }
            
            .feature-box {
                flex-basis: 100%;
            }
            
            .book-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }
    </style>
</head>
<body>
    <?php include "header.php"; ?>
    
    <section class="hero">
        <div class="container">
            <h1>Selamat Datang di PerpusTaman</h1>
            <p>Akses ke ribuan koleksi buku dan sumber daya pengetahuan untuk mengembangkan wawasan dan keterampilan Anda.</p>
            <a href="catalog.php" class="btn">Jelajahi Katalog</a>
        </div>
    </section>
    
    <div class="container">
        <section class="features">
            <div class="feature-box">
                <i class="fas fa-book-reader"></i>
                <h3>Koleksi Terlengkap</h3>
                <p>Ribuan judul buku dari berbagai kategori dan genre untuk memenuhi kebutuhan pengetahuan Anda.</p>
            </div>
            <div class="feature-box">
                <i class="fas fa-laptop"></i>
                <h3>Akses Digital</h3>
                <p>Kemudahan mengakses katalog secara online dan kemudahan dalam peminjaman buku.</p>
            </div>
            <div class="feature-box">
                <i class="fas fa-users"></i>
                <h3>Komunitas Pembaca</h3>
                <p>Bergabunglah dengan komunitas pembaca dan diskusi literatur yang aktif dan inspiratif.</p>
            </div>
        </section>
        
        <section class="recent-books">
            <h2>Buku Terbaru</h2>
            
            <div class="book-grid">
                <?php
                // Include database connection
                require_once "connection.php";
                
                // Get recent books
                $sql = "SELECT b.buku_id, b.judul, p.nama_penulis, b.tahun_terbit, b.cover_url 
                        FROM Buku b 
                        JOIN Buku_Penulis bp ON b.buku_id = bp.buku_id 
                        JOIN Penulis p ON bp.penulis_id = p.penulis_id 
                        ORDER BY b.buku_id DESC LIMIT 6";
                
                if($result = $conn->query($sql)) {
                    if($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<div class="book-card">';
                            // Use a placeholder image if no cover URL is available
                            if(empty($row['cover_url'])) {
                                echo '<div class="book-cover"><i class="fas fa-book fa-5x" style="display: block; padding: 60px 0; text-align: center; background-color: #f0f0f0;"></i></div>';
                            } else {
                                echo '<img src="' . htmlspecialchars($row['cover_url']) . '" alt="' . htmlspecialchars($row['judul']) . '" class="book-cover">';
                            }
                            echo '<div class="book-info">';
                            echo '<h3>' . htmlspecialchars($row['judul']) . '</h3>';
                            echo '<p>Oleh: ' . htmlspecialchars($row['nama_penulis']) . '</p>';
                            echo '<p>Tahun: ' . htmlspecialchars($row['tahun_terbit']) . '</p>';
                            echo '<a href="book_detail.php?id=' . $row['buku_id'] . '" class="btn">Detil</a>';
                            echo '</div>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>Tidak ada buku yang tersedia saat ini.</p>';
                    }
                } else {
                    echo '<p>Terjadi kesalahan dalam mengambil data buku.</p>';
                }
                
                // Close connection
                $conn->close();
                ?>
            </div>
        </section>
    </div>
    
    <?php include "footer.php"; ?>
</body>
</html>
