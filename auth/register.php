<?php
include '../Koneksi.php';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = sanitize($koneksi, $_POST['nama']);
    $angkatan = sanitize($koneksi, $_POST['angkatan']);
    $jenis = sanitize($koneksi, $_POST['jenis_keanggotaan']);
    $alamat = sanitize($koneksi, $_POST['alamat']);
    $no_telp = sanitize($koneksi, $_POST['no_telp']);
    $email = sanitize($koneksi, $_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    // Validasi
    if (empty($nama) || empty($angkatan) || empty($jenis) || empty($email) || empty($no_telp) || empty($password) || empty($password_confirm)) {
        $error = 'Semua field wajib diisi dengan lengkap.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal terdiri dari 6 karakter.';
    } elseif ($password !== $password_confirm) {
        $error = 'Konfirmasi password tidak cocok.';
    } elseif (strlen($no_telp) < 10) {
        $error = 'Nomor telepon tidak valid.';
    } else {
        // Cek email
        $cek_email = mysqli_query($koneksi, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($cek_email) > 0) {
            $error = 'Email tersebut sudah digunakan. Silakan login atau gunakan email lain.';
        } else {
            // Insert user baru
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $role = ($jenis == 'penjual') ? 'penjual' : 'pembeli';
            
            $query = "INSERT INTO users (nama, angkatan, jenis_keanggotaan, alamat, no_telp, email, password, role, is_active) 
                      VALUES ('$nama', '$angkatan', '$jenis', '$alamat', '$no_telp', '$email', '$password_hash', '$role', 1)";
            
            if (mysqli_query($koneksi, $query)) {
                $success = true;
                $user_id = mysqli_insert_id($koneksi);
                // Jika penjual, buat toko otomatis
                if ($jenis == 'penjual') {
                    $nama_toko = "Penjual " . $nama;
                    $query_toko = "INSERT INTO toko (user_id, nama_toko, is_active) VALUES ($user_id, '$nama_toko', 1)";
                    mysqli_query($koneksi, $query_toko);
                }
                // Kirim notifikasi ke email admin jika sudah dikonfigurasi
                $subject = 'Pendaftaran Akun Baru di Sistem Pupuk';
                $message = '<p>Akun baru telah didaftarkan dengan rincian berikut:</p>' .
                           '<ul>' .
                           '<li>Nama: ' . htmlspecialchars($nama) . '</li>' .
                           '<li>Email: ' . htmlspecialchars($email) . '</li>' .
                           '<li>Role: ' . htmlspecialchars($role) . '</li>' .
                           '<li>Jenis keanggotaan: ' . htmlspecialchars($jenis) . '</li>' .
                           '</ul>';
                send_notification_email($koneksi, $subject, $message);
            } else {
                $error = 'Gagal membuat akun: ' . mysqli_error($koneksi);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Sistem Penjualan Pupuk</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f4fbf1 0%, #e7f3e2 100%);
            min-height: 100vh;
            padding: 24px;
            color: #264229;
        }
        .container {
            max-width: 780px;
            margin: 0 auto;
            background: linear-gradient(145deg, #ffffff 0%, #fbfef8 100%);
            padding: 36px;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(33, 67, 31, 0.12);
            border: 1px solid #dcebd8;
            position: relative;
            overflow: hidden;
        }
        .container::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(93, 143, 77, 0.08), transparent 35%);
            pointer-events: none;
        }
        .brand {
            text-align: center;
            margin-bottom: 20px;
        }
        .brand-icon {
            width: 58px;
            height: 58px;
            margin: 0 auto 12px;
            border-radius: 18px;
            background: linear-gradient(135deg, #dcefd3, #b7d7a8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 8px 18px rgba(93, 143, 77, 0.18);
        }
        h1 { color: #21431f; margin-bottom: 6px; text-align: center; }
        .subtitle { text-align: center; color: #6b7b6b; margin-bottom: 24px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 8px; color: #2f5a2b; font-weight: 600; }
        input, select, textarea {
            width: 100%;
            min-height: 46px;
            padding: 12px 14px;
            border: 1.5px solid #dcebd8;
            border-radius: 12px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
            background: #f6fbf3;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #5d8f4d;
            box-shadow: 0 0 0 3px rgba(93, 143, 77, 0.16);
            transform: translateY(-1px);
        }
        textarea { resize: vertical; min-height: 90px; }
        button {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #2f6b3f 0%, #5d8f4d 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 10px 20px rgba(47, 107, 63, 0.2);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(47, 107, 63, 0.25);
        }
        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        .alert-danger { background: #fceceb; color: #7d2e2e; border-color: #e8a0a0; }
        .alert-success { background: #edf7ea; color: #2f6b3f; border-color: #8eb56b; }
        .links { text-align: center; margin-top: 20px; color: #6b7b6b; }
        .links a { color: #2f6b3f; text-decoration: none; font-weight: 600; }
        .links a:hover { text-decoration: underline; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-card {
            background: linear-gradient(135deg, #f8fcf6 0%, #f2f9ec 100%);
            padding: 18px;
            border-radius: 18px;
            border: 1px solid #e8f2e3;
            margin-bottom: 16px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.7);
        }
        .form-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: linear-gradient(135deg, #eef8e8 0%, #f6fcef 100%);
            border: 1px solid #d9ebce;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 16px;
            color: #4a6548;
            font-size: 13px;
        }
        .form-note-icon {
            font-size: 16px;
            margin-top: 1px;
        }

        /* --- Toggle lihat/sembunyikan password --- */
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input {
            padding-right: 42px;
        }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #8eb56b;
            user-select: none;
            display: flex;
            align-items: center;
            line-height: 1;
        }
        .toggle-password:hover { color: #2f5a2b; }
        .toggle-password svg {
            width: 20px;
            height: 20px;
            display: block;
        }
        .toggle-password .icon-hide { display: none; }
        .toggle-password.is-visible .icon-show { display: none; }
        .toggle-password.is-visible .icon-hide { display: block; }

        /* Sembunyikan ikon mata bawaan Microsoft Edge, supaya tidak dobel dengan ikon kita */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }

        @media (max-width: 600px) {
            .form-row { grid-template-columns: 1fr; }
            .container { padding: 24px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="brand">
            <div class="brand-icon">🌱</div>
            <div>
                <h1>Daftar Akun Baru</h1>
                <p class="subtitle">Jika Anda ingin membeli produk, pilih akun Pembeli. Jika Anda ingin menjual produk, pilih akun Penjual.</p>
            </div>
        </div>
        
        <?php if ($error) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>
        
        <?php if ($success) { ?>
            <div class="alert alert-success">
                ✓ Akun berhasil dibuat! <a href="../index.php">Login di sini</a>
            </div>
        <?php } else { ?>
        
        <form method="POST">
            <div class="form-card">
            <div class="form-note">
                <span class="form-note-icon">🌿</span>
                <div>Isi data dengan lengkap agar akun Anda siap dipakai untuk belanja atau menjual produk organik dengan cepat. Untuk akun Pembeli, pilih role Pembeli. Untuk akun Penjual, pilih role Penjual.</div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="nama">Nama Lengkap *</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                
                <div class="form-group">
                    <label for="angkatan">Angkatan/Alumni *</label>
                    <input type="text" id="angkatan" name="angkatan" placeholder="2020, 2019, dll" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="no_telp">No Telepon *</label>
                <input type="text" id="no_telp" name="no_telp" placeholder="0812xxxxxxx" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" required>
                        <small style="color:#6b7b6b; display:block; margin-top:6px;">Password minimal terdiri dari 6 karakter.</small>
                        <span class="toggle-password" onclick="togglePassword('password', 'toggleIcon1')" id="toggleIcon1">
                            <svg class="icon-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="icon-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.6 18.6 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Konfirmasi Password *</label>
                    <div class="password-wrapper">
                        <input type="password" id="password_confirm" name="password_confirm" required>
                        <span class="toggle-password" onclick="togglePassword('password_confirm', 'toggleIcon2')" id="toggleIcon2">
                            <svg class="icon-show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="icon-hide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.6 18.6 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="jenis_keanggotaan">Jenis Keanggotaan *</label>
                <select id="jenis_keanggotaan" name="jenis_keanggotaan" required>
                    <option value="">-- Pilih --</option>
                    <option value="penjual">Penjual (Input & Jual Produk)</option>
                    <option value="bukan_penjual">Pembeli (Hanya Belanja)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="alamat">Alamat Umum (Kabupaten/Kota) *</label>
                <input type="text" id="alamat" name="alamat" placeholder="Contoh: Kabupaten Karanganyar" required>
                <small style="color:#6b7b6b; display:block; margin-top:6px;">Anda cukup mengisi kabupaten/kota saja, tidak perlu alamat lengkap.</small>
            </div>
            </div>
            
            <button type="submit">Daftar Akun</button>
        </form>
        
        <?php } ?>
        
        <div class="links">
            <p>Sudah punya akun? <a href="../index.php">Login di sini</a></p>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.add('is-visible');
            } else {
                input.type = 'password';
                icon.classList.remove('is-visible');
            }
        }
    </script>
</body>
</html>