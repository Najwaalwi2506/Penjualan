<?php
include '../Koneksi.php';
check_login();
check_role(['pembeli']);

$user_id = $_SESSION['user_id'];

// Ambil keranjang
$keranjang = mysqli_query($koneksi, "
    SELECT k.*, p.harga_jual, j.nama_jenis, j.satuan, t.nama_toko, t.id as toko_id
    FROM keranjang k
    JOIN produk p ON k.produk_id = p.id
    JOIN jenis_produk j ON p.jenis_produk_id = j.id
    JOIN toko t ON p.toko_id = t.id
    WHERE k.user_id = $user_id
    ORDER BY t.id, k.added_at DESC
");

$total_items = mysqli_num_rows($keranjang);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<!-- NAVBAR -->
<div class="navbar">
    <div class="navbar-brand">🛒 Keranjang Belanja</div>
    <div class="navbar-right">
        <div class="navbar-links">
            <a href="dashboard.php">Belanja Lagi</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>
</div>

<div class="main-content" style="max-width: 900px; margin: 0 auto;">
    <h1 class="page-title">🛒 Keranjang Belanja Anda</h1>
    
    <?php if (isset($_GET['success'])) { ?>
    <div class="alert alert-success">✓ Produk berhasil ditambahkan ke keranjang</div>
    <?php } ?>
    
    <?php if ($total_items > 0) { ?>
    
    <div class="alert alert-info" style="margin-bottom: 15px;">Pesanan akan dipisah per penjual saat checkout, sehingga setiap penjual menerima ordernya sendiri.</div>
    <div class="card">
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Penjual</th>
                        <th>Harga</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_harga = 0;
                    while ($item = mysqli_fetch_assoc($keranjang)) { 
                        $subtotal = $item['harga_jual'] * $item['jumlah'];
                        $total_harga += $subtotal;
                    ?>
                    <tr>
                        <td><?php echo $item['nama_jenis']; ?></td>
                        <td><?php echo $item['nama_toko']; ?></td>
                        <td><?php echo format_rupiah($item['harga_jual']); ?>/<?php echo $item['satuan']; ?></td>
                        <td>
                            <form method="POST" action="keranjang_update.php" style="display: flex; gap: 5px;">
                                <input type="hidden" name="keranjang_id" value="<?php echo $item['id']; ?>">
                                <input type="number" name="jumlah" value="<?php echo $item['jumlah']; ?>" min="1" style="width: 60px; padding: 5px;">
                                <button type="submit" class="btn btn-secondary" style="padding: 5px 10px;">Update</button>
                            </form>
                        </td>
                        <td><?php echo format_rupiah($subtotal); ?></td>
                        <td>
                            <a href="keranjang_hapus.php?id=<?php echo $item['id']; ?>" class="btn btn-danger" style="padding: 5px 10px;" onclick="return confirm('Hapus item ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- RINGKASAN PESANAN -->
    <div class="card" style="margin-top: 20px;">
        <div class="card-header">Ringkasan Pesanan</div>
        <div style="padding: 20px;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <strong>Subtotal</strong>
                <strong><?php echo format_rupiah($total_harga); ?></strong>
            </div>
            <div style="display: flex; justify-content: space-between; margin: 20px 0; font-size: 18px;">
                <strong>Total</strong>
                <strong><?php echo format_rupiah($total_harga); ?></strong>
            </div>

            <a href="checkout.php" class="btn btn-primary btn-block">Lanjut ke Checkout</a>
            <a href="dashboard.php" class="btn btn-secondary btn-block" style="margin-top: 10px;">Belanja Lagi</a>
        </div>
    </div>
    
    <?php } else { ?>
    <div class="card text-center">
        <p style="color: #999; padding: 40px;">Keranjang Anda kosong</p>
        <a href="dashboard.php" class="btn btn-primary">Mulai Belanja</a>
    </div>
    <?php } ?>
</div>
</body>
</html>
