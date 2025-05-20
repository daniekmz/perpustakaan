<?php
$page_title = "Dashboard Admin";
require_once 'header-admin.php';

// Hitung total buku
$total_buku = $conn->query("SELECT COUNT(*) FROM buku")->fetchColumn();

// Hitung total anggota
$total_anggota = $conn->query("SELECT COUNT(*) FROM anggota")->fetchColumn();

// Hitung buku dipinjam
$buku_dipinjam = $conn->query("SELECT COUNT(*) FROM peminjaman WHERE status = 'Dipinjam'")->fetchColumn();
?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-primary h-100">
            <div class="card-body">
                <h5 class="card-title">Total Buku</h5>
                <h2 class="card-text"><?= $total_buku ?></h2>
            </div>
            <div class="card-footer">
                <a href="buku.php" class="text-white">Lihat Detail <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-success h-100">
            <div class="card-body">
                <h5 class="card-title">Total Anggota</h5>
                <h2 class="card-text"><?= $total_anggota ?></h2>
            </div>
            <div class="card-footer">
                <a href="#" class="text-white">Lihat Detail <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-warning h-100">
            <div class="card-body">
                <h5 class="card-title">Buku Dipinjam</h5>
                <h2 class="card-text"><?= $buku_dipinjam ?></h2>
            </div>
            <div class="card-footer">
                <a href="#" class="text-white">Lihat Detail <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h5>Peminjaman Terakhir</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul Buku</th>
                        <th>Nama Anggota</th>
                        <th>Tanggal Pinjam</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->query("
                        SELECT p.peminjaman_id, b.judul, a.nama, p.tanggal_pinjam, p.status 
                        FROM peminjaman p
                        JOIN buku b ON p.buku_id = b.buku_id
                        JOIN anggota a ON p.anggota_id = a.anggota_id
                        ORDER BY p.tanggal_pinjam DESC LIMIT 5
                    ");
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <tr>
                        <td><?= $row['peminjaman_id'] ?></td>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= htmlspecialchars($row['nama']) ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                        <td>
                            <span class="badge bg-<?= 
                                $row['status'] == 'Dipinjam' ? 'warning' : 
                                ($row['status'] == 'Dikembalikan' ? 'success' : 'danger') 
                            ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'footer-admin.php'; ?>