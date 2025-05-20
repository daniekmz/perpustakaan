<?php
$page_title = "Beranda";
require_once 'config.php';
require_once 'header.php';

$stmt = $conn->query("SELECT buku.judul, penerbit.nama_penerbit 
                     FROM buku 
                     JOIN penerbit ON buku.penerbit_id = penerbit.penerbit_id 
                     LIMIT 10");
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="jumbotron bg-light p-5 rounded">
            <h1 class="display-4">Selamat Datang di Perpustakaan</h1>
            <p class="lead">Temukan berbagai koleksi buku dan bergabunglah menjadi anggota untuk menikmati fasilitas peminjaman buku.</p>
            <?php if (!isset($_SESSION['user']) && !isset($_SESSION['anggota'])): ?>
                <hr class="my-4">
                <p>Belum menjadi anggota? Daftar sekarang untuk menikmati fasilitas perpustakaan kami.</p>
                <a class="btn btn-primary btn-lg" href="register.php" role="button">Daftar Sekarang</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<h2 class="mb-4">Buku Terbaru</h2>
<div class="table-responsive">
    <table class="table table-striped">
        <thead class="table-dark">
            <tr>
                <th>Judul Buku</th>
                <th>Penerbit</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $book): ?>
            <tr>
                <td><?= htmlspecialchars($book['judul']) ?></td>
                <td><?= htmlspecialchars($book['nama_penerbit']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
require_once 'footer.php';
?>