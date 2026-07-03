
<?php
include '../Koneksi.php';
check_login();
check_role(['admin']);

$pesanan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($pesanan_id <= 0) {
    die('ID pesanan tidak valid');
}

$pesanan = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT p.*,
           u.nama AS pembeli,
           u.email AS email_pembeli,
           u.no_telp,
           u.alamat,
           t.nama_toko,
           s.nama AS penjual
    FROM pesanan p
    JOIN users u ON p.pembeli_id = u.id
    JOIN toko t ON p.toko_id = t.id
    JOIN users s ON t.user_id = s.id
    WHERE p.id = {$pesanan_id}
"));

if (!$pesanan) {
    die('Pesanan tidak ditemukan');
}

$bukti_bayar = $pesanan['bukti_pembayaran'] ?? ($pesanan['bukti_bayar'] ?? '');

$detail = mysqli_query($koneksi, "
    SELECT
        dp.*,
        j.nama_jenis,
        j.satuan
    FROM detail_pesanan dp
    JOIN produk pr ON dp.produk_id = pr.id
    JOIN jenis_produk j ON pr.jenis_produk_id = j.id
    WHERE dp.pesanan_id = {$pesanan_id}
");

function badgeColor($status)
{
    switch (strtolower($status)) {
        case 'pending':
            return '#f39c12';
        case 'diproses':
            return '#3498db';
        case 'dikirim':
            return '#8e44ad';
        case 'selesai':
            return '#27ae60';
        case 'dibatalkan':
            return '#e74c3c';
        default:
            return '#6c757d';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Pesanan</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, sans-serif;
}

body{
    background:#f4f6f9;
}

.wrapper{
    padding:25px;
}

.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.page-title{
    font-size:28px;
    font-weight:bold;
    color:#333;
}

.btn-back{
    background:#16a34a;
    color:white;
    text-decoration:none;
    padding:10px 18px;
    border-radius:8px;
}

.btn-back:hover{
    opacity:.9;
}

.grid{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
}

.card{
    background:white;
    border-radius:12px;
    box-shadow:0 2px 12px rgba(0,0,0,.08);
    overflow:hidden;
}

.card-header{
    background:#16a34a;
    color:white;
    padding:15px 20px;
    font-weight:600;
}

.card-body{
    padding:20px;
}

.info-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:15px;
}

.info-item label{
    display:block;
    color:#888;
    font-size:13px;
    margin-bottom:5px;
}

.info-item span{
    font-weight:600;
    color:#333;
}

.table{
    width:100%;
    border-collapse:collapse;
}

.table th{
    background:#f8f9fa;
    text-align:left;
    padding:12px;
    border-bottom:1px solid #ddd;
}

.table td{
    padding:12px;
    border-bottom:1px solid #eee;
}

.table tbody tr:hover{
    background:#f8f9ff;
}

.summary-row{
    display:flex;
    justify-content:space-between;
    margin-bottom:12px;
}

.summary-total{
    display:flex;
    justify-content:space-between;
    margin-top:15px;
    padding-top:15px;
    border-top:2px solid #16a34a;
    font-size:18px;
    font-weight:bold;
}

.badge{
    display:inline-block;
    color:white;
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:bold;
}

.mt-20{
    margin-top:20px;
}

@media(max-width:768px){
    .grid{
        grid-template-columns:1fr;
    }

    .info-grid{
        grid-template-columns:1fr;
    }

    .page-header{
        flex-direction:column;
        gap:10px;
        align-items:flex-start;
    }
}
</style>
</head>
<body>

<div class="wrapper">

    <div class="page-header">
        <h1 class="page-title">
            Detail Pesanan
            #<?php echo htmlspecialchars($pesanan['kode_pesanan']); ?>
        </h1>

        <a href="pesanan.php" class="btn-back">
            ← Kembali
        </a>
    </div>

    <div class="grid">

        <!-- KIRI -->
        <div>

            <div class="card">
                <div class="card-header">
                    Informasi Pembeli
                </div>

                <div class="card-body">
                    <div class="info-grid">

                        <div class="info-item">
                            <label>Nama</label>
                            <span><?php echo htmlspecialchars($pesanan['pembeli']); ?></span>
                        </div>

                        <div class="info-item">
                            <label>Email</label>
                            <span><?php echo htmlspecialchars($pesanan['email_pembeli']); ?></span>
                        </div>

                        <div class="info-item">
                            <label>No Telepon</label>
                            <span><?php echo htmlspecialchars($pesanan['no_telp']); ?></span>
                        </div>

                        <div class="info-item">
                            <label>Alamat</label>
                            <span><?php echo htmlspecialchars($pesanan['alamat']); ?></span>
                        </div>

                    </div>
                </div>
            </div>

            <div class="card mt-20">
                <div class="card-header">
                    Item Pesanan
                </div>

                <div class="card-body">

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php
                        if(mysqli_num_rows($detail) > 0){

                            while($d = mysqli_fetch_assoc($detail)){
                        ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?php echo htmlspecialchars($d['nama_jenis']); ?>
                                    </strong>
                                    <br>
                                    <small>
                                        <?php echo htmlspecialchars($d['satuan']); ?>
                                    </small>
                                </td>

                                <td>
                                    <?php echo $d['jumlah']; ?>
                                </td>

                                <td>
                                    <?php echo format_rupiah($d['harga_satuan']); ?>
                                </td>

                                <td>
                                    <strong>
                                        <?php echo format_rupiah($d['subtotal']); ?>
                                    </strong>
                                </td>
                            </tr>
                        <?php
                            }

                        } else {
                        ?>
                            <tr>
                                <td colspan="4" style="text-align:center;">
                                    Tidak ada data item.
                                </td>
                            </tr>
                        <?php } ?>

                        </tbody>
                    </table>

                </div>
            </div>

        </div>

        <!-- KANAN -->
        <div>

            <div class="card">
                <div class="card-header">
                    Ringkasan Pesanan
                </div>

                <div class="card-body">

                    <div class="summary-row">
                        <span>Subtotal</span>
                        <strong>
                            <?php echo format_rupiah($pesanan['total_harga']); ?>
                        </strong>
                    </div>

                    <div class="summary-row">
                        <span>Ongkir</span>
                        <strong>
                            <?php echo format_rupiah($pesanan['ongkir']); ?>
                        </strong>
                    </div>

                    <div class="summary-total">
                        <span>Total Bayar</span>
                        <span>
                            <?php echo format_rupiah($pesanan['grand_total']); ?>
                        </span>
                    </div>

                    <hr style="margin:20px 0;">

                    <div class="info-item">
                        <label>Bukti Pembayaran</label>
                        <?php if (!empty($bukti_bayar)) { ?>
                            <a href="../uploads/<?php echo htmlspecialchars($bukti_bayar); ?>" target="_blank" class="btn-back" style="display:inline-block; margin-top:6px;">Lihat Bukti</a>
                        <?php } else { ?>
                            <span>Belum ada bukti pembayaran</span>
                        <?php } ?>
                    </div>

                    <hr style="margin:20px 0;">

                    <div class="info-item">
                        <label>Status Pesanan</label>
                        <span class="badge"
                              style="background:<?php echo badgeColor($pesanan['status']); ?>">
                            <?php echo ucfirst($pesanan['status']); ?>
                        </span>
                    </div>

                    <br>

                    <div class="info-item">
                        <label>Toko</label>
                        <span>
                            <?php echo htmlspecialchars($pesanan['nama_toko']); ?>
                        </span>
                    </div>

                    <br>

                    <div class="info-item">
                        <label>Penjual</label>
                        <span>
                            <?php echo htmlspecialchars($pesanan['penjual']); ?>
                        </span>
                    </div>

                </div>
            </div>

        </div>

    </div>

</div>

</body>
</html>
```
