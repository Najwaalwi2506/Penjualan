<?php
include '../Koneksi.php';
check_login();
check_role(['admin']);

// Ambil produk baru dalam 24 jam terakhir (yang dibuat penjual)
$limit_hours = 24;
$now = date('Y-m-d H:i:s');

// MySQL: NOW() - INTERVAL n HOUR
$produk_baru = mysqli_query($koneksi, "
    SELECT pr.id,
           pr.toko_id,
           pr.jenis_produk_id,
           pr.harga_jual,
           pr.jumlah_stok,
           pr.is_tersedia,
           pr.created_at,
           jp.nama_jenis,
           k.nama as nama_kategori,
           t.nama_toko,
           u.nama as penjual_nama
    FROM produk pr
    JOIN toko t ON pr.toko_id = t.id
    JOIN users u ON t.user_id = u.id
    JOIN jenis_produk jp ON pr.jenis_produk_id = jp.id
    JOIN kategori_produk k ON jp.kategori_id = k.id
    WHERE pr.created_at >= (NOW() - INTERVAL {$limit_hours} HOUR)
    ORDER BY pr.created_at DESC
    LIMIT 8
");
?>
<div class="card" style="padding: 16px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap;">
        <div>
            <div style="font-weight:800;">Produk masuk (24 jam terakhir)</div>
            <div style="font-size:12px; color:#888; margin-top:4px;">Admin bisa memantau produk yang baru saja ditambahkan penjual.</div>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <?php while ($row = mysqli_fetch_assoc($produk_baru)) { ?>
                <div class="badge" style="background:#f5f7ff; color:#333; border:1px solid #ddd; padding:8px 10px; border-radius:10px; max-width: 260px;">
                    <div style="font-weight:700;"><?php echo htmlspecialchars($row['nama_jenis']); ?></div>

                    <div style="font-size:12px; color:#666;">Kategori: <?php echo htmlspecialchars($row['nama_kategori']); ?></div>
                    <div style="font-size:12px; color:#666;">Toko: <?php echo htmlspecialchars($row['nama_toko']); ?></div>
                    <div style="font-size:12px; color:#666;">
                        Stok: <?php echo htmlspecialchars($row['jumlah_stok']); ?> • <?php echo format_rupiah($row['harga_jual']); ?>
                    </div>
                    <div style="font-size:12px; color:#666;">Penjual: <?php echo htmlspecialchars($row['penjual_nama']); ?></div>
                </div>
            <?php } ?>
            <?php if (mysqli_num_rows($produk_baru) === 0) { ?>
                <div style="font-size:12px; color:#999;">Belum ada produk baru.</div>
            <?php } ?>
        </div>
    </div>
</div>

