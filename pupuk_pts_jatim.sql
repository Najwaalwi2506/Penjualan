-- ============================================================
--  SISTEM INFORMASI PENJUAL PUPUK SESUAI SOP BSM
--  DAN BAHAN BAKU PUPUK ANGGOTA PTS JATIM
--  Database: pupuk_pts_jatim
--  Engine: MySQL (XAMPP)
-- ============================================================

CREATE DATABASE IF NOT EXISTS pupuk_pts_jatim
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE pupuk_pts_jatim;

-- ============================================================
-- 1. TABEL USERS
--    Menyimpan semua akun: admin, penjual, pembeli
-- ============================================================
CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama          VARCHAR(150)  NOT NULL,
  angkatan      VARCHAR(30)   NOT NULL COMMENT 'Contoh: 2020, 2021, Alumni',
  jenis_keanggotaan ENUM('penjual','bukan_penjual') NOT NULL,
  alamat        TEXT          NOT NULL,
  no_telp       VARCHAR(30)   NULL COMMENT 'Nomor telepon pengguna',
  email         VARCHAR(150)  NOT NULL UNIQUE,
  password      VARCHAR(255)  NOT NULL COMMENT 'Bcrypt hash',
  role          ENUM('admin','penjual','pembeli') NOT NULL DEFAULT 'pembeli',
  foto_profil   VARCHAR(255)  NULL,
  is_active     TINYINT(1)    NOT NULL DEFAULT 1,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                              ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2. TABEL TOKO
