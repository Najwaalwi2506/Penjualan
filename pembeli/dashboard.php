<?php
include '../Koneksi.php';
check_login();
check_role(['pembeli']);

$user_id = $_SESSION['user_id'];

// Ambil semua produk
$query = "
    SELECT p.*, j.nama_jenis, j.satuan, t.nama_toko, k.nama as nama_kategori, u.nama as penjual_nama, u.alamat
    FROM produk p
    JOIN jenis_produk j ON p.jenis_produk_id = j.id
    JOIN toko t ON p.toko_id = t.id
    JOIN kategori_produk k ON j.kategori_id = k.id
    JOIN users u ON t.user_id = u.id
    WHERE p.is_tersedia = 1 AND u.is_active = 1
    ORDER BY p.created_at DESC
";

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
                <p class="hero-text">Temukan pupuk terbaik untuk kebutuhan pertanian Anda dengan harga terjangkau, kualitas terjamin, dan pengiriman cepat ke lahan.</p>
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

    <div class="search-card">
        <form method="GET" class="search-form">
            <div class="form-group">
                <label for="cari">Cari Produk</label>
                <input type="text" id="cari" name="cari" placeholder="Cari nama produk..." value="<?php echo isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="kategori">Semua Kategori</label>
                <select id="kategori" name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php 
                    $kat = mysqli_query($koneksi, "SELECT * FROM kategori_produk");
                    while ($k = mysqli_fetch_assoc($kat)) {
                        $selected = (isset($_GET['kategori']) && $_GET['kategori'] == $k['id']) ? 'selected' : '';
                        echo "<option value='{$k['id']}' $selected>" . htmlspecialchars($k['nama']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Cari</button>
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
                <div class="product-price"><?php echo format_rupiah($row['harga_jual']); ?>/<?php echo htmlspecialchars($row['satuan']); ?></div>
                <div class="product-rating"><span class="material-symbols-outlined">star</span> 4.8 <span>(120)</span></div>
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
</body>
</html>
