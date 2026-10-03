<?php
session_start();
require_once 'config/database.php';
require_once 'models/Surat.php';
require_once 'models/Tracking.php';

$db = connect_db();
$suratModel = new Surat($db);
$trackingModel = new Tracking($db);

// Ambil ID surat dari parameter URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("ID surat tidak valid");
}

// Ambil data surat dan tracking
$surat = $suratModel->getSurat($id);
$tracking = $trackingModel->getTracking($id);

if (!$surat) {
    die("Surat tidak ditemukan");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Detail Surat</title>
    <link rel="stylesheet" href="../viewCss/detail.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../gambar/icon.png" type="image/png">
</head>
<body>
    <aside class="sidebar">
  <div class="sidebar-header">
    <img src="../gambar/Logo PT. ADS2.png" alt="Logo PT. ADS" class="sidebar-logo">
  </div>
  <hr class="sidebar-divider" />
  <ul class="sidebar-menu">
    <li class="sidebar-menu-detail"><i></i>DETAIL</li>
  </ul>
</aside>
<div class="container">
        <h2>Detail Surat</h2>

        <div class="tables-container">
            <table class="detail-table">
                <tr>
                    <th>NO Surat</th>
                    <td><?= htmlspecialchars($surat['no_surat']) ?></td>
                </tr>
                <tr>
                    <th>Jenis Surat</th>
                    <td><?= htmlspecialchars($surat['jenis_surat']) ?></td>
                </tr>
                <tr>
                    <th>Judul</th>
                    <td><?= htmlspecialchars($surat['subjek']) ?></td>
                </tr>
                <tr>
                    <th>Pengirim</th>
                    <td><?= htmlspecialchars($surat['pengirim']) ?></td>
                </tr>
                <tr>
                    <th>Tujuan</th>
                    <td><?= htmlspecialchars($surat['tujuan']) ?></td>
                </tr>
            </table>

            <table class="detail-table">
                <tr>
                    <th>Tanggal</th>
                    <td><?= htmlspecialchars($surat['tanggal']) ?></td>
                </tr>
                <tr>
                    <th>Nama File</th>
                    <td><?= htmlspecialchars(basename($surat['file_path'])) ?></td>
                </tr>
                <tr>
                    <th>Dilihat</th>
                    <td><?= htmlspecialchars($surat['jumlah_dilihat']) ?> Kali Dilihat</td>
                </tr>
                <th>Di Download</th>
                    <td><?= htmlspecialchars($surat['jumlah_download']) ?> Kali Didownload</td>
                </tr>
                 <tr>
                    <th>Divisi</th>
                    <td><?php echo htmlspecialchars($surat['devisi']); ?></td>
                </tr>
            </table>
            </div>

            <div class="file-link">
                <a href="../lihatfile&id=<?php echo $surat['id']; ?>" target="_blank">Lihat File</a>
            </div>
                      
        <h3 class="tracking-title">Riwayat Tracking</h3>
        <div class="table-scroll">
            <table>
                <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th>Keterangan</th>
                </tr>
                </thead>
                <tbody>
                <?php if (!empty($tracking)) : ?>
                    <?php foreach ($tracking as $track) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($track['tanggal']); ?></td>
                            <td><?php echo htmlspecialchars($track['status']); ?></td>
                            <td><?php echo htmlspecialchars($track['keterangan'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="2">Belum ada riwayat tracking.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>