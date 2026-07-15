<?php
include '../Koneksi.php';
check_login();
check_role(['pembeli']);
ensure_toko_rekening_columns($koneksi);

$user_id = $_SESSION['user_id'];

// Ambil data keranjang
$selected_ids = array();
if (!empty($_GET['selected_ids']) && is_array($_GET['selected_ids'])) {
    $selected_ids = array_map('intval', $_GET['selected_ids']);
    $selected_ids = array_filter($selected_ids);
}
$selected_filter = '';
if (!empty($selected_ids)) {
    $selected_filter = ' AND k.id IN (' . implode(',', $selected_ids) . ')';
}

$keranjang = mysqli_query($koneksi, "
    SELECT k.*, p.harga_jual, j.nama_jenis, t.id as toko_id, t.nama_toko,
           t.bank_nama, t.no_rekening, t.nama_rekening
    FROM keranjang k
    JOIN produk p ON k.produk_id = p.id
    JOIN jenis_produk j ON p.jenis_produk_id = j.id
    JOIN toko t ON p.toko_id = t.id
    WHERE k.user_id = $user_id $selected_filter
    ORDER BY t.id
");

$items = array();
$total_harga = 0;
$toko_list = array();
$toko_rekening = array();

while ($row = mysqli_fetch_assoc($keranjang)) {
    $items[] = $row;
    $subtotal = $row['harga_jual'] * $row['jumlah'];
    $total_harga += $subtotal;
    
    if (!in_array($row['toko_id'], $toko_list)) {
        $toko_list[] = $row['toko_id'];
        $toko_rekening[$row['toko_id']] = array(
            'nama_toko' => $row['nama_toko'],
            'bank_nama' => $row['bank_nama'],
            'no_rekening' => $row['no_rekening'],
            'nama_rekening' => $row['nama_rekening']
        );
    }
}

$total_items = count($items);
$total_toko = count($toko_list);

// Ambil data user
$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id = $user_id"));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja</title>
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
        <a href="keranjang.php" class="cart-badge"><span class="material-symbols-outlined">shopping_cart</span> Keranjang<span></a>
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

<div class="main-content" style="max-width: 900px; margin: 0 auto;">
    <h1 class="page-title">🛒 Konfirmasi Pesanan</h1>
    
    <?php if ($total_items > 0) { ?>
    
    <div class="checkout-layout" style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <!-- ITEM PESANAN -->
        <div>
            <div class="card">
                <div class="card-header">📦 Item Pesanan (<?php echo count($toko_list); ?> Penjual, <?php echo $total_items; ?> Produk)</div>
                
                <?php 
                $current_toko = '';
                foreach ($items as $item) { 
                    if ($current_toko != $item['toko_id']) {
                        if ($current_toko != '') echo '</div>';
                        $current_toko = $item['toko_id'];
                        echo '<div style="border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 15px;">';
                        echo '<div style="font-weight: bold; color: #667eea; margin-bottom: 10px;">👤 ' . $item['nama_toko'] . '</div>';
                    }
                    
                    $subtotal = $item['harga_jual'] * $item['jumlah'];
                ?>
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                        <div style="flex: 1;">
                            <strong><?php echo $item['nama_jenis']; ?></strong><br>
                            <small>Jumlah <?php echo $item['jumlah']; ?> <?php echo htmlspecialchars(strtoupper($item['satuan'] ?? 'unit')); ?> x <?php echo format_rupiah($item['harga_jual']); ?></small>
                        </div>
                        <div style="text-align: right; font-weight: bold;">
                            <?php echo format_rupiah($subtotal); ?>
                        </div>
                    </div>
                <?php 
                }
                echo '</div>';
                ?>
            </div>
            
            <!-- BANK DETAILS & BUKTI PEMBAYARAN -->
            <div class="card" style="margin-top: 20px; background: #f0f7ff; border-left: 4px solid #4facfe;">
                <div class="card-header" style="background: #4facfe; color: white;">💳 Informasi Pembayaran</div>
                <div style="padding: 20px;">
                    <strong style="display: block; margin-bottom: 10px;">🏦 Silakan transfer ke rekening penjual:</strong>
                    <?php foreach ($toko_rekening as $rekening_toko_id => $rek) { ?>
                        <div style="background: white; padding: 15px; border-radius: 5px; margin-bottom: 15px;">
                            <div style="font-weight: bold; color: #667eea; margin-bottom: 10px;">
                                <?php echo htmlspecialchars($rek['nama_toko']); ?>
                            </div>
                            <?php if (!empty($rek['bank_nama']) && !empty($rek['no_rekening']) && !empty($rek['nama_rekening'])) { ?>
                                <div style="margin-bottom: 10px;">
                                    <small style="color: #999;">Bank</small><br>
                                    <strong><?php echo htmlspecialchars($rek['bank_nama']); ?></strong>
                                </div>
                                <div style="margin-bottom: 10px;">
                                    <small style="color: #999;">No Rekening</small><br>
                                    <strong style="font-size: 16px;"><?php echo htmlspecialchars($rek['no_rekening']); ?></strong>
                                </div>
                                <div>
                                    <small style="color: #999;">Atas Nama</small><br>
                                    <strong><?php echo htmlspecialchars($rek['nama_rekening']); ?></strong>
                                </div>
                            <?php } else { ?>
                                <div style="color: #b45309; background: #fff7ed; padding: 10px; border-radius: 5px;">
                                    Rekening toko ini belum diisi. Hubungi penjual sebelum transfer.
                                </div>
                            <?php } ?>
                            <div style="margin-top: 12px;">
                                <label style="display:block; margin-bottom:6px;">Bukti Pembayaran <?php echo htmlspecialchars($rek['nama_toko']); ?> *</label>
                                <input type="file" name="bukti_pembayaran_toko[<?php echo (int)$rekening_toko_id; ?>]" form="checkoutForm" accept=".jpg,.jpeg,.png,.pdf" required style="padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 100%;">
                                <small style="color:#666; display:block; margin-top:6px;">Format yang diperbolehkan: JPG, JPEG, PNG, PDF. Maksimal ukuran file 2 MB per upload.</small>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            
            <!-- ALAMAT PENGIRIMAN -->
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">📍 Alamat Pengiriman & Bukti Pembayaran</div>
                <div style="padding: 20px;">
                    <form id="checkoutForm" method="POST" action="proses/proses_checkout.php" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Nama Penerima</label>
                            <input type="text" name="nama_penerima" value="<?php echo $user['nama']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>No Telepon</label>
                            <input type="text" name="no_telp" value="<?php echo isset($user['no_telp']) ? $user['no_telp'] : ''; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Alamat Lengkap</label>
                            <textarea name="alamat" required><?php echo $user['alamat']; ?></textarea>
                        </div>
                        <div class="form-group">
                            <label>Catatan (Opsional)</label>
                            <textarea name="catatan" placeholder="Tulis catatan untuk penjual..."></textarea>
                        </div>
                        
                        <small style="color: #999; display: block; margin-bottom: 15px;">Upload bukti pembayaran dilakukan per toko pada bagian informasi pembayaran di atas.</small>
                        
                        <?php foreach ($selected_ids as $selected_id) : ?>
                            <input type="hidden" name="selected_ids[]" value="<?php echo $selected_id; ?>">
                        <?php endforeach; ?>
                        <input type="hidden" name="total_harga" value="<?php echo $total_harga; ?>">

                    </form>
                </div>
            </div>
        </div>
        
        <!-- RINGKASAN & PEMBAYARAN -->
        <div>
            <div class="card">
                <div class="card-header">💰 Ringkasan Pembayaran</div>
                <div style="padding: 20px;">
                    <div style=" justify-content: space-between; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                        <span>Subtotal (<?php echo $total_items; ?> item)</span>
                        <strong><?php echo format_rupiah($total_harga); ?></strong>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin: 20px 0 10px 0; font-size: 18px; font-weight: bold; padding: 15px 0; border-top: 2px solid #667eea; border-bottom: 2px solid #667eea;">
                        <span>Total</span>
                        <span id="totalDisplay"><?php echo format_rupiah($total_harga); ?></span>
                    </div>
                    
                    <div style="display: flex; gap: 10px; flex-direction: column;">

                        <button type="submit" form="checkoutForm" class="btn btn-primary btn-block">✓ Pesan Sekarang</button>
                        <a href="keranjang.php" class="btn btn-secondary btn-block">← Kembali</a>
                    </div>
                </div>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">⚠️ Catatan</div>
                <div style="padding: 15px; font-size: 13px; color: #666;">
                    <p>• Pesanan akan diproses setelah Anda melakukan pembayaran</p>
                    <p>• Penjual akan mengkonfirmasi pesanan Anda</p>
                    <p>• Anda akan menerima notifikasi status pesanan</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- checkoutForm already defined in the Alamat Pengiriman card -->
    
    <?php } else { ?>
    <div class="card text-center">
        <p style="color: #999; padding: 40px;">Keranjang Anda kosong</p>
        <a href="dashboard.php" class="btn btn-primary">Mulai Belanja</a>
    </div>
    <?php } ?>
</div>

<script>
function formatRupiah(nominal) {
    return nominal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
</script>

</body>
</html>
