<!DOCTYPE html>
<html>
<head>
    <title>Detail Surat</title>
    <link rel="stylesheet" href="viewCss/detail.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="gambar/icon.png" type="image/png">
</head>
<body>
<aside class="sidebar">
  <div class="sidebar-header">
    <img src="gambar/Logo PT. ADS2.png" alt="Logo PT. ADS" class="sidebar-logo">
  </div>
  <hr class="sidebar-divider" />
  <ul class="sidebar-menu">
    <li class="sidebar-menu-detail"><i></i>DETAIL</li>
    <li class="sidebar-menu-back">
      <a href="overviews"><i class="fas fa-arrow-left"></i>BACK</a>
    </li>
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
                <a href="lihatfile&id=<?php echo $surat['id']; ?>" target="_blank">Lihat File</a>
                <button class="share-link-btn" data-link="lihatfile&id=<?= $surat['id'] ?>">Share Link</button>
            </div>
                      
        <h3 class="tracking-title">Riwayat Tracking</h3>
        <div class="table-scroll">
            <table>
                <thead>
                <tr>
                    <th>Waktu</th>
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
         <script>
            document.querySelectorAll('.share-link-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                const link = window.location.origin + '/projekmagang/bagiUrl/<?php echo $surat['id']; ?>';
                navigator.clipboard.writeText(link);
                alert('Link disalin: ' + link);
            });
        });

        </script>
    </div>
</body>
</html>
