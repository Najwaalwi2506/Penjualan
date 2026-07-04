<?php
include '../../Koneksi.php';
check_login();
check_role(['pembeli']);
ensure_toko_rekening_columns($koneksi);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../checkout.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$total_harga = isset($_POST['total_harga']) ? floatval(sanitize($koneksi, $_POST['total_harga'])) : 0;
$grand_total = $total_harga;


// Data alamat dan penerima
$nama_penerima = isset($_POST['nama_penerima']) ? sanitize($koneksi, $_POST['nama_penerima']) : '';
$no_telp = isset($_POST['no_telp']) ? sanitize($koneksi, $_POST['no_telp']) : '';
$alamat = isset($_POST['alamat']) ? sanitize($koneksi, $_POST['alamat']) : '';
$catatan = isset($_POST['catatan']) ? sanitize($koneksi, $_POST['catatan']) : null;

// Upload bukti pembayaran
$bukti_pembayaran = null;
if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] === UPLOAD_ERR_OK) {
    $bukti_pembayaran = upload_file('bukti_pembayaran', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
    if ($bukti_pembayaran === false) {
        die('Gagal upload bukti pembayaran. Format tidak didukung.');
    }
}

// Ambil item terpilih dari keranjang pembeli
$selected_ids = array();
if (!empty($_POST['selected_ids']) && is_array($_POST['selected_ids'])) {
    $selected_ids = array_map('intval', $_POST['selected_ids']);
    $selected_ids = array_filter($selected_ids);
}
$selected_filter = '';
if (!empty($selected_ids)) {
    $selected_filter = ' AND k.id IN (' . implode(',', $selected_ids) . ')';
}

$keranjang = mysqli_query($koneksi, "
    SELECT k.*, p.harga_jual, t.id as toko_id, j.nama_jenis, j.satuan
    FROM keranjang k
    JOIN produk p ON k.produk_id = p.id
    JOIN jenis_produk j ON p.jenis_produk_id = j.id
    JOIN toko t ON p.toko_id = t.id
    WHERE k.user_id = $user_id $selected_filter
");

if (mysqli_num_rows($keranjang) == 0) {
    die('Keranjang kosong');
}

// Group by toko
$toko_pesanan = array();
while ($row = mysqli_fetch_assoc($keranjang)) {
    $toko_id = $row['toko_id'];
    if (!isset($toko_pesanan[$toko_id])) {
        $toko_pesanan[$toko_id] = array('items' => array(), 'total' => 0);
    }
    $toko_pesanan[$toko_id]['items'][] = $row;
    $toko_pesanan[$toko_id]['total'] += $row['harga_jual'] * $row['jumlah'];
}


// Insert pesanan untuk setiap toko
$jumlah_toko = count($toko_pesanan);
$has_admin_approval = column_exists($koneksi, 'pesanan', 'admin_approval_status');

$bukti_column = column_exists($koneksi, 'pesanan', 'bukti_pembayaran') ? 'bukti_pembayaran' : (column_exists($koneksi, 'pesanan', 'bukti_bayar') ? 'bukti_bayar' : null);
$has_detail_snapshot = column_exists($koneksi, 'detail_pesanan', 'nama_produk') && column_exists($koneksi, 'detail_pesanan', 'satuan');

foreach ($toko_pesanan as $toko_id => $data) {
    $kode_pesanan = generate_kode_pesanan();
    $pesanan_total = $data['total'];
    $pesanan_grand_total = $pesanan_total;

    
    $bukti_pesanan = upload_file_array('bukti_pembayaran_toko', $toko_id, ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
    if ($bukti_pesanan === false) {
        die('Gagal upload bukti pembayaran untuk salah satu toko. Format tidak didukung.');
    }
    if ($bukti_pesanan === null) {
        $bukti_pesanan = $bukti_pembayaran;
    }

// Insert ke pesanan (simpan alamat pengiriman, catatan, dan bukti pembayaran)
    $catatan_sql = $catatan !== null ? "'" . $catatan . "'" : "NULL";
    $bukti_sql = $bukti_pesanan !== null ? "'" . $bukti_pesanan . "'" : "NULL";
    $columns = "pembeli_id, toko_id, kode_pesanan, total_harga, grand_total, alamat_kirim, catatan, status, created_at";
    $values = "$user_id, $toko_id, '$kode_pesanan', $pesanan_total, $pesanan_grand_total, '$alamat', $catatan_sql, 'menunggu_konfirmasi', NOW()";

    if ($bukti_column !== null) {
        $columns .= ", $bukti_column";
        $values .= ", $bukti_sql";
    }
    if ($has_admin_approval) {
        $columns .= ", admin_approval_status";
        $values .= ", 'approved'";
    }
    $insert_pesanan = "INSERT INTO pesanan ($columns) VALUES ($values)";
    
    if (!mysqli_query($koneksi, $insert_pesanan)) {
        die('Error: ' . mysqli_error($koneksi));
    }
    
    $pesanan_id = mysqli_insert_id($koneksi);
    
    // Insert detail pesanan
    foreach ($data['items'] as $item) {
        $produk_id = $item['produk_id'];
        $jumlah = $item['jumlah'];
        $harga = $item['harga_jual'];
        $subtotal = $jumlah * $harga;
        $nama_produk = sanitize($koneksi, $item['nama_jenis']);
        $satuan = sanitize($koneksi, $item['satuan']);
        
        if ($has_detail_snapshot) {
            $insert_detail = "
                INSERT INTO detail_pesanan (pesanan_id, produk_id, nama_produk, satuan, jumlah, harga_satuan, subtotal)
                VALUES ($pesanan_id, $produk_id, '$nama_produk', '$satuan', $jumlah, $harga, $subtotal)
            ";
        } else {
            $insert_detail = "
                INSERT INTO detail_pesanan (pesanan_id, produk_id, jumlah, harga_satuan, subtotal)
                VALUES ($pesanan_id, $produk_id, $jumlah, $harga, $subtotal)
            ";
        }
        
        if (!mysqli_query($koneksi, $insert_detail)) {
            die('Error: ' . mysqli_error($koneksi));
        }
        
        // Update stok produk
        mysqli_query($koneksi, "UPDATE produk SET jumlah_stok = jumlah_stok - $jumlah WHERE id = $produk_id");
    }
}

// Hapus item terpilih dari keranjang setelah checkout berhasil
if (!empty($selected_ids)) {
    mysqli_query($koneksi, "DELETE FROM keranjang WHERE user_id = $user_id AND id IN (" . implode(',', $selected_ids) . ")");
} else {
    mysqli_query($koneksi, "DELETE FROM keranjang WHERE user_id = $user_id");
}

$subject = 'Pesanan Baru Dibuat';
$message = '<p>Pembeli dengan ID ' . $user_id . ' telah membuat pesanan baru.</p>';
$message .= '<p>Jumlah toko: ' . count($toko_pesanan) . '</p>';
$message .= '<p>Total harga: ' . format_rupiah($grand_total) . '</p>';
send_notification_email($koneksi, $subject, $message);

// Redirect ke pesanan dengan success message
header('Location: ../pesanan.php?success=Pesanan berhasil dibuat');
?>
