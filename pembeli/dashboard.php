<?php
include '../Koneksi.php';
check_login();
check_role(['pembeli']);

$user_id = $_SESSION['user_id'];

$nama_barang_id = isset($_GET['nama_barang']) ? (int)$_GET['nama_barang'] : 0;
$daerah = isset($_GET['daerah']) ? trim($_GET['daerah']) : '';
$kategori_id = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'terbaru';
$sort = in_array($sort, ['terbaru', 'termurah', 'termahal'], true) ? $sort : 'terbaru';

// Jika nama_barang dipilih, pastikan kategori_id konsisten dengan kategori jenis produk itu
// supaya tidak terjadi kombinasi kategori + nama barang yang saling bertentangan
if ($nama_barang_id > 0) {
    $cek_kategori = mysqli_query($koneksi, "SELECT kategori_id FROM jenis_produk WHERE id = $nama_barang_id");
    if ($row_kat = mysqli_fetch_assoc($cek_kategori)) {
        $kategori_id = (int)$row_kat['kategori_id'];
    }
}

$nama_barang_options_query = "
    SELECT DISTINCT j.id AS jenis_id, j.nama_jenis, j.kategori_id
    FROM produk p
    JOIN jenis_produk j ON p.jenis_produk_id = j.id
    JOIN toko t ON p.toko_id = t.id
    JOIN users u ON t.user_id = u.id
    WHERE p.is_tersedia = 1 AND u.is_active = 1
    ORDER BY j.kategori_id, j.nama_jenis
";
$nama_barang_options_result = mysqli_query($koneksi, $nama_barang_options_query);
$nama_barang_options = [];
while ($option = mysqli_fetch_assoc($nama_barang_options_result)) {
    $nama_barang_options[] = [
        'id' => (int)$option['jenis_id'],
        'nama' => $option['nama_jenis'],
        'kategori_id' => (int)$option['kategori_id']
    ];
}

// Ambil produk sesuai pencarian, filter kategori, dan urutan harga
$query = "
    SELECT p.*, j.nama_jenis, j.satuan, t.nama_toko, k.nama as nama_kategori, u.nama as penjual_nama, u.alamat
    FROM produk p
    JOIN jenis_produk j ON p.jenis_produk_id = j.id
    JOIN toko t ON p.toko_id = t.id
    JOIN kategori_produk k ON j.kategori_id = k.id
    JOIN users u ON t.user_id = u.id
    WHERE p.is_tersedia = 1 AND u.is_active = 1";

if ($nama_barang_id > 0) {
    $query .= " AND j.id = $nama_barang_id";
}

if ($daerah !== '') {
    $keyword_daerah = mysqli_real_escape_string($koneksi, $daerah);
    $query .= " AND (u.alamat LIKE '%$keyword_daerah%' OR t.nama_toko LIKE '%$keyword_daerah%' OR u.nama LIKE '%$keyword_daerah%')";
}

if ($kategori_id > 0) {
    $query .= " AND j.kategori_id = $kategori_id";
}

switch ($sort) {
    case 'termurah':
        $query .= " ORDER BY p.harga_jual ASC";
        break;
    case 'termahal':
        $query .= " ORDER BY p.harga_jual DESC";
        break;
    default:
        $query .= " ORDER BY p.created_at DESC";
        break;
}

