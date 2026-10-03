<?php

if (isset($_GET['id'])) {
    $db = connect_db();
    $suratModel = new Surat($db);
    $id = intval($_GET['id']);
    $surat = $suratModel->getSurat($id);

    if ($surat) {
        $file_path = $surat['file_path'];
        $file_name = basename($file_path);

        // Pastikan file benar-benar ada
        if (file_exists($file_path)) {
            // Tambahkan jumlah download
            $suratModel->updateJumlahDownload($id);

            // Set header agar browser melakukan download
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            flush();
            readfile($file_path);
            exit();
        } else {
            echo "File tidak ditemukan.";
        }
    } else {
        echo "Surat tidak ditemukan.";
    }
} else {
    echo "ID tidak valid.";
}
?>
