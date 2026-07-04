<?php
include '../Koneksi.php';
check_login();
check_role(['pembeli']);

$user_id = $_SESSION['user_id'];
$pesanan_id = sanitize($koneksi, $_GET['id']);

// Ambil data pesanan
$pesanan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT p.*, p.alamat_kirim AS alamat, t.nama_toko, u.nama as penjual, u.no_telp as penjual_no_telp
    FROM pesanan p
    JOIN toko t ON p.toko_id = t.id
    JOIN users u ON t.user_id = u.id
    WHERE p.id = $pesanan_id AND p.pembeli_id = $user_id
"));

if (!$pesanan) {
    die('Pesanan tidak ditemukan');
}

// Ambil detail pesanan (ONE-TO-MANY: 1 Pesanan punya MANY Detail Item)
$detail = mysqli_query($koneksi, "
    SELECT dp.*, p.*, j.nama_jenis, j.satuan
    FROM detail_pesanan dp
    JOIN produk p ON dp.produk_id = p.id
    JOIN jenis_produk j ON p.jenis_produk_id = j.id
    WHERE dp.pesanan_id = $pesanan_id
");
$cart_count = !empty($_SESSION['cart']) && is_array($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
$penjual_phone = !empty($pesanan['penjual_no_telp']) ? preg_replace('/\D+/', '', $pesanan['penjual_no_telp']) : '';
if ($penjual_phone !== '' && strpos($penjual_phone, '0') === 0) {
    $penjual_phone = '62' . substr($penjual_phone, 1);
}
$penjual_contact_url = $penjual_phone !== '' ? 'https://wa.me/' . $penjual_phone : '#';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan</title>
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

<div class="main-content page-shell" style="max-width: 900px; margin: 0 auto;">
    <h1 class="page-title">📋 Detail Pesanan <?php echo $pesanan['kode_pesanan']; ?></h1>
    <p class="page-subtitle">Relasi ONE-TO-MANY: 1 Pesanan memiliki MANY Detail Item</p>
    
    <div class="order-detail-header">
        <div class="order-title">
            <div>
                <h1>Detail Pesanan</h1>
                <p style="color: #64748b; margin-top: 8px;">#<?php echo $pesanan['kode_pesanan']; ?></p>
            </div>
            <span class="order-status-pill"><span class="material-symbols-outlined">info</span><?php echo ucfirst(str_replace('_', ' ', $pesanan['status'])); ?></span>
        </div>
        <div class="order-detail-meta">
            <div class="meta-item">
                <span>Tanggal Pesan</span>
                <strong><?php echo date('d/m/Y H:i', strtotime($pesanan['created_at'])); ?></strong>
            </div>
            <div class="meta-item">
                <span>Penjual</span>
                <strong><?php echo htmlspecialchars($pesanan['penjual']); ?></strong>
            </div>
            <div class="meta-item">
                <span>Toko</span>
                <strong><?php echo htmlspecialchars($pesanan['nama_toko']); ?></strong>
            </div>
            <div class="meta-item">
                <span>Alamat Kirim</span>
                <strong><?php echo htmlspecialchars($pesanan['alamat']); ?></strong>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">💼 Informasi Pesanan</div>
        <div style="padding: 20px;">
            <div class="order-detail-meta">
                <div class="meta-item">
                    <span>Kode Pesanan</span>
                    <strong><?php echo $pesanan['kode_pesanan']; ?></strong>
                </div>
                <div class="meta-item">
                    <span>Status</span>
                    <strong><?php echo ucfirst(str_replace('_', ' ', $pesanan['status'])); ?></strong>
                </div>
                <div class="meta-item">
                    <span>Alamat Pengiriman</span>
                    <strong><?php echo htmlspecialchars($pesanan['alamat']); ?></strong>
                </div>
                <div class="meta-item">
                    <span>Catatan</span>
                    <strong><?php echo !empty($pesanan['catatan']) ? htmlspecialchars($pesanan['catatan']) : '-'; ?></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <div class="card-header">📦 Daftar Produk</div>
        <div class="order-products-list">
            <?php 
            $total_item = 0;
            mysqli_data_seek($detail, 0);
            while ($row = mysqli_fetch_assoc($detail)) { 
                $total_item++;
            ?>
            <div class="order-product-card">
                <div class="order-product-image">
                    <?php if (!empty($row['foto_produk']) && file_exists('../uploads/' . $row['foto_produk'])) { ?>
                        <img src="../uploads/<?php echo htmlspecialchars($row['foto_produk']); ?>" alt="<?php echo htmlspecialchars($row['nama_jenis']); ?>">
                    <?php } else { ?>
                        <span class="material-symbols-outlined" style="font-size: 2rem; color: #94a3b8;">inventory_2</span>
                    <?php } ?>
                </div>
                <div class="order-product-info">
                    <div class="product-name"><?php echo htmlspecialchars($row['nama_jenis']); ?></div>
                    <div class="product-meta"><span><strong>Qty:</strong> <?php echo $row['jumlah']; ?></span></div>
                    <div class="product-meta"><span><strong>Harga:</strong> <?php echo format_rupiah($row['harga_satuan']); ?></span></div>
                    <div class="product-meta"><span><strong>Subtotal:</strong> <?php echo format_rupiah($row['subtotal']); ?></span></div>
                    <div class="product-meta"><span><strong>Satuan:</strong> <?php echo htmlspecialchars($row['satuan']); ?></span></div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <div class="card" style="margin-top: 20px;">
        <div class="card-header">💰 Ringkasan Pembayaran</div>
        <div class="order-summary-grid">
            <div class="order-summary-row">
                <span>Subtotal (<?php echo $total_item; ?> item)</span>
                <strong><?php echo format_rupiah($pesanan['total_harga']); ?></strong>
            </div>
            <div class="order-summary-row">
                <span>Biaya Pengiriman</span>
                <strong>Rp 0</strong>
            </div>
            <div class="order-summary-total">
                <span>Total</span>
                <strong><?php echo format_rupiah($pesanan['grand_total']); ?></strong>
            </div>
        </div>
    </div>

    <div class="card order-status-card" style="margin-top: 20px;">
        <div class="card-header">⏱️ Status Pesanan</div>
        <?php 
        $status = $pesanan['status'];
        if ($status == 'menunggu_konfirmasi') {
            $statusTitle = 'Menunggu Konfirmasi';
            $statusDesc = 'Penjual akan mengkonfirmasi pesanan Anda.';
        } elseif ($status == 'dikonfirmasi') {
            $statusTitle = 'Pesanan Dikonfirmasi';
            $statusDesc = 'Penjual sedang menyiapkan barang.';
        } elseif ($status == 'dikirim') {
            $statusTitle = 'Pesanan Dikirim';
            $statusDesc = 'Barang dalam perjalanan ke Anda.';
        } elseif ($status == 'selesai') {
            $statusTitle = 'Pesanan Selesai';
            $statusDesc = 'Terima kasih telah berbelanja.';
        } elseif ($status == 'dibatalkan') {
            $statusTitle = 'Pesanan Dibatalkan';
            $statusDesc = 'Pesanan tidak dapat diproses lebih lanjut.';
        } else {
            $statusTitle = ucfirst(str_replace('_', ' ', $status));
            $statusDesc = '';
        }
        ?>
        <div class="status-item">
            <div class="status-icon"><span class="material-symbols-outlined">check_circle</span></div>
            <div class="status-content">
                <strong><?php echo $statusTitle; ?></strong>
                <span><?php echo $statusDesc; ?></span>
            </div>
        </div>
    </div>

    <div class="order-actions">
        <a href="pesanan.php" class="btn btn-secondary">← Kembali</a>
        <a href="javascript:void(0)" onclick="window.print()" class="btn btn-outline">Cetak Invoice</a>
        <a href="<?php echo htmlspecialchars($penjual_contact_url); ?>" class="btn btn-primary" <?php echo $penjual_contact_url === '#' ? '' : 'target="_blank" rel="noopener"'; ?>>Hubungi Penjual</a>
    </div>
</body>
</html>
