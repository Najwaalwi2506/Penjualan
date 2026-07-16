<?php
include '../Koneksi.php';
check_login();
check_role(['admin']);

$module = isset($_GET['module']) ? strtolower(trim($_GET['module'])) : 'pesanan';
$format = isset($_GET['format']) ? strtolower(trim($_GET['format'])) : 'csv';
$selected_month = isset($_GET['bulan']) ? trim($_GET['bulan']) : '';
$month_condition = '';
if ($selected_month !== '') {
    $month_condition = " AND DATE_FORMAT(p.created_at, '%Y-%m') = '" . mysqli_real_escape_string($koneksi, $selected_month) . "'";
}
$allowedModules = ['pesanan', 'penjualan', 'produk', 'toko', 'users'];
$allowedFormats = ['csv', 'xlsx', 'docx', 'pdf'];

if (!in_array($module, $allowedModules, true) || !in_array($format, $allowedFormats, true)) {
    http_response_code(400);
    die('Parameter tidak valid.');
}

function escape_cell($value) {
    return str_replace(["\r", "\n"], [' ', ' '], trim((string)$value));
}

function export_csv($filename, $headers, $rows) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    fputcsv($out, $headers);
    foreach ($rows as $row) {
        fputcsv($out, $row);
    }
    fclose($out);
    exit;
}

function export_xlsx($filename, $headers, $rows) {
    $zip = new ZipArchive();
    $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
    if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
        die('Gagal membuat file Excel.');
    }

    $sheetRows = [];
    $sheetRows[] = '<row r="1">' . implode('', array_map(function ($header, $index) {
        $col = chr(65 + $index);
        return '<c r="' . $col . '1" t="inlineStr"><is><t>' . htmlspecialchars((string)$header, ENT_XML1, 'UTF-8') . '</t></is></c>';
    }, $headers, array_keys($headers))) . '</row>';

    foreach ($rows as $rIdx => $row) {
        $rowCells = [];
        foreach ($row as $cIdx => $cell) {
            $col = chr(65 + $cIdx);
            $rowCells[] = '<c r="' . $col . ($rIdx + 2) . '" t="inlineStr"><is><t>' . htmlspecialchars((string)$cell, ENT_XML1, 'UTF-8') . '</t></is></c>';
        }
        $sheetRows[] = '<row r="' . ($rIdx + 2) . '">' . implode('', $rowCells) . '</row>';
    }

    $sheetData = implode('', $sheetRows);

    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>');
    $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>');
    $zip->addFromString('docProps/core.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:title>' . htmlspecialchars($filename, ENT_XML1, 'UTF-8') . '</dc:title><dc:creator>Admin</dc:creator><cp:lastModifiedBy>Admin</cp:lastModifiedBy></cp:coreProperties>');
    $zip->addFromString('docProps/app.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><Application>Microsoft Excel</Application></Properties>');
    $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets></workbook>');
    $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
    $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>' . $sheetData . '</sheetData></worksheet>');
    $zip->close();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    readfile($tmp);
    unlink($tmp);
    exit;
}

function export_docx($filename, $headers, $rows) {
    $zip = new ZipArchive();
    $tmp = tempnam(sys_get_temp_dir(), 'docx');
    if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
        die('Gagal membuat file Word.');
    }

    $paragraphs = [];
    $paragraphs[] = '<w:p><w:r><w:t>Export: ' . htmlspecialchars($filename, ENT_XML1, 'UTF-8') . '</w:t></w:r></w:p>';
    $paragraphs[] = '<w:p><w:r><w:t>Header: ' . htmlspecialchars(implode(', ', $headers), ENT_XML1, 'UTF-8') . '</w:t></w:r></w:p>';

    foreach ($rows as $row) {
        $paragraphs[] = '<w:p><w:r><w:t>' . htmlspecialchars(implode(' | ', $row), ENT_XML1, 'UTF-8') . '</w:t></w:r></w:p>';
    }

    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>');
    $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>');
    $zip->addFromString('docProps/core.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:title>Export</dc:title><dc:creator>Admin</dc:creator><cp:lastModifiedBy>Admin</cp:lastModifiedBy></cp:coreProperties>');
    $zip->addFromString('docProps/app.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes"><Application>Microsoft Word</Application></Properties>');
    $zip->addFromString('word/document.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>' . implode('', $paragraphs) . '</w:body></w:document>');
    $zip->close();

    header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    readfile($tmp);
    unlink($tmp);
    exit;
}