$result = mysqli_query($koneksi, $query);
$total_produk = mysqli_num_rows($result);
$cart_count = !empty($_SESSION['cart']) && is_array($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belanja Pupuk - PTS JATIM</title>
    <link rel="stylesheet" href="../pembeli/Style.css">
</head>
<body>
<!-- NAVBAR -->
<div class="navbar">
    <div class="navbar-brand">
        <span class="brand-icon material-symbols-outlined">shopping_bag</span>
        Toko Pupuk Online
    </div>
    <div class="navbar-actions">
        <div class="navbar-user">
            <span>Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
        </div>
        <a href="dashboard.php"><span class="material-symbols-outlined">home</span> Beranda</a>
        <a href="keranjang.php" class="cart-badge"><span class="material-symbols-outlined">shopping_cart</span> Keranjang<span class="cart-counter"><?php echo $cart_count; ?></span></a>
        <a href="pesanan.php"><span class="material-symbols-outlined">receipt_long</span> Pesanan Saya</a>
        <a href="../auth/logout.php"><span class="material-symbols-outlined">logout</span> Logout</a>
    </div>
    <details class="navbar-mobile">
        <summary><span>Menu</span><span class="material-symbols-outlined">menu</span></summary>
        <div class="mobile-actions">
            <a href="dashboard.php"><span class="material-symbols-outlined">home</span> Beranda</a>
            <a href="keranjang.php"><span class="material-symbols-outlined">shopping_cart</span> Keranjang</a>
            <a href="pesanan.php"><span class="material-symbols-outlined">receipt_long</span> Pesanan Saya</a>
            <a href="../auth/logout.php"><span class="material-symbols-outlined">logout</span> Logout</a>
        </div>
    </details>
</div>

<div class="main-content page-shell">
    <div class="hero-banner">
        <div class="hero-grid">
            <div>
                <span class="hero-tag"><span class="material-symbols-outlined">rocket_launch</span> Belanja Mudah</span>
                <h1 class="hero-headline">Pupuk Berkualitas Hasil Panen Optimal</h1>
                <p class="hero-text">Sistem Informasi Pupuk Organik dan Bahan Baku dengan harga yang terjangkau.</p>
                <a href="#produk" class="btn btn-primary hero-cta">Belanja Sekarang</a>
            </div>
            <div class="hero-visual">
                <div class="hero-visual-card">
                    <span class="hero-visual-label">Pupuk Organik</span>
                    <div class="hero-visual-img">🌱</div>
                    <div class="hero-visual-info">
                        <div class="hero-visual-name">Pupuk Organik 25kg</div>
                        <div class="hero-visual-price">Rp 80.000</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-icon material-symbols-outlined">inventory_2</div>
            <div class="stat-number"><?php echo $total_produk; ?></div>
            <div class="stat-label">Produk Tersedia</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon material-symbols-outlined">shopping_cart</div>
            <div class="stat-number"><?php echo $cart_count; ?></div>
            <div class="stat-label">Item Keranjang</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon material-symbols-outlined">history</div>
            <div class="stat-number">0</div>
            <div class="stat-label">Pesanan Saya</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon material-symbols-outlined">savings</div>
            <div class="stat-number">Rp 0</div>
            <div class="stat-label">Total Transaksi</div>
        </div>
    </div>

    <!-- FORM PENCARIAN: Kategori -> Nama Barang -> Daerah -> Harga -->
    <div class="search-card search-card-simple">
        <div class="search-card-title">
            <span class="material-symbols-outlined">search</span>
            Cari Pupuk yang Anda Butuhkan
        </div>
        <form method="GET" class="search-form search-form-simple">
            <div class="form-group">
                <label for="kategori"><span class="step-number">1</span> Kategori</label>
                <select id="kategori" name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php 
                    $kat = mysqli_query($koneksi, "SELECT * FROM kategori_produk ORDER BY nama");
                    while ($k = mysqli_fetch_assoc($kat)) {
                        $selected = ($kategori_id == $k['id']) ? 'selected' : '';
                        echo "<option value='{$k['id']}' $selected>" . htmlspecialchars($k['nama']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="nama_barang"><span class="step-number">2</span> Nama Barang</label>
                <select id="nama_barang" name="nama_barang">
                    <option value="">Semua Nama Barang</option>
                    <?php foreach ($nama_barang_options as $option): ?>
                        <option value="<?php echo (int)$option['id']; ?>" <?php echo ($nama_barang_id === (int)$option['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($option['nama']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="daerah"><span class="step-number">3</span> Daerah</label>
                <input type="text" id="daerah" name="daerah" placeholder="Contoh: Surabaya" value="<?php echo htmlspecialchars($daerah); ?>">
            </div>
            <div class="form-group">
                <label for="sort"><span class="step-number">4</span> Urutkan Harga</label>
                <select id="sort" name="sort">
                    <option value="terbaru" <?php echo $sort === 'terbaru' ? 'selected' : ''; ?>>Terbaru</option>
                    <option value="termurah" <?php echo $sort === 'termurah' ? 'selected' : ''; ?>>Termurah</option>
                    <option value="termahal" <?php echo $sort === 'termahal' ? 'selected' : ''; ?>>Termahal</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary search-btn search-btn-simple">
                <span class="material-symbols-outlined">search</span> Cari Sekarang
            </button>
        </form>
    </div>

    <div class="section-heading">
        <span class="material-symbols-outlined">star</span>
        <h2>Produk Terbaik</h2>
    </div>

    <!-- PRODUK GRID -->
    <?php if ($total_produk > 0) { ?>
    <div id="produk" class="products-grid">
        <?php 
        while ($row = mysqli_fetch_assoc($result)) { 
            $has_badge = !empty($row['nama_kategori']);
        ?>
        <div class="product-card">
            <div class="product-image">
                <?php if (!empty($row['foto_produk']) && file_exists('../uploads/' . $row['foto_produk'])) { ?>
                    <img src="../uploads/<?php echo htmlspecialchars($row['foto_produk']); ?>" alt="Foto produk">
                <?php } else { ?>
                    <div class="image-placeholder">📦</div>
                <?php } ?>
                <?php if ($has_badge) { ?>
                <span class="product-badge"><?php echo htmlspecialchars($row['nama_kategori']); ?></span>
                <?php } ?>
            </div>
            <div class="product-body">
                <div class="product-title"><?php echo htmlspecialchars($row['nama_jenis']); ?></div>
                <div class="product-store"><?php echo htmlspecialchars($row['nama_toko']); ?></div>
                <div class="product-location">Lokasi: <?php echo htmlspecialchars($row['alamat']); ?></div>
                <div class="product-price"><?php echo format_rupiah($row['harga_jual']); ?>/<?php echo htmlspecialchars($row['satuan']); ?></div>
                <form method="POST" action="../proses/add_keranjang.php" class="product-actions">
                    <input type="hidden" name="produk_id" value="<?php echo $row['id']; ?>">
                    <input type="number" name="jumlah" class="product-qty" value="1" min="1" max="<?php echo $row['jumlah_stok']; ?>">
                    <button type="submit" class="btn btn-outline">Tambah Keranjang</button>
                    <button type="submit" class="btn btn-primary">Beli</button>
                </form>
            </div>
        </div>
        <?php } ?>
    </div>
    <?php } else { ?>
    <div class="card text-center">
        <p style="color: #999; padding: 40px;">Belum ada produk tersedia</p>
    </div>
    <?php } ?>
</div>
<script>
const categorySelect = document.getElementById('kategori');
const namaBarangSelect = document.getElementById('nama_barang');
const namaBarangOptions = <?php echo json_encode($nama_barang_options, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;

function renderNamaBarangOptions(selectedCategoryId) {
    if (!namaBarangSelect) return;

    const currentValue = namaBarangSelect.value;
    namaBarangSelect.innerHTML = '<option value="">Semua Nama Barang</option>';

    namaBarangOptions
        .filter(function (option) {
            return !selectedCategoryId || String(option.kategori_id) === String(selectedCategoryId);
        })
        .forEach(function (option) {
            const opt = document.createElement('option');
            opt.value = option.id;
            opt.textContent = option.nama;
            if (String(currentValue) === String(option.id)) {
                opt.selected = true;
            }
            namaBarangSelect.appendChild(opt);
        });
}

if (categorySelect && namaBarangSelect) {
    categorySelect.addEventListener('change', function () {
        renderNamaBarangOptions(this.value);
    });

    renderNamaBarangOptions(categorySelect.value);
}
</script>
</body>
</html>