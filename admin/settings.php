<?php
include '../Koneksi.php';
check_login();
check_role(['admin']);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notification_email = sanitize($koneksi, $_POST['notification_email'] ?? '');
    $mail_from_address = sanitize($koneksi, $_POST['mail_from_address'] ?? '');
    $mail_from_name = sanitize($koneksi, $_POST['mail_from_name'] ?? '');
    $mail_reply_to = sanitize($koneksi, $_POST['mail_reply_to'] ?? '');
    $smtp_host = sanitize($koneksi, $_POST['smtp_host'] ?? '');
    $smtp_port = sanitize($koneksi, $_POST['smtp_port'] ?? '');
    $smtp_user = sanitize($koneksi, $_POST['smtp_user'] ?? '');
    $smtp_pass = sanitize($koneksi, $_POST['smtp_pass'] ?? '');
    $smtp_secure = sanitize($koneksi, $_POST['smtp_secure'] ?? '');

    if ($notification_email === '') {
        $error = 'Alamat email notifikasi wajib diisi.';
    } elseif (!filter_var($notification_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Alamat email notifikasi tidak valid.';
    } else {
        set_app_setting($koneksi, 'notification_email', $notification_email);
        set_app_setting($koneksi, 'mail_from_address', $mail_from_address ?: $notification_email);
        set_app_setting($koneksi, 'mail_from_name', $mail_from_name ?: 'Sistem Penjualan Pupuk');
        set_app_setting($koneksi, 'mail_reply_to', $mail_reply_to ?: $notification_email);
        set_app_setting($koneksi, 'smtp_host', $smtp_host);
        set_app_setting($koneksi, 'smtp_port', $smtp_port);
        set_app_setting($koneksi, 'smtp_user', $smtp_user);
        set_app_setting($koneksi, 'smtp_pass', $smtp_pass);
        set_app_setting($koneksi, 'smtp_secure', $smtp_secure);
        $success = 'Pengaturan notifikasi berhasil disimpan.';
    }
}

$notification_email = get_app_setting($koneksi, 'notification_email', '');
$mail_from_address = get_app_setting($koneksi, 'mail_from_address', $notification_email);
$mail_from_name = get_app_setting($koneksi, 'mail_from_name', 'Sistem Penjualan Pupuk');
$mail_reply_to = get_app_setting($koneksi, 'mail_reply_to', $notification_email);
$smtp_host = get_app_setting($koneksi, 'smtp_host', '');
$smtp_port = get_app_setting($koneksi, 'smtp_port', '587');
$smtp_user = get_app_setting($koneksi, 'smtp_user', '');
$smtp_pass = get_app_setting($koneksi, 'smtp_pass', '');
$smtp_secure = get_app_setting($koneksi, 'smtp_secure', 'tls');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Notifikasi Email</title>
    <link rel="stylesheet" href="Style.css">
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <div class="sidebar-head">
            <h3><span class="material-symbols-outlined icon">settings</span> Pengaturan</h3>
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-title">Menu Utama</li>
            <li><a href="index.php"><span class="material-symbols-outlined icon">dashboard</span> Dashboard</a></li>
            <li><a href="users.php"><span class="material-symbols-outlined icon">people</span> Kelola User</a></li>
            <li><a href="kelola_toko.php"><span class="material-symbols-outlined icon">store</span> Kelola Toko</a></li>
            <li><a href="pesanan.php"><span class="material-symbols-outlined icon">inventory_2</span> Data Pesanan</a></li>
            <li><a href="laporan_penjualan.php"><span class="material-symbols-outlined icon">bar_chart</span> Laporan Penjualan</a></li>
            <li><a href="kelola_kategori.php"><span class="material-symbols-outlined icon">label</span> Kategori & Jenis Produk</a></li>
            <li class="sidebar-title">Pengaturan</li>
            <li><a href="settings.php" class="active"><span class="material-symbols-outlined icon">email</span> Notifikasi Email</a></li>
            <li class="sidebar-title">Akun</li>
            <li><a href="../auth/logout.php"><span class="material-symbols-outlined icon">logout</span> Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="navbar">
            <div class="navbar-brand"><span class="material-symbols-outlined">email</span> Notifikasi Email</div>
        </div>

        <h1 class="page-title">Pengaturan Notifikasi Email</h1>
        <p class="page-subtitle">Email ini akan menerima notifikasi dari sistem untuk semua peristiwa penting.</p>

        <?php if ($success) { ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php } ?>
        <?php if ($error) { ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="card">
            <div class="card-header">Alamat Email Notifikasi</div>
            <div style="padding: 20px;">
                <form method="POST">
                    <div class="form-group">
                        <label>Alamat Email Penerima Notifikasi *</label>
                        <input type="email" name="notification_email" value="<?php echo htmlspecialchars($notification_email); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat Email Dari (From)</label>
                        <input type="email" name="mail_from_address" value="<?php echo htmlspecialchars($mail_from_address); ?>" placeholder="noreply@example.com">
                    </div>
                    <div class="form-group">
                        <label>Nama Pengirim Email</label>
                        <input type="text" name="mail_from_name" value="<?php echo htmlspecialchars($mail_from_name); ?>" placeholder="Sistem Penjualan Pupuk">
                    </div>
                    <div class="form-group">
                        <label>Reply-To</label>
                        <input type="email" name="mail_reply_to" value="<?php echo htmlspecialchars($mail_reply_to); ?>" placeholder="reply@example.com">
                    </div>
                    <div class="form-group">
                        <label>SMTP Host</label>
                        <input type="text" name="smtp_host" value="<?php echo htmlspecialchars($smtp_host); ?>" placeholder="smtp.gmail.com">
                    </div>
                    <div class="form-group">
                        <label>SMTP Port</label>
                        <input type="text" name="smtp_port" value="<?php echo htmlspecialchars($smtp_port); ?>" placeholder="587">
                    </div>
                    <div class="form-group">
                        <label>SMTP Username</label>
                        <input type="text" name="smtp_user" value="<?php echo htmlspecialchars($smtp_user); ?>" placeholder="username@example.com">
                    </div>
                    <div class="form-group">
                        <label>SMTP Password</label>
                        <input type="password" name="smtp_pass" value="<?php echo htmlspecialchars($smtp_pass); ?>" placeholder="Password SMTP">
                    </div>
                    <div class="form-group">
                        <label>Enkripsi SMTP</label>
                        <select name="smtp_secure">
                            <option value="" <?php echo $smtp_secure === '' ? 'selected' : ''; ?>>Tidak ada</option>
                            <option value="tls" <?php echo $smtp_secure === 'tls' ? 'selected' : ''; ?>>TLS</option>
                            <option value="ssl" <?php echo $smtp_secure === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="../js/admin-responsive.js"></script>
</body>
</html>
