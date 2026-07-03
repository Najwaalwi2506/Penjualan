<?php
include '../Koneksi.php';
check_login();
check_role(['penjual']);

$user_id = $_SESSION['user_id'];
$toko = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM toko WHERE user_id = $user_id"));
$toko_id = $toko['id'];

// Ambil kategori dan jenis produk
$kategori = mysqli_query($koneksi, "SELECT * FROM kategori_produk ORDER BY nama");
$jenis = mysqli_query($koneksi, "SELECT j.*, k.nama as nama_kategori FROM jenis_produk j JOIN kategori_produk k ON j.kategori_id = k.id ORDER BY k.nama, j.nama_jenis");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="wrapper">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div style="padding: 20px; border-bottom: 1px solid #444;">
            <h3 style="color: #667eea; font-size: 18px;">🏪 Toko Saya</h3>
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-title">Menu Utama</li>
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="produk.php" class="active">📦 Produk Saya</a></li>
            <li><a href="pesanan.php">📋 Pesanan Masuk</a></li>
            <li><a href="riwayat.php">📈 Riwayat Penjualan</a></li>
            <li class="sidebar-title">Pengaturan</li>
            <li><a href="toko_edit.php">⚙️ Atur Toko</a></li>
            <li class="sidebar-title">Akun</li>
            <li><a href="../auth/logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    
    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- NAVBAR -->
        <div class="navbar">
            <div class="navbar-brand">📦 Tambah Produk Baru</div>
            <div class="navbar-right">
                <div class="navbar-links">
                    <a href="produk.php">← Kembali</a>
                </div>
            </div>
        </div>
        
        <h1 class="page-title">📦 Tambah Produk Baru</h1>
        
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <form method="POST" action="proses/tambah_produk.php" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="jenis">Jenis Produk *</label>
                    <select id="jenis" name="jenis_produk_id" required>
                        <option value="">- Pilih Jenis Produk -</option>
                        <?php 
                        while ($row = mysqli_fetch_assoc($jenis)) {
                            echo "<option value='{$row['id']}' data-satuan='{$row['satuan']}'>{$row['nama_jenis']} ({$row['nama_kategori']})</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="harga_display">Harga Jual (Rp) *</label>
                    <input type="text" id="harga_display" inputmode="numeric" placeholder="Contoh: 500000" required>
                    <input type="hidden" id="harga_jual" name="harga_jual" value="">
                    <small id="harga_preview" style="color:#666;">Isi nominal tanpa tanda titik atau koma.</small>
                </div>
                
                <div class="form-group">
                    <label for="stok">Jumlah Stok *</label>
                    <input type="number" id="stok" name="jumlah_stok" placeholder="0" required min="0" step="0.01">
                    <small id="stok_help">Satuan mengikuti jenis produk yang dipilih (kg/liter).</small>
                </div>
                
                <div class="form-group">
                    <label for="deskripsi">Deskripsi Produk</label>
                    <textarea id="deskripsi" name="deskripsi" placeholder="Masukkan deskripsi produk..." rows="4"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="foto">Foto Produk</label>
                    <input type="file" id="foto" name="foto_produk" accept="image/*">
                    <small>Format: JPG, PNG (Max 2MB)</small>
                </div>
                
                <div class="form-group">
                    <label for="status">
                        <input type="checkbox" id="status" name="is_tersedia" value="1" checked>
                        Produk Tersedia
                    </label>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary btn-block">✓ Simpan Produk</button>
                    <a href="produk.php" class="btn btn-secondary btn-block">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
const jenisSelect = document.getElementById('jenis');
const stokHelp = document.getElementById('stok_help');
const hargaDisplay = document.getElementById('harga_display');
const hargaHidden = document.getElementById('harga_jual');
const hargaPreview = document.getElementById('harga_preview');

function formatRupiah(value) {
    const digits = String(value).replace(/\D/g, '');
    if (!digits) return '';
    const number = parseInt(digits, 10);
    return new Intl.NumberFormat('id-ID').format(number);
}

if (jenisSelect) {
    jenisSelect.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const satuan = selected?.getAttribute('data-satuan') || '';
        if (satuan) {
            stokHelp.textContent = 'Satuan yang dipakai: ' + satuan.toUpperCase();
        } else {
            stokHelp.textContent = 'Satuan mengikuti jenis produk yang dipilih (kg/liter).';
        }
    });
}

if (hargaDisplay) {
    hargaDisplay.addEventListener('input', function () {
        const digits = this.value.replace(/\D/g, '');
        hargaHidden.value = digits;
        this.value = formatRupiah(digits);
        hargaPreview.textContent = digits ? 'Preview: Rp ' + formatRupiah(digits) : 'Isi nominal tanpa tanda titik atau koma.';
    });
}

const form = document.querySelector('form');
if (form) {
    form.addEventListener('submit', function (event) {
        if (!hargaHidden.value) {
            event.preventDefault();
            alert('Harga jual wajib diisi.');
        }
    });
}
</script>
</body>
</html>