--    Setiap penjual punya 1 toko (mirip toko di Shopee)
-- ============================================================
CREATE TABLE toko (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id       INT UNSIGNED  NOT NULL UNIQUE,
  nama_toko     VARCHAR(150)  NOT NULL,
  deskripsi     TEXT          NULL,
  foto_toko     VARCHAR(255)  NULL,
  bank_nama      VARCHAR(100)  NULL,
  no_rekening    VARCHAR(50)   NULL,
  nama_rekening  VARCHAR(150)  NULL,
  rating        DECIMAL(3,2)  NOT NULL DEFAULT 0.00,
  total_terjual INT UNSIGNED  NOT NULL DEFAULT 0,
  is_active     TINYINT(1)    NOT NULL DEFAULT 1,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
                              ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_toko_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 3. TABEL KATEGORI PRODUK
--    Pupuk & Bahan Baku dipisah kategori
-- ============================================================
CREATE TABLE kategori_produk (
  id    TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama  VARCHAR(50) NOT NULL COMMENT 'Pupuk / Bahan Baku'
) ENGINE=InnoDB;

INSERT INTO kategori_produk (nama) VALUES
  ('Pupuk'),
  ('Bahan Baku');

-- ============================================================
-- 4. TABEL JENIS PRODUK (Master Data)
--    Berisi semua jenis pupuk & bahan baku sesuai form SOP
-- ============================================================
CREATE TABLE jenis_produk (
  id           SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kategori_id  TINYINT UNSIGNED  NOT NULL,
  nama_jenis   VARCHAR(100)      NOT NULL,
  satuan       ENUM('liter','kg') NOT NULL,
  CONSTRAINT fk_jenis_kategori FOREIGN KEY (kategori_id)
    REFERENCES kategori_produk(id)
) ENGINE=InnoDB;

-- Insert master jenis PUPUK
INSERT INTO jenis_produk (kategori_id, nama_jenis, satuan) VALUES
  (1, 'Pembenah Tanah', 'liter'),
  (1, 'Urea Padat',     'kg'),
  (1, 'Urea Cair',      'liter'),
  (1, 'KCL',            'liter'),
  (1, 'ZPT Buah',       'liter'),
  (1, 'ZPT Daun',       'liter'),
  (1, 'Asam Amino',     'liter'),
  (1, 'Herbisida',      'liter'),
  (1, 'Insek',          'liter'),
  (1, 'Fungi',          'liter'),
  (1, 'Horti Padat',    'kg'),
  (1, 'Horti Cair',     'liter'),
  (1, 'Pengusir Tikus', 'liter'),
  (1, 'Asap Cair',      'liter');

-- Insert master jenis BAHAN BAKU
INSERT INTO jenis_produk (kategori_id, nama_jenis, satuan) VALUES
  (2, 'Kohe Sapi',           'kg'),
  (2, 'Kohe Kambing',        'kg'),
  (2, 'Kohe Ayam Pedaging',  'kg'),
  (2, 'Kohe Ayam Petelur',   'kg'),
  (2, 'Kencing Sapi',        'liter'),
  (2, 'Kencing Kambing',     'liter'),
  (2, 'Kencing Kelinci',     'liter'),
  (2, 'Air Kelapa',          'liter'),
  (2, 'Katalis Booster',     'liter'),
  (2, 'Kascing',             'kg'),
  (2, 'Rumen Sapi',          'kg'),
  (2, 'Gadung',              'kg'),
  (2, 'Sambiloto',           'kg');

-- ============================================================
-- 5. TABEL PRODUK
--    Produk yang diinput penjual (pupuk & bahan baku)
--    Wajib update setiap perubahan jumlah / harga
-- ============================================================
CREATE TABLE produk (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  toko_id         INT UNSIGNED     NOT NULL,
  jenis_produk_id SMALLINT UNSIGNED NOT NULL,
  jumlah_stok     DECIMAL(12,2)    NOT NULL DEFAULT 0
                  COMMENT 'Jumlah penjualan (liter/kg)',
  harga_jual      DECIMAL(15,2)    NOT NULL DEFAULT 0
                  COMMENT 'Harga per liter/kg, tidak termasuk ongkir',
  deskripsi       TEXT             NULL,
  foto_produk     VARCHAR(255)     NULL,
  is_tersedia     TINYINT(1)       NOT NULL DEFAULT 1,
  created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP
                                   ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_produk_toko  FOREIGN KEY (toko_id)
    REFERENCES toko(id) ON DELETE CASCADE,
  CONSTRAINT fk_produk_jenis FOREIGN KEY (jenis_produk_id)
    REFERENCES jenis_produk(id),
  UNIQUE KEY uq_toko_jenis (toko_id, jenis_produk_id)
    COMMENT 'Satu toko hanya boleh punya 1 entri per jenis produk'
) ENGINE=InnoDB;

-- ============================================================
-- 6. TABEL RIWAYAT HARGA PRODUK
--    Log setiap kali penjual update harga/jumlah
-- ============================================================
CREATE TABLE riwayat_produk (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  produk_id   INT UNSIGNED  NOT NULL,
  jumlah_lama DECIMAL(12,2) NOT NULL,
  jumlah_baru DECIMAL(12,2) NOT NULL,
  harga_lama  DECIMAL(15,2) NOT NULL,
  harga_baru  DECIMAL(15,2) NOT NULL,
  diubah_pada DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_riwayat_produk FOREIGN KEY (produk_id)
    REFERENCES produk(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 7. TABEL KERANJANG (CART)
--    Pembeli menambah produk ke keranjang sebelum checkout
-- ============================================================
CREATE TABLE keranjang (
  id         INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED  NOT NULL COMMENT 'Pembeli',
  produk_id  INT UNSIGNED  NOT NULL,
  jumlah     DECIMAL(10,2) NOT NULL DEFAULT 1,
  added_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_keranjang_user   FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_keranjang_produk FOREIGN KEY (produk_id)
    REFERENCES produk(id) ON DELETE CASCADE,
  UNIQUE KEY uq_cart_item (user_id, produk_id)
) ENGINE=InnoDB;

-- ============================================================
-- 8. TABEL PESANAN (ORDER HEADER)
--    Satu checkout bisa berisi produk dari 1 toko
--    (seperti Shopee: per-toko dipisah)
-- ============================================================
CREATE TABLE pesanan (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode_pesanan    VARCHAR(30)  NOT NULL UNIQUE
                  COMMENT 'Format: ORD-YYYYMMDD-XXXXX',
  pembeli_id      INT UNSIGNED NOT NULL,
  toko_id         INT UNSIGNED NOT NULL,
  total_harga     DECIMAL(15,2) NOT NULL DEFAULT 0,
  ongkir          DECIMAL(15,2) NOT NULL DEFAULT 0,
  grand_total     DECIMAL(15,2) NOT NULL DEFAULT 0,
  alamat_kirim    TEXT         NOT NULL,
  catatan         TEXT         NULL,
  status          ENUM(
                    'menunggu_konfirmasi',
                    'dikonfirmasi',
                    'diproses',
                    'dikirim',
                    'selesai',
                    'dibatalkan'
                  ) NOT NULL DEFAULT 'menunggu_konfirmasi',
  metode_bayar    ENUM('transfer','cod','ewallet') NOT NULL DEFAULT 'transfer',
  bukti_bayar     VARCHAR(255) NULL,
  created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
                               ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_pesanan_pembeli FOREIGN KEY (pembeli_id)
    REFERENCES users(id),
  CONSTRAINT fk_pesanan_toko   FOREIGN KEY (toko_id)
    REFERENCES toko(id)
) ENGINE=InnoDB;

-- ============================================================
-- 9. TABEL DETAIL PESANAN (ORDER ITEM)
--    Rincian produk per pesanan
-- ============================================================
CREATE TABLE detail_pesanan (
  id          INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
  pesanan_id  INT UNSIGNED  NOT NULL,
  produk_id   INT UNSIGNED  NOT NULL,
  nama_produk VARCHAR(150)  NOT NULL COMMENT 'Snapshot nama saat transaksi',
  satuan      ENUM('liter','kg') NOT NULL,
  jumlah      DECIMAL(10,2) NOT NULL,
  harga_satuan DECIMAL(15,2) NOT NULL COMMENT 'Snapshot harga saat transaksi',
  subtotal    DECIMAL(15,2) NOT NULL,
  CONSTRAINT fk_detail_pesanan FOREIGN KEY (pesanan_id)
    REFERENCES pesanan(id) ON DELETE CASCADE,
  CONSTRAINT fk_detail_produk  FOREIGN KEY (produk_id)
    REFERENCES produk(id)
) ENGINE=InnoDB;

-- ============================================================
-- 10. TABEL KONFIRMASI PENJUAL
--     Penjual konfirmasi/tolak pesanan masuk
-- ============================================================
CREATE TABLE konfirmasi_penjual (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pesanan_id  INT UNSIGNED NOT NULL UNIQUE,
  penjual_id  INT UNSIGNED NOT NULL,
  aksi        ENUM('dikonfirmasi','ditolak') NOT NULL,
  catatan     TEXT NULL,
  dilakukan_pada DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_konfirmasi_pesanan FOREIGN KEY (pesanan_id)
    REFERENCES pesanan(id) ON DELETE CASCADE,
  CONSTRAINT fk_konfirmasi_penjual FOREIGN KEY (penjual_id)
    REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- 11. TABEL ULASAN (REVIEW)
--     Pembeli beri ulasan setelah pesanan selesai
-- ============================================================
CREATE TABLE ulasan (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pesanan_id  INT UNSIGNED     NOT NULL,
  produk_id   INT UNSIGNED     NOT NULL,
  pembeli_id  INT UNSIGNED     NOT NULL,
  rating      TINYINT UNSIGNED NOT NULL CHECK (rating BETWEEN 1 AND 5),
  komentar    TEXT             NULL,
  created_at  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_ulasan (pesanan_id, produk_id),
  CONSTRAINT fk_ulasan_pesanan  FOREIGN KEY (pesanan_id)
    REFERENCES pesanan(id) ON DELETE CASCADE,
  CONSTRAINT fk_ulasan_produk   FOREIGN KEY (produk_id)
    REFERENCES produk(id),
  CONSTRAINT fk_ulasan_pembeli  FOREIGN KEY (pembeli_id)
    REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- 12. TABEL NOTIFIKASI
--     Notifikasi untuk penjual (ada pesanan baru) &
--     pembeli (status pesanan berubah)
-- ============================================================
CREATE TABLE notifikasi (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED  NOT NULL,
  judul       VARCHAR(150)  NOT NULL,
  pesan       TEXT          NOT NULL,
  tipe        ENUM('pesanan_masuk','konfirmasi','pengiriman',
                   'selesai','pembatalan','sistem') NOT NULL,
  referensi_id INT UNSIGNED NULL COMMENT 'id pesanan terkait',
  is_read     TINYINT(1)    NOT NULL DEFAULT 0,
  created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notif_user FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- 13. TABEL LOG AKTIVITAS ADMIN
--     Rekam semua aksi admin (audit trail)
-- ============================================================
CREATE TABLE log_admin (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  admin_id    INT UNSIGNED  NOT NULL,
  aksi        VARCHAR(255)  NOT NULL,
  target_tabel VARCHAR(100) NULL,
  target_id   INT UNSIGNED  NULL,
  keterangan  TEXT          NULL,
  ip_address  VARCHAR(45)   NULL,
  created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_admin FOREIGN KEY (admin_id)
    REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- DATA AWAL: Akun Admin Default
-- ============================================================
INSERT INTO users (nama, angkatan, jenis_keanggotaan, alamat, email, password, role)
VALUES (
  'Administrator',
  'Admin',
  'bukan_penjual',
  'Kantor PTS Jatim',
  'admin@ptsjatim.id',
  '$2y$10$placeholderHashGantiDenganBcrypt',  -- ganti dengan hash bcrypt
  'admin'
);

-- ============================================================
-- INDEXES TAMBAHAN (performa query)
-- ============================================================
CREATE INDEX idx_produk_toko       ON produk(toko_id);
CREATE INDEX idx_produk_jenis      ON produk(jenis_produk_id);
CREATE INDEX idx_pesanan_pembeli   ON pesanan(pembeli_id);
CREATE INDEX idx_pesanan_toko      ON pesanan(toko_id);
CREATE INDEX idx_pesanan_status    ON pesanan(status);
CREATE INDEX idx_keranjang_user    ON keranjang(user_id);
CREATE INDEX idx_notifikasi_user   ON notifikasi(user_id, is_read);
CREATE INDEX idx_log_admin_id      ON log_admin(admin_id);
