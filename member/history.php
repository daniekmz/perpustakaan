<?php
$page_title = "Riwayat Peminjaman";
require_once 'header-member.php';

$anggota_id = $current_member['anggota_id'];
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Riwayat Peminjaman</h5>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Judul Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Tanggal Kembali</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->query("
                        SELECT b.judul, p.tanggal_pinjam, p.tanggal_jatuh_tempo, 
                               pg.tanggal_kembali, p.status
                        FROM peminjaman p
                        JOIN buku b ON p.buku_id = b.buku_id
                        LEFT JOIN pengembalian pg ON p.peminjaman_id = pg.peminjaman_id
                        WHERE p.anggota_id = $anggota_id
                        ORDER BY p.tanggal_pinjam DESC
                    ");
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                        $status_class = '';
                        if ($row['status'] == 'Dipinjam') {
                            $status_class = strtotime($row['tanggal_jatuh_tempo']) < time() ? 'bg-danger' : 'bg-warning';
                        } elseif ($row['status'] == 'Dikembalikan') {
                            $status_class = 'bg-success';
                        }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($row['tanggal_jatuh_tempo'])) ?></td>
                        <td><?= $row['tanggal_kembali'] ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-' ?></td>
                        <td>
                            <span class="badge <?= $status_class ?>">
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

<?php 
require_once 'footer-member.php';
?>