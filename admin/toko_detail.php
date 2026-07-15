<?php
include '../Koneksi.php';
check_login();
check_role(['admin']);
ensure_toko_rekening_columns($koneksi);

$toko_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($toko_id <= 0) {
    header('Location: kelola_toko.php');
    exit;
}

// Ambil data toko + penjual (termasuk alamat & no telp bila ada)
$toko = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT t.*, 
           u.nama AS penjual_nama,
           u.email AS penjual_email,
           u.alamat AS penjual_alamat,
           u.no_telp AS penjual_no_telp,
           u.angkatan AS penjual_angkatan
    FROM toko t
    JOIN users u ON t.user_id = u.id
    WHERE t.id = {$toko_id}
    LIMIT 1
"));

if (!$toko) {
    die('Toko tidak ditemukan');
}

// Hitung statistik penjualan
$stats = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT 
        COUNT(*) AS total_pesanan,
        SUM(CASE WHEN p.status = 'dikonfirmasi' THEN 1 ELSE 0 END) AS total_dikonfirmasi,
        SUM(CASE WHEN p.status = 'selesai' OR p.status = 'completed' OR p.status = 'dibayar' THEN 1 ELSE 0 END) AS total_selesai,
        COALESCE(SUM(p.grand_total),0) AS total_omzet
    FROM pesanan p
    WHERE p.toko_id = {$toko_id}
"));

$stats = $stats ?: ['total_pesanan'=>0,'total_selesai'=>0,'total_dikonfirmasi'=>0,'total_omzet'=>0];

