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
        <div class="sidebar-head">
            <h3><span class="material-symbols-outlined icon">storefront</span> Halo</h3>
            <p><?php echo htmlspecialchars($toko['nama_toko']); ?></p>
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-title">Menu Utama</li>
            <li><a href="dashboard.php"><span class="material-symbols-outlined icon">dashboard</span> Dashboard</a></li>
            <li><a href="produk.php" class="active"><span class="material-symbols-outlined icon">inventory_2</span> Produk Saya</a></li>
            <li><a href="pesanan.php"><span class="material-symbols-outlined icon">receipt_long</span> Pesanan Masuk</a></li>
            <li><a href="riwayat.php"><span class="material-symbols-outlined icon">bar_chart</span> Riwayat Penjualan</a></li>
            <li class="sidebar-title">Pengaturan</li>
            <li><a href="toko_edit.php"><span class="material-symbols-outlined icon">settings</span> Atur Toko</a></li>
            <li class="sidebar-title">Akun</li>
            <li><a href="../auth/logout.php"><span class="material-symbols-outlined icon">logout</span> Logout</a></li>
        </ul>
    </div>
    
    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- NAVBAR -->
        <div class="navbar">
            <div class="navbar-right">
                <div class="navbar-user">
                    <div class="avatar">
                        <?php if (!empty($_SESSION['avatar']) && file_exists(__DIR__ . '/../uploads/' . $_SESSION['avatar'])): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($_SESSION['avatar']); ?>" alt="avatar">
                        <?php else: ?>
                            <span class="avatar-initials"><?php echo strtoupper(substr(trim($_SESSION['nama'] ?? 'P'),0,1)); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Penjual'); ?></div>
                        <div class="user-role"><span class="badge badge-<?php echo strtolower($_SESSION['role'] ?? 'penjual'); ?>"><?php echo htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Penjual')); ?></span></div>
                    </div>
                    <div class="navbar-links">
                        <a href="produk.php" class="btn btn-secondary btn-sm">Kembali</a>
                    </div>
                </div>
            </div>
        </div>
        
        <h1 class="page-title">Tambah Produk Baru</h1>

        <?php if (isset($_GET['error']) && $_GET['error'] === 'duplicate'): ?>
            <div class="alert alert-warning" style="max-width: 600px; margin: 0 auto 20px auto;">Produk ini sudah terdaftar. Silakan edit produk yang sudah ada atau tambahkan produk yang berbeda.</div>
        <?php endif; ?>
        
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <form method="POST" action="proses/tambah_produk.php" enctype="multipart/form-data" novalidate>
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
                    <label for="harga_display">Harga Jual (Rp/<span id="harga_satuan_label">Unit</span>) *</label>
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
                    <button type="submit" class="btn btn-primary btn-block"><span class="material-symbols-outlined">save</span> Simpan Produk</button>
                    <a href="produk.php" class="btn btn-secondary btn-block"><span class="material-symbols-outlined">arrow_back</span> Batal</a>
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
const hargaSatuanLabel = document.getElementById('harga_satuan_label');

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
            if (hargaSatuanLabel) {
                hargaSatuanLabel.textContent = satuan.toUpperCase();
            }
        } else {
            stokHelp.textContent = 'Satuan mengikuti jenis produk yang dipilih (kg/liter).';
            if (hargaSatuanLabel) {
                hargaSatuanLabel.textContent = 'Unit';
            }
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
        const jenisValue = jenisSelect?.value || '';
        const stokValue = document.getElementById('stok')?.value?.trim() || '';
        const hargaValue = hargaHidden.value?.trim() || '';

        if (!jenisValue) {
            event.preventDefault();
            alert('Silakan pilih jenis produk terlebih dahulu.');
            return;
        }

        if (!hargaValue) {
            event.preventDefault();
            alert('Harga jual tidak boleh kosong.');
            return;
        }

        if (!stokValue || Number(stokValue) < 0) {
            event.preventDefault();
            alert('Jumlah stok wajib diisi dengan angka yang valid.');
        }
    });
}
</script>
<script src="../js/admin-responsive.js"></script>
</body>
</html>
