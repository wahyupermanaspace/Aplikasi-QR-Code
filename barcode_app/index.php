<?php
// Konfigurasi File XML
$file = 'data.xml';

// Membuat file XML jika belum ada
if (!file_exists($file)) {
    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput = true;
    $xml->appendChild($xml->createElement('items'));
    $xml->save($file);
}

// Load data XML
$xml = simplexml_load_file($file);

// --- PROSES CRUD ---

// 1. CREATE (Tambah Data)
if (isset($_POST['add'])) {
    $id = time(); // Membuat ID unik berdasarkan waktu
    $name = htmlspecialchars($_POST['name']);
    $code = htmlspecialchars($_POST['code']);

    $item = $xml->addChild('item');
    $item->addAttribute('id', $id);
    $item->addChild('name', $name);
    $item->addChild('code', $code);

    // Format agar rapi dan simpan
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    $dom->save($file);

    header("Location: index.php");
    exit;
}

// 2. DELETE (Hapus Data)
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $index = 0;
    foreach ($xml->item as $item) {
        if ((string)$item['id'] == $id) {
            unset($xml->item[$index]);
            break;
        }
        $index++;
    }
    $xml->asXML($file);
    header("Location: index.php");
    exit;
}

// 3. UPDATE (Edit Data)
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = htmlspecialchars($_POST['name']);
    $code = htmlspecialchars($_POST['code']);

    foreach ($xml->item as $item) {
        if ((string)$item['id'] == $id) {
            $item->name = $name;
            $item->code = $code;
            break;
        }
    }
    $xml->asXML($file);
    header("Location: index.php");
    exit;
}

// --- FITUR DOWNLOAD QR CODE ---
if (isset($_GET['action']) && $_GET['action'] == 'download' && isset($_GET['code'])) {
    $code = $_GET['code'];
    $name = isset($_GET['name']) ? $_GET['name'] : 'QR_Code';
    
    // Nama file dibersihkan dari karakter ilegal
    $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name) . ".png";
    $url = "https://quickchart.io/qr?text=" . urlencode($code) . "&size=300";
    
    $image = file_get_contents($url);
    
    if ($image !== false) {
        header('Content-Description: File Transfer');
        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($image));
        echo $image;
        exit;
    } else {
        die("Gagal mengunduh gambar.");
    }
}

