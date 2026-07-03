<?php
include '../Koneksi.php';
check_login();
check_role(['penjual']);

$user_id = $_SESSION['user_id'];
$toko = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM toko WHERE user_id = $user_id"));
$toko_id = $toko['id'];

$pesanan_id = sanitize($koneksi, $_GET['id']);

// Ambil data pesanan
$pesanan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT p.*, u.nama as pembeli, u.alamat, u.no_telp
    FROM pesanan p
    JOIN users u ON p.pembeli_id = u.id
    WHERE p.id = $pesanan_id AND p.toko_id = $toko_id
"));

if (!$pesanan) {
    die('Pesanan tidak ditemukan');
}

$bukti_bayar = $pesanan['bukti_pembayaran'] ?? ($pesanan['bukti_bayar'] ?? '');

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
    <title>Detail Pesanan - Penjual</title>
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
            <li><a href="produk.php">📦 Produk Saya</a></li>
            <li><a href="pesanan.php" class="active">📋 Pesanan Masuk</a></li>
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
            <div class="navbar-brand">📋 Detail Pesanan</div>
            <div class="navbar-right">
                <div class="navbar-links">
                    <a href="pesanan.php">← Pesanan Masuk</a>
                </div>
            </div>
        </div>
        
        <h1 class="page-title">📋 Detail Pesanan <?php echo $pesanan['kode_pesanan']; ?></h1>
        <p class="page-subtitle">Relasi ONE-TO-MANY: 1 Pesanan memiliki MANY Detail Item</p>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <!-- INFO PESANAN & ITEM -->
            <div>
                <!-- INFO PEMBELI -->
                <div class="card">
                    <div class="card-header">👤 Informasi Pembeli</div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; padding: 20px;">
                        <div>
                            <small style="color: #999;">Nama Pembeli</small><br>
                            <strong><?php echo $pesanan['pembeli']; ?></strong>
                        </div>
                        <div>
                            <small style="color: #999;">No Telepon</small><br>
                            <strong><?php echo $pesanan['no_telp']; ?></strong>
                        </div>
                        <div style="grid-column: 1 / -1;">
                            <small style="color: #999;">Alamat Pengiriman</small><br>
                            <strong><?php echo $pesanan['alamat']; ?></strong>
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
            
            <!-- RINGKASAN PEMBAYARAN & ACTION -->
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
                        <div style="margin-top: 15px;">
                            <small style="color: #999;">Bukti Pembayaran</small><br>
                            <?php if (!empty($bukti_bayar)) { ?>
                                <a href="../uploads/<?php echo htmlspecialchars($bukti_bayar); ?>" target="_blank" class="btn btn-secondary btn-sm" style="margin-top: 6px;">Lihat Bukti</a>
                            <?php } else { ?>
                                <strong>Belum ada bukti pembayaran</strong>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                
                <div class="card" style="margin-top: 20px;">
                    <div class="card-header">⚙️ Aksi Pesanan</div>
                    <div style="padding: 20px; display: flex; flex-direction: column; gap: 10px;">
                        <?php if ($pesanan['status'] == 'menunggu_konfirmasi') { ?>
                        <form method="POST" action="proses/konfirmasi_pesanan.php">
                            <input type="hidden" name="pesanan_id" value="<?php echo $pesanan['id']; ?>">
                            <button type="submit" class="btn btn-success btn-block">✓ Konfirmasi Pesanan</button>
                        </form>
                        <form method="POST" action="proses/tolak_pesanan.php">
                            <input type="hidden" name="pesanan_id" value="<?php echo $pesanan['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('Yakin tolak pesanan ini?')">✗ Tolak Pesanan</button>
                        </form>
                        <?php } elseif ($pesanan['status'] == 'dikonfirmasi') { ?>
                        <form method="POST" action="proses/kirim_pesanan.php">
                            <input type="hidden" name="pesanan_id" value="<?php echo $pesanan['id']; ?>">
                            <button type="submit" class="btn btn-primary btn-block">📦 Tandai Dikirim</button>
                        </form>
                        <?php } elseif ($pesanan['status'] == 'dikirim') { ?>
                        <div class="alert alert-info">📦 Pesanan sudah dikirim, tunggu pembeli mengkonfirmasi penerimaan</div>
                        <?php } elseif ($pesanan['status'] == 'selesai') { ?>
                        <div class="alert alert-success">✓ Pesanan sudah selesai</div>
                        <?php } ?>
                    </div>
                </div>
                
                <a href="pesanan.php" class="btn btn-secondary btn-block" style="margin-top: 20px;">← Kembali</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
