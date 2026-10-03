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
                <tr>
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

        <div class="update-forms-container">
           <div class="update-status">
                <h3>Update Status</h3>
                <form method="post">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="surat_id" value="<?php echo $surat['id']; ?>">
                    <select name="status" id="status" required>
                    <option value="">-- Pilih Status --</option>
                    <option value="Dalam Proses ⏳">Dalam Proses</option>
                    <option value="Ditunda ▶">Ditunda</option>
                    <option value="Ditinjau 🔄">Ditinjau</option>
                    <option value="Ditolak ❌">Ditolak</option>
                    <option value="Diterima ✅">Diterima</option>
                    </select>
                    <textarea name="keterangan" placeholder="Keterangan (opsional)"></textarea>
                    <button type="submit">Update</button><label for="status"></label>
                </form>
            </div>

            <div class="update-divisi">
                <h3>Update Divisi</h3>
                <form method="post">
                    <input type="hidden" name="action" value="update_divisi">
                    <input type="hidden" name="surat_id" value="<?php echo $surat['id']; ?>">
                    <select name="devisi" id="devisi" required>
                        <option value="">-- Pilih Divisi --</option>
                        <option value="Keuangan" <?= $surat['devisi'] == 'Keuangan' ? 'selected' : '' ?>>Keuangan</option>
                        <option value="General Affairs (GA)" <?= $surat['devisi'] == 'General Affairs (GA)' ? 'selected' : '' ?>>General Affairs (GA)</option>
                        <option value="Personalia" <?= $surat['devisi'] == 'Personalia' ? 'selected' : '' ?>>Personalia</option>
                        <option value="Corporate Social Responsibility (CSR)" <?= $surat['devisi'] == 'Corporate Social Responsibility (CSR)' ? 'selected' : '' ?>>Corporate Social Responsibility (CSR)</option>
                        <option value="Satuan Pengawas Internal" <?= $surat['devisi'] == 'Satuan Pengawas Internal' ? 'selected' : '' ?>>Satuan Pengawas Internal</option>
                        <option value="Teknik dan Business Development" <?= $surat['devisi'] == 'Teknik dan Business Development' ? 'selected' : '' ?>>Teknik dan Business Development</option>
                    </select>
                    <button type="submit">Update Divisi</button>
                </form>
            </div>
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
