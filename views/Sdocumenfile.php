
<!DOCTYPE html>
<head>
    <title>Surat di Sampah</title>
    <link rel="stylesheet" href="viewCss/trash.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="gambar/icon.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<aside class="sidebar">
  <div class="sidebar-header">
    <img src="gambar/Logo PT. ADS2.png" alt="Logo PT. ADS" class="sidebar-logo">
  </div>
  <hr class="sidebar-divider" />
  <ul class="sidebar-menu">
    <li class="sidebar-menu-trash"><i></i>🗑️ TRASH</li>
</aside>

<div class="container">
    <div class="trash-container">
        <div class="trash-header">
            <h2><i class="fas fa-trash"></i> Surat di Sampah</h2>
            <div>
                <span class="badge">Total: <?php echo count($semua_surat); ?></span>
            </div>
        </div>
        
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-<?php echo strpos($_GET['msg'], 'success') !== false ? 'success' : 
                                      (strpos($_GET['msg'], 'info') !== false ? 'info' : 'error'); ?>">
                <i class="fas <?php echo strpos($_GET['msg'], 'success') !== false ? 'fa-check-circle' : 
                                      (strpos($_GET['msg'], 'info') !== false ? 'fa-info-circle' : 'fa-exclamation-circle'); ?>"></i>
                <?php 
                    $messages = [
                        'deletePsucces' => 'Surat berhasil dihapus permanen!',
                        'deletePfail' => 'Gagal menghapus permanen surat.',
                        'restore_success' => 'Surat berhasil dipulihkan!',
                        'restore_fail' => 'Gagal memulihkan surat.',
                        'cleanup_success' => 'Berhasil membersihkan ' . ($_GET['count'] ?? 0) . ' surat yang sudah lebih dari 30 hari di sampah.',
                        'restore_all_success' => 'Semua surat berhasil dipulihkan!',
                        'empty_trash_success' => 'Sampah berhasil dikosongkan!'
                    ];
                    echo $messages[$_GET['msg']] ?? '';
                ?>
            </div>
        <?php endif; ?>

        <div class="trash-actions">
            <button type="button" class="btn-restore-all" onclick="restoreAllSurat()">
                <i class="fas fa-undo"></i> Pulihkan Semua
            </button>
            <button type="button" class="btn-empty-trash" onclick="emptyTrash()">
                <i class="fas fa-trash"></i> Kosongkan Sampah
            </button>
            <a href="overviews" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Overview
            </a>
        </div>

        <div class="table-wrapper">
    <table class="trash-table">
        <thead>
            <tr>
                <th>NO Surat</th>
                <th>Divisi</th>
                <th>Jenis Surat</th>
                <th>Tanggal Dihapus</th>
                <th>Sisa Hari</th>
                <th>Nama File</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($semua_surat)): ?>
                <tr>
                    <td colspan="7" class="empty-trash-message">
                        <i class="fas fa-trash"></i><br>
                        Sampah kosong. Tidak ada surat yang dihapus.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($semua_surat as $surat): 
                    $deleted_date = new DateTime($surat['deleted_at']);
                    $current_date = new DateTime();
                    $interval = $deleted_date->diff($current_date);
                    $days_left = 30 - $interval->days;
                ?>
                <tr>
                    <td><?= htmlspecialchars($surat['no_surat']) ?></td>
                    <td><?= htmlspecialchars($surat['devisi']) ?></td>
                    <td><?= htmlspecialchars($surat['jenis_surat']) ?></td>
                    <td><?= date('d M Y H:i', strtotime($surat['deleted_at'])) ?></td>
                    <td>
                        <?php if ($days_left > 0): ?>
                        <span class="days-left"><?= $days_left ?> hari lagi</span>
                        <?php else: ?>
                        <span class="days-warning">Akan dihapus (Proses cleanup berikutnya)</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(basename($surat['file_path'])) ?></td>
                    <td>
                        <div class="trash-item-actions">
                            <button type="button" class="btn-restore" onclick="restoreSurat(<?= $surat['id'] ?>)">
                                <i class="fas fa-undo"></i> Pulihkan
                            </button>
                            <button type="button" class="btn-delete" onclick="permanentDeleteSurat(<?= $surat['id'] ?>)">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
    </div>

<script>
function restoreSurat(id) {
    if (confirm("Apakah Anda yakin ingin memulihkan surat ini?")) {
        window.location.href = 'restore&id=' + id;
    }
}

function permanentDeleteSurat(id) {
    if (confirm("Apakah Anda yakin ingin menghapus permanen surat ini? Tindakan ini tidak dapat dibatalkan!")) {
        window.location.href = 'hapus_suratPermanen&id=' + id;
    }
}

function restoreAllSurat() {
    if (confirm("Apakah Anda yakin ingin memulihkan SEMUA surat yang ada di sampah?")) {
        window.location.href = 'restore_all';
    }
}

function emptyTrash() {
    if (confirm("Apakah Anda yakin ingin mengosongkan sampah? SEMUA surat akan dihapus permanen!")) {
        window.location.href = 'empty_trash';
    }
}

</script>
</div>
</body>
</html>