// Ambil produk-produk milik toko beserta jumlah terjual
$produk = mysqli_query($koneksi, "
    SELECT pr.*, 
           jp.nama_jenis,
           jp.satuan,
           (
               SELECT COALESCE(SUM(dp.jumlah),0)
               FROM detail_pesanan dp
               WHERE dp.produk_id = pr.id
           ) AS terjual_jumlah
    FROM produk pr
    JOIN jenis_produk jp ON pr.jenis_produk_id = jp.id
    WHERE pr.toko_id = {$toko_id}
    ORDER BY pr.created_at DESC
");

// Ambil daftar pesanan untuk toko tersebut
$pesanan = mysqli_query($koneksi, "
    SELECT p.*, 
           u.nama AS pembeli_nama,
           u.email AS pembeli_email
    FROM pesanan p
    JOIN users u ON p.pembeli_id = u.id
    WHERE p.toko_id = {$toko_id}
    ORDER BY p.created_at DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Toko - Admin</title>
    <link rel="stylesheet" href="../admin/Style.css">
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <div style="padding: 20px; border-bottom: 1px solid #444;">
            <h3 style="color: #16a34a; font-size: 18px;">Admin Panel</h3>
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-title">Menu Utama</li>
            <li><a href="index.php"><span class="material-symbols-outlined icon">dashboard</span> Dashboard</a></li>
            <li><a href="users.php"><span class="material-symbols-outlined icon">people</span> Kelola User</a></li>
            <li><a href="kelola_toko.php" class="active"><span class="material-symbols-outlined icon">store</span> Kelola Toko</a></li>
            <li><a href="kelola_pesanan.php"><span class="material-symbols-outlined icon">inventory_2</span> Kelola Pesanan</a></li>
            <li><a href="laporan_penjualan.php"><span class="material-symbols-outlined icon">bar_chart</span> Laporan Penjualan</a></li>
            <li><a href="kelola_kategori.php"><span class="material-symbols-outlined icon">label</span> Kategori & Jenis Produk</a></li>
            <li class="sidebar-title">Akun</li>
            <li><a href="../auth/logout.php"><span class="material-symbols-outlined icon">logout</span> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="navbar">
            <div class="navbar-brand">Detail Toko</div>
            <div class="navbar-right">
                <div class="navbar-user">
                    <div class="avatar">
                        <?php if (!empty($_SESSION['avatar']) && file_exists(__DIR__ . '/../uploads/' . $_SESSION['avatar'])): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($_SESSION['avatar']); ?>" alt="avatar">
                        <?php else: ?>
                            <span class="avatar-initials"><?php echo strtoupper(substr(trim($_SESSION['nama'] ?? 'A'),0,1)); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></div>
                        <div class="user-role"><span class="badge <?php echo 'badge-' . (strtolower($_SESSION['role'] ?? 'admin')); ?>"><?php echo htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Admin')); ?></span></div>
                    </div>
                    <div class="navbar-links">
                        <a href="kelola_toko.php" class="btn btn-sm">Kembali</a>
                        <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <h1 class="page-title">Detail Toko: <?php echo htmlspecialchars($toko['nama_toko'] ?? ''); ?></h1>
        <p class="page-subtitle">
            Penjual: <?php echo htmlspecialchars($toko['penjual_nama'] ?? ''); ?> • <?php echo htmlspecialchars($toko['penjual_email'] ?? ''); ?>
            <?php if (!empty($toko['penjual_alamat'])) { ?>
                <br>Alamat: <?php echo htmlspecialchars($toko['penjual_alamat']); ?>
            <?php } ?>
            <?php if (!empty($toko['penjual_no_telp'])) { ?>
                <br>No Telp: <?php echo htmlspecialchars($toko['penjual_no_telp']); ?>
            <?php } ?>
        </p>


        <div class="card" style="margin-bottom: 16px;">
            <div class="card-header">Informasi Toko</div>
            <div class="grid stats-grid-mobile" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px;">
                <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:14px; border-radius:14px;">
                    <div style="font-weight:800; color:#0f172a; margin-bottom:8px;">Statistik</div>
                    <div style="font-size:13px; color:#475569; line-height:1.6;">Total Pesanan: <strong><?php echo (int)$stats['total_pesanan']; ?></strong></div>
                    <div style="font-size:13px; color:#475569; line-height:1.6;">Dikonfirmasi Penjual: <strong><?php echo (int)$stats['total_dikonfirmasi']; ?></strong></div>
                    <div style="font-size:13px; color:#475569; line-height:1.6;">Selesai: <strong><?php echo (int)$stats['total_selesai']; ?></strong></div>
                    <div style="font-size:13px; color:#475569; line-height:1.6;">Omzet: <strong><?php echo format_rupiah($stats['total_omzet'] ?? 0); ?></strong></div>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:14px; border-radius:14px;">
                    <div style="font-weight:800; color:#0f172a; margin-bottom:8px;">Info Toko</div>
                    <div style="font-size:13px; color:#475569; line-height:1.6;">Status: <strong><?php echo !empty($toko['is_active']) ? '✓ Aktif' : '✗ Nonaktif'; ?></strong></div>
                    <div style="font-size:13px; color:#475569; line-height:1.6;">Alamat: <strong><?php echo htmlspecialchars($toko['alamat'] ?? '-'); ?></strong></div>
                    <div style="font-size:13px; color:#475569; line-height:1.6;">No Telp: <strong><?php echo htmlspecialchars($toko['no_telp'] ?? ($toko['telp'] ?? '-')); ?></strong></div>
                    <div style="font-size:13px; color:#475569; line-height:1.6;">Dibuat: <strong><?php echo !empty($toko['created_at']) ? date('d/m/Y', strtotime($toko['created_at'])) : '-'; ?></strong></div>
                </div>

                <div style="background:#f8fafc; border:1px solid #e2e8f0; padding:14px; border-radius:14px;">
                    <div style="font-weight:800; color:#0f172a; margin-bottom:8px;">Rekening Toko</div>
                    <div style="font-size:13px; color:#475569; line-height:1.6;">Bank: <strong><?php echo htmlspecialchars($toko['bank_nama'] ?? '-'); ?></strong></div>
                    <div style="font-size:13px; color:#475569; line-height:1.6;">No Rekening: <strong><?php echo htmlspecialchars($toko['no_rekening'] ?? '-'); ?></strong></div>
                    <div style="font-size:13px; color:#475569; line-height:1.6;">Atas Nama: <strong><?php echo htmlspecialchars($toko['nama_rekening'] ?? '-'); ?></strong></div>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 16px;">
            <div class="card-header">Produk Toko</div>
            <div class="table-wrapper">
                <table class="table mobile-table">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Jenis</th>
                            <th>Harga Jual</th>
                            <th>Stok</th>
                            <th>Terjual</th>
                            <th>Tersedia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($produk) === 0) { ?>
                            <tr><td colspan="6" style="text-align:center;color:#999;padding:20px;">Produk tidak ditemukan.</td></tr>
                        <?php } else { while ($p = mysqli_fetch_assoc($produk)) { ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($p['nama_produk'] ?? $p['nama'] ?? 'Produk'); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['nama_jenis'] ?? '-'); ?></td>
                                <td><?php echo format_rupiah($p['harga_jual'] ?? 0); ?></td>
                                <td><?php echo (int)($p['jumlah_stok'] ?? 0); ?></td>
                                <td><?php echo (int)($p['terjual_jumlah'] ?? 0); ?></td>
                                <td><?php echo !empty($p['is_tersedia']) ? '✓ Tersedia' : '✗ Tidak' ?></td>
                            </tr>
                        <?php } } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">🧾 Pesanan untuk Toko Ini</div>
            <div class="table-wrapper">
                <table class="table mobile-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Pembeli</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($pesanan) === 0) { ?>
                            <tr><td colspan="4" style="text-align:center;color:#999;padding:20px;">Belum ada pesanan.</td></tr>
                        <?php } else { while ($ps = mysqli_fetch_assoc($pesanan)) { ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($ps['kode_pesanan'] ?? $ps['id']); ?></strong></td>
                                <td><?php echo htmlspecialchars($ps['pembeli_nama'] ?? ''); ?><br><small style="color:#999;">(<?php echo htmlspecialchars($ps['pembeli_email'] ?? ''); ?>)</small></td>
                                <td><?php echo format_rupiah($ps['grand_total'] ?? 0); ?></td>
                                <td><?php echo htmlspecialchars($ps['status'] ?? '-'); ?></td>
                            </tr>
                        <?php } } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
<script src="../js/admin-responsive.js"></script>
</body>
</html>
