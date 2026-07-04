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
$cart_count = !empty($_SESSION['cart']) && is_array($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
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
    <h1 class="page-title">🛒 Keranjang Belanja Anda</h1>
    
    <?php if (isset($_GET['success'])) { ?>
    <div class="alert alert-success">✓ Produk berhasil ditambahkan ke keranjang</div>
    <?php } ?>
    
    <?php if ($total_items > 0) { ?>
    
    <div class="alert alert-info" style="margin-bottom: 15px;">Centang item yang ingin dibeli sekarang. Item lain akan tetap berada di keranjang untuk checkout berikutnya.</div>
    <form id="cartSelectionForm" method="GET" action="checkout.php">
        <div class="card">
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select_all"></th>
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
                        $current_toko = '';
                        while ($item = mysqli_fetch_assoc($keranjang)) { 
                            if ($current_toko !== $item['toko_id']) {
                                $current_toko = $item['toko_id'];
                        ?>
                        <tr class="seller-header-row">
                            <td colspan="7" class="seller-header">
                                <label class="seller-select-label">
                                    <input type="checkbox" class="seller-select-checkbox" data-toko="<?php echo $item['toko_id']; ?>">
                                    Pilih semua <strong><?php echo htmlspecialchars($item['nama_toko']); ?></strong>
                                </label>
                            </td>
                        </tr>
                        <?php
                            }
                            $subtotal = $item['harga_jual'] * $item['jumlah'];
                            $total_harga += $subtotal;
                        ?>
                        <tr>
                            <td><input type="checkbox" class="item-checkbox seller-<?php echo $item['toko_id']; ?>" name="selected_ids[]" value="<?php echo $item['id']; ?>"></td>
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

        <div class="card" style="margin-top: 20px; padding: 20px;">
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <button type="submit" id="checkoutSelectedBtn" class="btn btn-primary btn-block" disabled>Checkout Terpilih</button>
                <a href="checkout.php" class="btn btn-secondary btn-block">Checkout Semua</a>
            </div>
        </div>
    </form>
    
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

            <a href="checkout.php" class="btn btn-secondary btn-block">Checkout Semua</a>
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

<script>
const selectAll = document.getElementById('select_all');
const itemCheckboxes = document.querySelectorAll('.item-checkbox');
const sellerCheckboxes = document.querySelectorAll('.seller-select-checkbox');
const checkoutSelectedBtn = document.getElementById('checkoutSelectedBtn');

function updateCheckoutButton() {
    const anyChecked = Array.from(itemCheckboxes).some(cb => cb.checked);
    checkoutSelectedBtn.disabled = !anyChecked;
}

selectAll?.addEventListener('change', function() {
    itemCheckboxes.forEach(cb => cb.checked = this.checked);
    sellerCheckboxes.forEach(cb => cb.checked = this.checked);
    updateCheckoutButton();
});

sellerCheckboxes.forEach(function(sellerCb) {
    sellerCb.addEventListener('change', function() {
        const toko = this.dataset.toko;
        document.querySelectorAll('.seller-' + toko).forEach(itemCb => itemCb.checked = this.checked);
        updateCheckoutButton();
    });
});

itemCheckboxes.forEach(function(itemCb) {
    itemCb.addEventListener('change', function() {
        const toko = Array.from(this.classList).find(c => c.startsWith('seller-'))?.split('-')[1];
        if (toko) {
            const sellerGroup = document.querySelectorAll('.seller-' + toko);
            const sellerHeader = document.querySelector('.seller-select-checkbox[data-toko="' + toko + '"]');
            if (sellerHeader) {
                sellerHeader.checked = Array.from(sellerGroup).every(cb => cb.checked);
            }
        }
        selectAll.checked = Array.from(itemCheckboxes).every(cb => cb.checked);
        updateCheckoutButton();
    });
});

updateCheckoutButton();
</script>
</body>
</html>