function export_pdf($filename, $headers, $rows) {
    $lines = [
        'Laporan ' . $filename,
        '',
        'Header: ' . implode(' | ', $headers),
    ];

    foreach ($rows as $row) {
        $lines[] = implode(' | ', $row);
    }

    $stream = '';
    $y = 760;
    foreach ($lines as $line) {
        $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
        $stream .= "BT /F1 10 Tf 50 {$y} Td ({$escaped}) Tj ET\n";
        $y -= 14;
    }

    $contentObject = "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "endstream";
    $pdf = "%PDF-1.4\n";
    $offsets = [0];

    $objects = [
        1 => "<< /Type /Catalog /Pages 2 0 R >>",
        2 => "<< /Type /Pages /Count 1 /Kids [3 0 R] >>",
        3 => "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>",
        4 => "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>",
        5 => $contentObject,
    ];

    foreach ($objects as $objNum => $objData) {
        $offsets[$objNum] = strlen($pdf);
        $pdf .= $objNum . " 0 obj\n" . $objData . "\nendobj\n";
    }

    $xrefStart = strlen($pdf);
    $pdf .= "xref\n0 6\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i = 1; $i <= 5; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }
    $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n" . $xrefStart . "\n%%EOF";

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $pdf;
    exit;
}

$headers = [];
$rows = [];

switch ($module) {
    case 'pesanan':
        $headers = ['Kode Pesanan', 'Pembeli', 'Toko', 'Total', 'Status', 'Tanggal'];
        $result = mysqli_query($koneksi, "SELECT p.kode_pesanan, u.nama AS pembeli, t.nama_toko, p.grand_total, p.status, p.created_at FROM pesanan p JOIN users u ON p.pembeli_id = u.id JOIN toko t ON p.toko_id = t.id WHERE 1=1" . $month_condition . " ORDER BY p.created_at DESC");
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [escape_cell($row['kode_pesanan']), escape_cell($row['pembeli']), escape_cell($row['nama_toko']), format_rupiah($row['grand_total']), escape_cell($row['status']), escape_cell($row['created_at'])];
        }
        break;
    case 'penjualan':
        $headers = ['Nama Toko', 'Penjual', 'Jumlah Pesanan', 'Total Penjualan', 'Rata-rata'];
        $result = mysqli_query($koneksi, "SELECT t.nama_toko, u.nama AS penjual, COUNT(p.id) AS jumlah_pesanan, COALESCE(SUM(p.grand_total),0) AS total FROM pesanan p JOIN toko t ON p.toko_id = t.id JOIN users u ON t.user_id = u.id WHERE p.status != 'dibatalkan'" . $month_condition . " GROUP BY t.id ORDER BY total DESC");
        while ($row = mysqli_fetch_assoc($result)) {
            $rata = $row['jumlah_pesanan'] > 0 ? $row['total'] / $row['jumlah_pesanan'] : 0;
            $rows[] = [escape_cell($row['nama_toko']), escape_cell($row['penjual']), (int)$row['jumlah_pesanan'], format_rupiah($row['total']), format_rupiah($rata)];
        }
        break;
    case 'produk':
        $headers = ['Nama Produk', 'Jenis', 'Toko', 'Harga', 'Stok', 'Tersedia'];
        $result = mysqli_query($koneksi, "SELECT p.nama_produk, j.nama_jenis, t.nama_toko, p.harga_jual, p.jumlah_stok, p.is_tersedia FROM produk p JOIN jenis_produk j ON p.jenis_produk_id = j.id JOIN toko t ON p.toko_id = t.id WHERE 1=1" . $month_condition . " ORDER BY p.created_at DESC");
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [escape_cell($row['nama_produk']), escape_cell($row['nama_jenis']), escape_cell($row['nama_toko']), format_rupiah($row['harga_jual']), (int)$row['jumlah_stok'], $row['is_tersedia'] ? 'Ya' : 'Tidak'];
        }
        break;
    case 'toko':
        $headers = ['Nama Toko', 'Penjual', 'Email', 'Alamat', 'No Telp', 'Status'];
        $result = mysqli_query($koneksi, "SELECT t.nama_toko, u.nama AS penjual, u.email, u.alamat, u.no_telp, t.is_active FROM toko t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [escape_cell($row['nama_toko']), escape_cell($row['penjual']), escape_cell($row['email']), escape_cell($row['alamat']), escape_cell($row['no_telp']), $row['is_active'] ? 'Aktif' : 'Nonaktif'];
        }
        break;
    case 'users':
        $headers = ['Nama', 'Email', 'Role', 'Status', 'Tanggal Daftar'];
        $result = mysqli_query($koneksi, "SELECT nama, email, role, is_active, created_at FROM users ORDER BY created_at DESC");
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = [escape_cell($row['nama']), escape_cell($row['email']), escape_cell($row['role']), $row['is_active'] ? 'Aktif' : 'Nonaktif', escape_cell($row['created_at'])];
        }
        break;
}

$filename = 'laporan_' . $module . '_' . date('Ymd_His') . '.' . $format;

switch ($format) {
    case 'csv':
        export_csv($filename, $headers, $rows);
        break;
    case 'xlsx':
        export_xlsx($filename, $headers, $rows);
        break;
    case 'docx':
        export_docx($filename, $headers, $rows);
        break;
    case 'pdf':
        export_pdf($filename, $headers, $rows);
        break;
}
