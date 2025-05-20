-- Database: perpustakaan_db
-- Author: DeepSeek Chat
-- Date: 2023-11-15

-- Create database
CREATE DATABASE IF NOT EXISTS perpustakaan_db;
USE perpustakaan_db;

-- Table: Anggota (Members)
CREATE TABLE IF NOT EXISTS Anggota (
    anggota_id INT PRIMARY KEY AUTO_INCREMENT,
    nomor_anggota VARCHAR(20) UNIQUE,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT,
    telepon VARCHAR(15),
    email VARCHAR(100),
    tanggal_daftar DATE NOT NULL,
    status_keanggotaan ENUM('Aktif', 'Non-Aktif', 'Ditangguhkan') DEFAULT 'Aktif',
    password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: Penerbit (Publishers)
CREATE TABLE IF NOT EXISTS Penerbit (
    penerbit_id INT PRIMARY KEY AUTO_INCREMENT,
    nama_penerbit VARCHAR(100) NOT NULL,
    alamat TEXT,
    telepon VARCHAR(15),
    email VARCHAR(100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: Penulis (Authors)
CREATE TABLE IF NOT EXISTS Penulis (
    penulis_id INT PRIMARY KEY AUTO_INCREMENT,
    nama_penulis VARCHAR(100) NOT NULL,
    biografi TEXT,
    tanggal_lahir DATE,
    asal_negara VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: Kategori (Categories)
CREATE TABLE IF NOT EXISTS Kategori (
    kategori_id INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(50) NOT NULL,
    deskripsi TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: Buku (Books)
CREATE TABLE IF NOT EXISTS Buku (
    buku_id INT PRIMARY KEY AUTO_INCREMENT,
    isbn VARCHAR(20) UNIQUE,
    judul VARCHAR(255) NOT NULL,
    penerbit_id INT,
    tahun_terbit INT,
    jumlah_halaman INT,
    bahasa VARCHAR(50),
    deskripsi TEXT,
    stok INT DEFAULT 0,
    lokasi_rak VARCHAR(50),
    cover_url VARCHAR(255),
    FOREIGN KEY (penerbit_id) REFERENCES Penerbit(penerbit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: Buku_Penulis (Book Authors)
CREATE TABLE IF NOT EXISTS Buku_Penulis (
    buku_id INT,
    penulis_id INT,
    peran VARCHAR(50),
    PRIMARY KEY (buku_id, penulis_id),
    FOREIGN KEY (buku_id) REFERENCES Buku(buku_id),
    FOREIGN KEY (penulis_id) REFERENCES Penulis(penulis_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: Kategori_Buku (Book Categories)
CREATE TABLE IF NOT EXISTS Kategori_Buku (
    buku_id INT,
    kategori_id INT,
    PRIMARY KEY (buku_id, kategori_id),
    FOREIGN KEY (buku_id) REFERENCES Buku(buku_id),
    FOREIGN KEY (kategori_id) REFERENCES Kategori(kategori_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: Peminjaman (Loans)
CREATE TABLE IF NOT EXISTS Peminjaman (
    peminjaman_id INT PRIMARY KEY AUTO_INCREMENT,
    anggota_id INT NOT NULL,
    buku_id INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_jatuh_tempo DATE NOT NULL,
    status ENUM('Dipinjam', 'Dikembalikan', 'Terlambat', 'Hilang') DEFAULT 'Dipinjam',
    FOREIGN KEY (anggota_id) REFERENCES Anggota(anggota_id),
    FOREIGN KEY (buku_id) REFERENCES Buku(buku_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: Pengembalian (Returns)
CREATE TABLE IF NOT EXISTS Pengembalian (
    pengembalian_id INT PRIMARY KEY AUTO_INCREMENT,
    peminjaman_id INT NOT NULL,
    tanggal_kembali DATE NOT NULL,
    denda DECIMAL(10,2) DEFAULT 0,
    kondisi_buku ENUM('Baik', 'Rusak Ringan', 'Rusak Berat', 'Hilang'),
    catatan TEXT,
    FOREIGN KEY (peminjaman_id) REFERENCES Peminjaman(peminjaman_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: Staf (Staff)
CREATE TABLE IF NOT EXISTS Staf (
    staf_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Pustakawan', 'Staff') NOT NULL,
    terakhir_login DATETIME,
    status ENUM('Aktif', 'Non-Aktif') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample data

-- Penerbit
INSERT INTO Penerbit (nama_penerbit, alamat, telepon, email) VALUES
('Gramedia Pustaka Utama', 'Jl. Palmerah Barat 29-37, Jakarta', '021-53650110', 'info@gramedia.com'),
('Erlangga', 'Jl. H. Baping Raya No. 100, Jakarta', '021-8717006', 'customer@erlangga.co.id'),
('Mizan', 'Jl. Cinambo No. 135, Bandung', '022-7834310', 'pemasaran@mizan.com');

-- Penulis
INSERT INTO Penulis (nama_penulis, biografi, tanggal_lahir, asal_negara) VALUES
('Pramoedya Ananta Toer', 'Sastrawan terkenal Indonesia', '1925-02-06', 'Indonesia'),
('Andrea Hirata', 'Penulis novel Laskar Pelangi', '1967-10-24', 'Indonesia'),
('Tere Liye', 'Penulis novel bestseller', '1979-05-21', 'Indonesia');

-- Kategori
INSERT INTO Kategori (nama_kategori, deskripsi) VALUES
('Fiksi', 'Buku-buku fiksi termasuk novel, cerpen, dll'),
('Non-Fiksi', 'Buku-buku non-fiksi seperti biografi, ensiklopedia'),
('Sains', 'Buku-buku ilmu pengetahuan dan sains'),
('Sejarah', 'Buku-buku sejarah dan budaya');

-- Anggota
INSERT INTO Anggota (nomor_anggota, nama, alamat, telepon, email, tanggal_daftar, password_hash) VALUES
('LIB2023001', 'Andi Wijaya', 'Jl. Merdeka No. 10', '08123456789', 'andi@email.com', '2023-01-15', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('LIB2023002', 'Budi Santoso', 'Jl. Sudirman No. 25', '08234567890', 'budi@email.com', '2023-02-20', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('LIB2023003', 'Citra Dewi', 'Jl. Gatot Subroto No. 5', '08345678901', 'citra@email.com', '2023-03-10', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Buku
INSERT INTO Buku (isbn, judul, penerbit_id, tahun_terbit, jumlah_halaman, bahasa, stok, lokasi_rak) VALUES
('9789792234300', 'Bumi Manusia', 1, 2005, 535, 'Indonesia', 5, 'RAK-A1'),
('9789793062792', 'Laskar Pelangi', 2, 2005, 529, 'Indonesia', 3, 'RAK-A2'),
('9786024246945', 'Pulang', 3, 2015, 400, 'Indonesia', 7, 'RAK-B1'),
('9786020324817', 'Hujan', 1, 2016, 352, 'Indonesia', 4, 'RAK-B2');

-- Buku_Penulis
INSERT INTO Buku_Penulis (buku_id, penulis_id, peran) VALUES
(1, 1, 'Penulis Utama'),
(2, 2, 'Penulis Utama'),
(3, 3, 'Penulis Utama'),
(4, 3, 'Penulis Utama');

-- Kategori_Buku
INSERT INTO Kategori_Buku (buku_id, kategori_id) VALUES
(1, 1), (1, 4), -- Bumi Manusia: Fiksi, Sejarah
(2, 1),         -- Laskar Pelangi: Fiksi
(3, 1),         -- Pulang: Fiksi
(4, 1);         -- Hujan: Fiksi

-- Peminjaman
INSERT INTO Peminjaman (anggota_id, buku_id, tanggal_pinjam, tanggal_jatuh_tempo, status) VALUES
(1, 1, '2023-11-01', '2023-11-15', 'Dikembalikan'),
(2, 2, '2023-11-05', '2023-11-19', 'Dipinjam'),
(1, 3, '2023-11-10', '2023-11-24', 'Dipinjam');

-- Pengembalian
INSERT INTO Pengembalian (peminjaman_id, tanggal_kembali, denda, kondisi_buku) VALUES
(1, '2023-11-14', 0, 'Baik');

-- Staf
INSERT INTO Staf (username, nama_lengkap, email, password_hash, role) VALUES
('admin', 'Admin Perpustakaan', 'admin@perpustakaan.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin'),
('pustakawan1', 'Budi Pustakawan', 'pustakawan@perpustakaan.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Pustakawan');

-- Create indexes for better performance
CREATE INDEX idx_buku_judul ON Buku(judul);
CREATE INDEX idx_anggota_nomor ON Anggota(nomor_anggota);
CREATE INDEX idx_peminjaman_status ON Peminjaman(status);
CREATE INDEX idx_peminjaman_anggota ON Peminjaman(anggota_id);
CREATE INDEX idx_peminjaman_buku ON Peminjaman(buku_id);