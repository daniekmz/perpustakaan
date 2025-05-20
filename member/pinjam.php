<?php
$page_title = "Pinjam Buku";
require_once 'header-member.php';

// Proses peminjaman
if (isset($_POST['pinjam'])) {
    $buku_id = $_POST['buku_id'];
    $anggota_id = $current_member['anggota_id'];
    $tanggal_pinjam = date('Y-m-d');
    $tanggal_jatuh_tempo = date('Y-m-d', strtotime('+14 days'));
    
    // Cek stok buku
    $stok = $conn->query("SELECT stok FROM buku WHERE buku_id = $buku_id")->fetchColumn();
    
    if ($stok > 0) {
        // Kurangi stok
        $conn->query("UPDATE buku SET stok = stok - 1 WHERE buku_id = $buku_id");
        
        // Tambah peminjaman
        $stmt = $conn->prepare("INSERT INTO peminjaman (anggota_id, buku_id, tanggal_pinjam, tanggal_jatuh_tempo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$anggota_id, $buku_id, $tanggal_pinjam, $tanggal_jatuh_tempo]);
        
        $_SESSION['success'] = "Buku berhasil dipinjam!";
        header("Location: history.php");
        exit();
    } else {
        $error = "Stok buku habis!";
    }
}
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Daftar Buku Tersedia</h5>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Judul</th>
                        <th>Penerbit</th>
                        <th>Tahun</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $conn->query("
                        SELECT b.buku_id, b.judul, p.nama_penerbit, b.tahun_terbit, b.stok 
                        FROM buku b
                        JOIN penerbit p ON b.penerbit_id = p.penerbit_id
                        WHERE b.stok > 0
                        ORDER BY b.judul
                    ");
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= htmlspecialchars($row['nama_penerbit']) ?></td>
                        <td><?= $row['tahun_terbit'] ?></td>
                        <td><?= $row['stok'] ?></td>
                        <td>
                            <form method="POST" onsubmit="return confirm('Pinjam buku ini?')">
                                <input type="hidden" name="buku_id" value="<?= $row['buku_id'] ?>">
                                <button type="submit" name="pinjam" class="btn btn-sm btn-primary">
                                    <i class="bi bi-bookmark-plus"></i> Pinjam
                                </button>
                            </form>
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