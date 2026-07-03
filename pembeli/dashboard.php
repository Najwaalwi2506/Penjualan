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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Belanja Pupuk - PTS JATIM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<!-- NAVBAR -->
<div class="navbar">
    <div class="navbar-brand">🛒 Toko Pupuk Online</div>
    <div class="navbar-right">
        <div class="navbar-user">
            <span><?php echo $_SESSION['nama']; ?></span>
        </div>
        <div class="navbar-links">
            <a href="keranjang.php">🛒 Keranjang</a>
            <a href="pesanan.php">📋 Pesanan Saya</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>
</div>

<div class="main-content" style="max-width: 1400px; margin: 0 auto;">
    <div class="card hero-banner" style="margin-bottom: 24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; gap:20px; flex-wrap:wrap;">
            <div>
                <div style="display:inline-block; padding:6px 12px; border-radius:999px; background:rgba(255,255,255,0.15); font-size:12px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; margin-bottom:10px;">Belanja organik</div>
                <h1 class="page-title" style="margin-bottom:6px; color:white;">🌾 Belanja Pupuk Berkualitas</h1>
                <p style="margin:0; color:#e7f7e4;">Temukan produk yang sesuai kebutuhan Anda dari penjual terpercaya.</p>
            </div>
            <div style="background:rgba(255,255,255,0.14); padding:14px 18px; border-radius:14px; min-width:180px;">
                <div style="font-size:12px; text-transform:uppercase; opacity:.8;">Produk tersedia</div>
                <div style="font-size:24px; font-weight:700; margin-top:4px;"><?php echo $total_produk; ?></div>
            </div>
        </div>
    </div>
    
    <!-- FILTER -->
    <div class="card" style="margin-bottom: 30px;">
        <form method="GET" style="display: flex; gap: 15px; align-items: flex-end;">
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label for="cari">Cari Produk</label>
                <input type="text" id="cari" name="cari" placeholder="Cari nama produk..." value="<?php echo isset($_GET['cari']) ? $_GET['cari'] : ''; ?>">
            </div>
            <div class="form-group" style="flex: 1; margin-bottom: 0;">
                <label for="kategori">Kategori</label>
                <select id="kategori" name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php 
                    $kat = mysqli_query($koneksi, "SELECT * FROM kategori_produk");
                    while ($k = mysqli_fetch_assoc($kat)) {
                        $selected = (isset($_GET['kategori']) && $_GET['kategori'] == $k['id']) ? 'selected' : '';
                        echo "<option value='{$k['id']}' $selected>{$k['nama']}</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>
    </div>
    
    <!-- PRODUK GRID -->
    <?php if ($total_produk > 0) { ?>
    <div class="grid grid-3">
        <?php 
        while ($row = mysqli_fetch_assoc($result)) { 
        ?>
        <div class="product-card">
            <div class="product-image" style="display:flex; align-items:center; justify-content:center; min-height:140px; overflow:hidden; background:#f7f9fc;">
                <?php if (!empty($row['foto_produk']) && file_exists('../uploads/' . $row['foto_produk'])) { ?>
                    <img src="../uploads/<?php echo htmlspecialchars($row['foto_produk']); ?>" alt="Foto produk" style="width:100%; height:140px; object-fit:cover;">
                <?php } else { ?>
                    <span style="font-size:42px;">📦</span>
                <?php } ?>
            </div>
            <div class="product-info">
                <div class="product-name"><?php echo $row['nama_jenis']; ?></div>
                <div class="product-seller" style="margin-bottom: 5px;">
                    <strong>Penjual: <?php echo htmlspecialchars($row['nama_toko']); ?></strong><br>
                    <small><?php echo $row['penjual_nama']; ?></small>
                </div>
                <div style="background: #f5f7fa; padding: 8px; border-radius: 5px; margin-bottom: 10px; font-size: 12px;">
                    <span class="badge badge-info"><?php echo $row['nama_kategori']; ?></span>
                </div>
                <div class="product-price"><?php echo format_rupiah($row['harga_jual']); ?>/<?php echo $row['satuan']; ?></div>
                <div class="product-stok">Stok: <?php echo $row['jumlah_stok']; ?> <?php echo $row['satuan']; ?></div>
                <form method="POST" action="../proses/add_keranjang.php" style="display: flex; gap: 10px;">
                    <input type="hidden" name="produk_id" value="<?php echo $row['id']; ?>">
                    <input type="number" name="jumlah" value="1" min="1" max="<?php echo $row['jumlah_stok']; ?>" style="width: 70px; padding: 8px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">🛒 Beli</button>
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