// --- TAMPILAN PRINT QR CODE KHUSUS ---
if (isset($_GET['action']) && $_GET['action'] == 'print' && isset($_GET['code'])) {
    $code = htmlspecialchars($_GET['code']);
    $name = htmlspecialchars($_GET['name']);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Print QR Code - <?= $name ?></title>
        <style>
            body { text-align: center; font-family: Arial, sans-serif; margin-top: 50px; }
            .qr-container { display: inline-block; padding: 20px; border: 1px dashed #ccc; border-radius: 10px; }
            img { max-width: 100%; height: auto; }
            @media print {
                .no-print { display: none; }
                .qr-container { border: none; }
            }
        </style>
    </head>
    <body>
        <div class="qr-container">
            <h3><?= $name ?></h3>
            <img src="https://quickchart.io/qr?text=<?= urlencode($code) ?>&size=250" alt="QR Code <?= $code ?>">
            <p style="font-size: 12px; color: #555;"><?= $code ?></p>
        </div>
        <br><br>
        <button class="no-print" onclick="window.print()" style="padding: 10px 20px; font-size: 16px; cursor: pointer;">🖨️ Cetak Sekarang</button>
        <a href="index.php" class="no-print" style="margin-left: 10px;">Kembali</a>
        <script> window.onload = function() { window.print(); } </script>
    </body>
    </html>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi CRUD QR Code & XML</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7f6; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #333; }
        form { margin-bottom: 20px; padding: 15px; background: #e9ecef; border-radius: 5px; }
        input[type="text"] { padding: 8px; width: calc(50% - 20px); margin-right: 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #218838; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 12px; text-align: center; vertical-align: middle; }
        th { background-color: #6f42c1; color: white; }
        .btn-edit { background: #ffc107; color: black; text-decoration: none; padding: 5px 10px; border-radius: 3px; font-size: 14px;}
        .btn-delete { background: #dc3545; color: white; text-decoration: none; padding: 5px 10px; border-radius: 3px; font-size: 14px;}
        .btn-print { background: #17a2b8; color: white; text-decoration: none; padding: 5px 10px; border-radius: 3px; font-size: 14px;}
        .btn-download { background: #007bff; color: white; text-decoration: none; padding: 5px 10px; border-radius: 3px; font-size: 14px;}
        .btn-view { background: #28a745; color: white; padding: 5px 10px; border-radius: 3px; text-decoration: none; font-size: 14px;}
        .qr-img { height: 70px; width: 70px; border-radius: 5px; border: 1px solid #ddd; padding: 2px; }
        .action-btns { display: flex; gap: 5px; justify-content: center; flex-wrap: wrap; }
    </style>
</head>
<body>

<div class="container">
    <h2>📱 Aplikasi QR Code (Database XML)</h2>

    <?php
    // --- TAMPILAN FORM EDIT ---
    if (isset($_GET['edit'])) {
        $edit_id = $_GET['edit'];
        $edit_name = '';
        $edit_code = '';
        
        foreach ($xml->item as $item) {
            if ((string)$item['id'] == $edit_id) {
                $edit_name = (string)$item->name;
                $edit_code = (string)$item->code;
                break;
            }
        }
    ?>
        <form method="POST" action="index.php">
            <input type="hidden" name="id" value="<?= $edit_id ?>">
            <input type="text" name="name" value="<?= $edit_name ?>" required placeholder="Nama / Judul">
            <input type="text" name="code" value="<?= $edit_code ?>" required placeholder="Data QR (Teks, Angka, atau URL Web)">
            <button type="submit" name="update">Update Data</button>
            <a href="index.php" style="margin-left: 10px;">Batal</a>
        </form>
    <?php } else { ?>
    <!-- --- TAMPILAN FORM TAMBAH --- -->
        <form method="POST" action="index.php">
            <input type="text" name="name" required placeholder="Judul (Contoh: Website Saya)">
            <input type="text" name="code" required placeholder="Data QR (Contoh: https://google.com)">
            <button type="submit" name="add">➕ Tambah Data</button>
        </form>
    <?php } ?>
    
    <?php
    // --- TAMPILAN VIEW DETAIL ---
    if (isset($_GET['view'])) {
        $view_id = $_GET['view'];
        foreach ($xml->item as $item) {
            if ((string)$item['id'] == $view_id) {
                $view_name = (string)$item->name;
                $view_code = (string)$item->code;
                break;
            }
        }
    ?>
    <div style="background: #e9ecef; padding: 20px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
        <h3>Detail QR Code: <?= $view_name ?></h3>
        <img src="https://quickchart.io/qr?text=<?= urlencode($view_code) ?>&size=150" alt="QR" style="border: 2px solid #fff; box-shadow: 0 0 5px rgba(0,0,0,0.2); border-radius: 10px;">
        <p><strong>Isi Data:</strong> <br> <a href="<?= $view_code ?>" target="_blank"><?= $view_code ?></a></p>
        
        <div style="margin-top: 15px;">
            <a href="index.php?action=download&code=<?= urlencode($view_code) ?>&name=<?= urlencode($view_name) ?>" style="padding: 8px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin-right: 5px;">💾 Download QR</a>
            <a href="index.php" style="padding: 8px 15px; background: #6f42c1; color: white; text-decoration: none; border-radius: 4px;">Kembali</a>
        </div>
    </div>
    <?php 
    } // Akhir dari Tampilan View
    ?>

    <!-- --- TAMPILAN TABEL DATA --- -->
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama / Judul</th>
                <th>Isi Data QR</th>
                <th>Preview QR</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            foreach ($xml->item as $item) {
                $id = (string)$item['id'];
                $name = (string)$item->name;
                $code = (string)$item->code;
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $name ?></td>
                <td><small><?= $code ?></small></td>
                <td>
                    <!-- Tampilan Preview QR Code -->
                    <img class="qr-img" src="https://quickchart.io/qr?text=<?= urlencode($code) ?>&size=100" alt="QR">
                </td>
                <td>
                    <div class="action-btns">
                        <a href="index.php?action=download&code=<?= urlencode($code) ?>&name=<?= urlencode($name) ?>" class="btn-download">💾 Download</a>
                        <a href="index.php?action=print&code=<?= urlencode($code) ?>&name=<?= urlencode($name) ?>" class="btn-print" target="_blank">🖨️ Print</a>
                        <a href="index.php?view=<?= $id ?>" class="btn-view">👁️ View</a>
                        <a href="index.php?edit=<?= $id ?>" class="btn-edit">✏️ Edit</a>
                        <a href="index.php?delete=<?= $id ?>" class="btn-delete" onclick="return confirm('Yakin ingin menghapus data ini?')">🗑️ Hapus</a>
                    </div>
                </td>
            </tr>
            <?php } ?>
            
            <?php if(count($xml->item) == 0): ?>
            <tr>
                <td colspan="5">Belum ada data. Silakan tambahkan data produk / link.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>