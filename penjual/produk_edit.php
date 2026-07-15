<?php
include '../Koneksi.php';
check_login();
check_role(['penjual']);

$user_id = $_SESSION['user_id'];
$toko = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM toko WHERE user_id = $user_id"));
$toko_id = $toko['id'];

// Ambil ID produk dari parameter
$produk_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$produk_id) {
    die('Produk tidak ditemukan');
}

// Ambil data produk
$produk = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT p.*, j.nama_jenis, j.satuan, k.nama as kategori
    FROM produk p
    JOIN jenis_produk j ON p.jenis_produk_id = j.id
    JOIN kategori_produk k ON j.kategori_id = k.id
    WHERE p.id = $produk_id AND p.toko_id = $toko_id
"));

if (!$produk) {
    die('Produk tidak ditemukan atau Anda tidak memiliki akses');
}

// Ambil kategori dan jenis produk
$kategori = mysqli_query($koneksi, "SELECT * FROM kategori_produk ORDER BY nama");
$jenis = mysqli_query($koneksi, "SELECT j.*, k.nama as nama_kategori FROM jenis_produk j JOIN kategori_produk k ON j.kategori_id = k.id ORDER BY k.nama, j.nama_jenis");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk</title>
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
        
        <h1 class="page-title">Edit Produk: <?php echo htmlspecialchars($produk['nama_jenis']); ?></h1>
        
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <form method="POST" action="proses/edit_produk.php" enctype="multipart/form-data">
                <input type="hidden" name="produk_id" value="<?php echo $produk_id; ?>">
                
                <div class="form-group">
                    <label for="jenis">Jenis Produk *</label>
                    <select id="jenis" name="jenis_produk_id" required>
                        <option value="">- Pilih Jenis Produk -</option>
                        <?php 
                        mysqli_data_seek($jenis, 0);
                        while ($row = mysqli_fetch_assoc($jenis)) {
                            $selected = ($row['id'] == $produk['jenis_produk_id']) ? 'selected' : '';
                            echo "<option value='{$row['id']}' data-satuan='{$row['satuan']}' $selected>{$row['nama_jenis']} ({$row['nama_kategori']})</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="harga_display">Harga Jual (Rp/<?php echo htmlspecialchars(strtoupper($produk['satuan'] ?? 'unit')); ?>) *</label>
                    <input type="text" id="harga_display" inputmode="numeric" placeholder="Contoh: 500000" value="<?php echo number_format($produk['harga_jual'], 0, '', '.'); ?>" required>
                    <input type="hidden" id="harga_jual" name="harga_jual" value="<?php echo $produk['harga_jual']; ?>">
                    <small id="harga_preview" style="color:#666;">Preview: Rp <?php echo number_format($produk['harga_jual'], 0, '', '.'); ?> per <?php echo htmlspecialchars(strtoupper($produk['satuan'] ?? 'unit')); ?></small>
                </div>
                
                <div class="form-group">
                    <label for="stok">Jumlah Stok *</label>
                    <input type="number" id="stok" name="jumlah_stok" placeholder="0" value="<?php echo format_stock($produk['jumlah_stok']); ?>" required min="0" step="0.01">
                    <small id="stok_help">Satuan: <strong><?php echo strtoupper($produk['satuan']); ?></strong></small>
                </div>
                
                <div class="form-group">
                    <label for="deskripsi">Deskripsi Produk</label>
                    <textarea id="deskripsi" name="deskripsi" placeholder="Masukkan deskripsi produk..." rows="4"><?php echo htmlspecialchars($produk['deskripsi']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="foto">Foto Produk</label>
                    <?php if (!empty($produk['foto_produk']) && file_exists(__DIR__ . '/../uploads/' . $produk['foto_produk'])): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="../uploads/<?php echo htmlspecialchars($produk['foto_produk']); ?>" alt="Foto Produk" style="max-width: 150px; border-radius: 8px;">
                            <p style="font-size: 0.85rem; color: #666; margin-top: 5px;">Foto saat ini</p>
                        </div>
                    <?php endif; ?>
                    <input type="file" id="foto" name="foto_produk" accept="image/*">
                    <small>Biarkan kosong jika tidak ingin mengubah foto. Format: JPG, PNG (Max 2MB)</small>
                </div>
                
                <div class="form-group">
                    <label for="status">
                        <input type="checkbox" id="status" name="is_tersedia" value="1" <?php echo $produk['is_tersedia'] ? 'checked' : ''; ?>>
                        Produk Tersedia
                    </label>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-primary btn-block"><span class="material-symbols-outlined">save</span> Simpan Perubahan</button>
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
<script src="../js/admin-responsive.js"></script>
</body>
</html>
