<?php
function getExtension($file_path) {
    return strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
}

function getFullUrl($file_path) {
    // Ubah ke URL lengkap sesuai path server Anda
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['PHP_SELF']);
    return "http://$host$path/" . $file_path;
}

if (isset($_GET['id'])) {
    $db = connect_db();
    $suratModel = new Surat($db);
    $id = intval($_GET['id']);
    $surat = $suratModel->getSurat($id);

    if ($surat) {
        $suratModel->updateJumlahDilihat($id);
        $file_path = $surat['file_path'];
        $ext = getExtension($file_path);
        $file_url = getFullUrl($file_path);

        // Ekstensi file
        $office_ext = ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'];
        $image_ext = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];

        // Preview Office dan PDF via Google Docs Viewer
        if (in_array($ext, $office_ext) || $ext === 'pdf') {
            $google_viewer = "https://docs.google.com/gview?url=" . urlencode($file_url) . "&embedded=true";
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <link rel="icon" href="gambar/icon.png" type="image/png">
                <title>Lihat File Surat</title>
                <style>
                    body, html { margin:0; padding:0; height:100%; }
                    iframe { width:100vw; height:98vh; border:none; }
                </style>
            </head>
            <body>
                <iframe src="<?= htmlspecialchars($google_viewer) ?>"></iframe>
            </body>
            </html>
            <?php
            exit();
        }
        // Preview gambar
        elseif (in_array($ext, $image_ext)) {
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <link rel="icon" href="gambar/icon.png" type="image/png">
                <title>Lihat File Surat</title>
                <style>
                    body, html { margin:0; padding:0; height:100%; display:flex; align-items:center; justify-content:center; }
                    img { max-width:100vw; max-height:98vh; }
                </style>
            </head>
            <body>
                <img src="<?= htmlspecialchars($file_url) ?>" alt="File Surat">
            </body>
            </html>
            <?php
            exit();
        }
        // File tidak didukung preview
        else {
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <link rel="icon" href="gambar/icon.png" type="image/png">
                <title>Lihat File Surat</title>
            </head>
            <body>
                <p>Preview tidak tersedia untuk format file ini.</p>
            </body>
            </html>
            <?php
            exit();
        }
    } else {
        echo "❌ Surat tidak ditemukan.";
    }
} else {
    echo "❌ ID tidak valid.";
}
?>