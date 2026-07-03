<?php
include '../Koneksi.php';
check_login();
check_role(['pembeli']);

$user_id = $_SESSION['user_id'];

// Ambil pesanan pembeli (ONE-TO-MANY: 1 Pembeli punya MANY Pesanan)
$pesanan = mysqli_query($koneksi, "
    SELECT p.*, t.nama_toko, u.nama as penjual 
    FROM pesanan p
    JOIN toko t ON p.toko_id = t.id
    JOIN users u ON t.user_id = u.id
    WHERE p.pembeli_id = $user_id
    ORDER BY p.created_at DESC
");

$total_pesanan = mysqli_num_rows($pesanan);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<!-- NAVBAR -->
<div class="navbar">
    <div class="navbar-brand">📋 Riwayat Pesanan Saya</div>
    <div class="navbar-right">
        <div class="navbar-links">
            <a href="dashboard.php">Belanja Lagi</a>
            <a href="../auth/logout.php">Logout</a>
        </div>
    </div>
</div>

<div class="main-content" style="max-width: 1000px; margin: 0 auto;">
    <h1 class="page-title">📋 Riwayat Pesanan Anda</h1>
    <p class="page-subtitle">Relasi ONE-TO-MANY: 1 Pembeli memiliki MANY Pesanan</p>
    
    <?php if ($total_pesanan > 0) { ?>
    
    <div class="card">
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>No Pesanan</th>
                        <th>Penjual</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($pesanan)) { ?>
                    <tr>
                        <td><strong><?php echo $row['kode_pesanan']; ?></strong></td>
                        <td><?php echo $row['nama_toko']; ?></td>
                        <td><?php echo format_rupiah($row['grand_total']); ?></td>
                        <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?></span></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                        <td><a href="pesanan_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Lihat</a></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php } else { ?>
    <div class="card text-center">
        <p style="color: #999; padding: 40px;">Anda belum melakukan pembelian</p>
        <a href="dashboard.php" class="btn btn-primary">Mulai Belanja</a>
    </div>
    <?php } ?>
</div>
</body>
</html>
