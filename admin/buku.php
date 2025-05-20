<?php
$page_title = "Manajemen Buku";
require_once 'header-admin.php';

// Tambah buku baru
if (isset($_POST['tambah_buku'])) {
    $isbn = $_POST['isbn'];
    $judul = $_POST['judul'];
    $penerbit_id = $_POST['penerbit_id'];
    $tahun_terbit = $_POST['tahun_terbit'];
    $stok = $_POST['stok'];
    
    $stmt = $conn->prepare("INSERT INTO buku (isbn, judul, penerbit_id, tahun_terbit, stok) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$isbn, $judul, $penerbit_id, $tahun_terbit, $stok]);
    
    $_SESSION['success'] = "Buku berhasil ditambahkan!";
    header("Location: buku.php");
    exit();
}

// Hapus buku
if (isset($_GET['hapus'])) {
    $buku_id = $_GET['hapus'];
    $conn->query("DELETE FROM buku WHERE buku_id = $buku_id");
    $_SESSION['success'] = "Buku berhasil dihapus!";
    header("Location: buku.php");
    exit();
}
?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Daftar Buku</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahBukuModal">
            <i class="bi bi-plus-circle"></i> Tambah Buku
        </button>
    </div>
    <div class="card-body">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-striped" id="tabelBuku">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ISBN</th>
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
                        SELECT b.buku_id, b.isbn, b.judul, p.nama_penerbit, b.tahun_terbit, b.stok 
                        FROM buku b
                        JOIN penerbit p ON b.penerbit_id = p.penerbit_id
                        ORDER BY b.buku_id DESC
                    ");
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <tr>
                        <td><?= $row['buku_id'] ?></td>
                        <td><?= $row['isbn'] ?></td>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= htmlspecialchars($row['nama_penerbit']) ?></td>
                        <td><?= $row['tahun_terbit'] ?></td>
                        <td><?= $row['stok'] ?></td>
                        <td>
                            <a href="edit-buku.php?id=<?= $row['buku_id'] ?>" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="?hapus=<?= $row['buku_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus buku ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Buku -->
<div class="modal fade" id="tambahBukuModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Buku Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ISBN</label>
                        <input type="text" class="form-control" name="isbn" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Judul Buku</label>
                        <input type="text" class="form-control" name="judul" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Penerbit</label>
                        <select class="form-select" name="penerbit_id" required>
                            <option value="">Pilih Penerbit</option>
                            <?php
                            $penerbit = $conn->query("SELECT * FROM penerbit");
                            while ($row = $penerbit->fetch(PDO::FETCH_ASSOC)):
                            ?>
                            <option value="<?= $row['penerbit_id'] ?>"><?= htmlspecialchars($row['nama_penerbit']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun Terbit</label>
                            <input type="number" class="form-control" name="tahun_terbit" min="1900" max="<?= date('Y') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stok</label>
                            <input type="number" class="form-control" name="stok" min="1" value="1" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-d