<?php
include '../Koneksi.php';
check_login();
check_role(['pembeli']);

$user_id = $_SESSION['user_id'];
$pesanan_id = sanitize($koneksi, $_GET['id']);

// Ambil data pesanan
$pesanan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT p.*, p.alamat_kirim AS alamat, t.nama_toko, u.nama as penjual
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
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<!-- NAVBAR -->
<div class="navbar">
    <div class="navbar-brand">📋 Detail Pesanan</div>
    <div class="navbar-right">
        <div class="navbar-links">
            <a href="pesanan.php">← Riwayat Pesanan</a>
        </div>
    </div>
</div>

<div class="main-content" style="max-width: 900px; margin: 0 auto;">
    <h1 class="page-title">📋 Detail Pesanan <?php echo $pesanan['kode_pesanan']; ?></h1>
    <p class="page-subtitle">Relasi ONE-TO-MANY: 1 Pesanan memiliki MANY Detail Item</p>
    
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <!-- INFO PESANAN & ITEM -->
        <div>
            <!-- INFO PESANAN -->
            <div class="card">
                <div class="card-header">📦 Informasi Pesanan</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; padding: 20px;">
                    <div>
                        <small style="color: #999;">No Pesanan</small><br>
                        <strong><?php echo $pesanan['kode_pesanan']; ?></strong>
                    </div>
                    <div>
                        <small style="color: #999;">Toko</small><br>
                        <strong><?php echo $pesanan['nama_toko']; ?></strong>
                    </div>
                    <div>
                        <small style="color: #999;">Penjual</small><br>
                        <strong><?php echo $pesanan['penjual']; ?></strong>
                    </div>
                    <div>
                        <small style="color: #999;">Status</small><br>
                        <strong><span class="badge badge-<?php echo $pesanan['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $pesanan['status'])); ?></span></strong>
                    </div>
                    <div>
                        <small style="color: #999;">Tanggal Pesan</small><br>
                        <strong><?php echo date('d/m/Y H:i', strtotime($pesanan['created_at'])); ?></strong>
                    </div>
                </div>
            </div>
            
            <!-- ITEM PESANAN -->
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">📦 Item Pesanan</div>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Qty</th>
                                <th>Harga/Satuan</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_item = 0;
                            while ($row = mysqli_fetch_assoc($detail)) { 
                                $total_item++;
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo $row['nama_jenis']; ?></strong><br>
                                    <small><?php echo $row['satuan']; ?></small>
                                </td>
                                <td><?php echo $row['jumlah']; ?></td>
                                <td><?php echo format_rupiah($row['harga_satuan']); ?></td>
                                <td><?php echo format_rupiah($row['subtotal']); ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- RINGKASAN PEMBAYARAN -->
        <div>
            <div class="card">
                <div class="card-header">💰 Ringkasan Pembayaran</div>
                <div style="padding: 20px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #ddd;">
                        <span>Subtotal (<?php echo $total_item; ?> item)</span>
                        <strong><?php echo format_rupiah($pesanan['total_harga']); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin: 15px 0; font-size: 18px; font-weight: bold; padding: 15px 0; border-top: 2px solid #667eea; border-bottom: 2px solid #667eea;">
                        <span>Total</span>
                        <span><?php echo format_rupiah($pesanan['grand_total']); ?></span>
                    </div>
                    
                    <div style="background: #f5f7fa; padding: 15px; border-radius: 5px; margin-top: 20px;">
                        <div style="font-weight: bold; margin-bottom: 10px; color: #667eea;">⏱️ Status Pesanan</div>
                        <div style="font-size: 13px; line-height: 1.8; color: #666;">
                            <?php 
                            $status = $pesanan['status'];
                            if ($status == 'menunggu_konfirmasi') {
                                echo '⏳ Menunggu konfirmasi penjual...<br>';
                                echo '<small style="color: #999;">Penjual akan mengkonfirmasi pesanan Anda</small>';
                            } elseif ($status == 'dikonfirmasi') {
                                echo '✓ Pesanan dikonfirmasi<br>';
                                echo '<small style="color: #999;">Penjual sedang menyiapkan barang</small>';
                            } elseif ($status == 'dikirim') {
                                echo '📦 Pesanan sudah dikirim<br>';
                                echo '<small style="color: #999;">Barang dalam perjalanan ke Anda</small>';
                            } elseif ($status == 'selesai') {
                                echo '✓ Pesanan selesai<br>';
                                echo '<small style="color: #999;">Terima kasih telah berbelanja</small>';
                            } elseif ($status == 'dibatalkan') {
                                echo '✗ Pesanan dibatalkan';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">📍 Alamat Pengiriman</div>
                <div style="padding: 20px; font-size: 13px; color: #666; line-height: 1.8;">
                    <strong><?php echo $pesanan['alamat']; ?></strong><br>
                    <br>
                    <?php echo $pesanan['catatan']; ?>
                </div>
            </div>
            
            <?php if ($pesanan['status'] == 'dikirim') { ?>
            <form method="POST" action="proses/konfirmasi_terima.php" style="margin-top: 20px;">
                <input type="hidden" name="pesanan_id" value="<?php echo (int)$pesanan['id']; ?>">
                <button type="submit" class="btn btn-success btn-block" onclick="return confirm('Konfirmasi barang sudah diterima?')">✓ Barang Sudah Diterima</button>
            </form>
            <?php } ?>
            <a href="pesanan.php" class="btn btn-secondary btn-block" style="margin-top: 20px;">← Kembali</a>
        </div>
    </div>
</div>
</body>
</html>
