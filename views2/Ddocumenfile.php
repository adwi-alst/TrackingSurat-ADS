<!DOCTYPE html>
<html>
<head>
    <title>Daftar Surat</title>
    <link rel="stylesheet" href="viewCss/daftarsurat.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="gambar/icon.png" type="image/png">
</head>
<body>
<?php
    // Identifikasi menu aktif
    $current_page = basename($_SERVER['PHP_SELF']);
    $current_action = isset($_GET['action']) ? $_GET['action'] : '';
    $current_devisi = isset($_GET['devisi']) ? $_GET['devisi'] : '';
    // Untuk action overview, sesuaikan dengan parameter yang Anda gunakan (?action=overviews)
    $is_overview = ($current_action == 'overviews');
    $is_tambah = ($current_action == 'tambah_surat_form');
    $is_devisi = !empty($current_devisi);
?>

<div class="main-wrapper">
    <button class="sidebar-toggle" id="sidebarToggle">☰</button>
    <!-- SIDEBAR HTML -->
    <nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="gambar/Logo PT. ADS2.png" alt="Logo PT. ADS" width="500" height="300" class="sidebar-logo">
    </div>
    <ul class="sidebar-menu">
        <!-- Overview -->
        <li<?php if($is_overview) echo ' class="active"'; ?>>
            <a href="overviews"><span>Overview</span></a>
        </li>
        <!-- Devisi Dropdown -->
        <li class="sidebar-dropdown<?php if($is_devisi) echo ' active'; ?>">
            <a href="#" id="devisiToggle"><span>DIVISI</span></a>
            <ul class="sidebar-submenu<?php if($is_devisi) echo ' active'; ?>" id="devisiSubmenu" style="<?php if($is_devisi) echo 'display:block;'; ?>">
                <li<?php if($current_devisi=='Keuangan') echo ' class="active"'; ?>>
                    <a href="daftar_surat?devisi=<?= urlencode('Keuangan') ?>"><span>KEUANGAN</span></a>
                </li>
                <li<?php if($current_devisi=='General Affairs (GA)') echo ' class="active"'; ?>>
                    <a href="daftar_surat?devisi=<?= urlencode('General Affairs (GA)') ?>"><span>GENERAL AFFAIRS (GA)</span></a>
                </li>
                <li<?php if($current_devisi=='Personalia') echo ' class="active"'; ?>>
                    <a href="daftar_surat?devisi=<?= urlencode('Personalia') ?>"><span>PERSONALIA</span></a>
                </li>
                <li<?php if($current_devisi=='Corporate Social Responsibility (CSR)') echo ' class="active"'; ?>>
                    <a href="daftar_surat?devisi=<?= urlencode('Corporate Social Responsibility (CSR)') ?>"><span>CORPORATE SOCIAL RESPONSIBILITY (CSR)</span></a>
                </li>
                <li<?php if($current_devisi=='Satuan Pengawas Internal') echo ' class="active"'; ?>>
                    <a href="daftar_surat?devisi=<?= urlencode('Satuan Pengawas Internal') ?>"><span>SATUAN PENGAWAS INTERNAL</span></a>
                </li>
                <li<?php if($current_devisi=='Teknik dan Business Development') echo ' class="active"'; ?>>
                    <a href="daftar_surat?devisi=<?= urlencode('Teknik dan Business Development') ?>"><span>TEKNIK & BUSINESS DEVELOPMENT</span></a>
                </li>
                <li<?php if($current_devisi=='-') echo ' class="active"'; ?>>
                    <a href="daftar_surat?devisi=<?= urlencode('-') ?>"><span>LAINNYA!</span></a>
                </li>
            </ul>
        </li>
        <!-- Logout -->
        <li>
            <form action="logout.php" method="get" style="margin:0;">
                <button type="submit" class="sidebar-logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span style="margin-left:8px;">Logout</span>
                </button>
            </form>
        </li>
    </ul>
</nav>

<!-- Tambahkan JavaScript berikut sebelum </body> -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var devisiToggle = document.getElementById('devisiToggle');
    var devisiSubmenu = document.getElementById('devisiSubmenu');
    if(devisiToggle && devisiSubmenu) {
        devisiToggle.addEventListener('click', function(e) {
            e.preventDefault();
            if(devisiSubmenu.style.display === 'block') {
                devisiSubmenu.style.display = 'none';
            } else {
                devisiSubmenu.style.display = 'block';
            }
        });
    }
});
</script>

<div class="sidebar-overlay" id="sidebarOverlay"></div>


<div class="container" id="container">
    <h2>Daftar Surat</h2>
    <!-- Filter Form -->
    <form method="get" class="filter-form">
        <input type="hidden" name="action" value="daftar_surat">
        <input type="hidden" name="devisi" value="<?php echo htmlspecialchars($_GET['devisi'] ?? ''); ?>">
        <label>
            NO Surat
            <input type="text" name="no_surat" value="<?php echo isset($_GET['no_surat']) ? htmlspecialchars($_GET['no_surat']) : ''; ?>">
        </label>
        <label>
            Nama File
            <input type="text" name="nama_file" value="<?php echo isset($_GET['nama_file']) ? htmlspecialchars($_GET['nama_file']) : ''; ?>">
        </label>
        <label>
            Tanggal Upload
            <input type="date" name="tanggal" value="<?php echo isset($_GET['tanggal']) ? htmlspecialchars($_GET['tanggal']) : ''; ?>">
        </label>
        <label>
            Jenis Surat
            <select name="jenis_surat">
                <option value="">-- Semua --</option>
                <?php
                // Contoh pilihan, ganti dengan data dari database jika perlu
                $jenisOptions = ['masuk', 'keluar', 'undangan', 'pengumuman'];
                $jenis_selected = isset($_GET['jenis_surat']) ? $_GET['jenis_surat'] : '';
                foreach ($jenisOptions as $jenis) {
                    echo '<option value="'.htmlspecialchars($jenis).'"'.($jenis_selected==$jenis?' selected':'').'>'.ucwords($jenis).'</option>';
                }
                ?>
            </select>
        </label>
        <label>
            Pengirim
            <input type="text" name="pengirim" value="<?php echo isset($_GET['pengirim']) ? htmlspecialchars($_GET['pengirim']) : ''; ?>">
        </label>
        <label class="sort-jumlah-dilihat">
            Urutkan Berdasarkan Jumlah Dilihat
            <select name="sort_jumlah_dilihat">
                <option value="">-- Default --</option>
                <option value="desc" <?php echo (isset($_GET['sort_jumlah_dilihat']) && $_GET['sort_jumlah_dilihat']=='desc')?'selected':''; ?>>Terbanyak ke Terkecil</option>
                <option value="asc" <?php echo (isset($_GET['sort_jumlah_dilihat']) && $_GET['sort_jumlah_dilihat']=='asc')?'selected':''; ?>>Terkecil ke Terbanyak</option>
            </select>
        </label>
        <button type="submit">Filter</button>
        <button type="button" class="reset-button" onclick="window.location.href='daftar_surat<?php echo isset($_GET['devisi']) ? '&devisi=' . urlencode($_GET['devisi']) : ''; ?>'">Reset</button>
    </form>

    <?php
$filtered_surat = $semua_surat; // Sudah difilter dari controller
?>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>NO Surat</th>
                    <th>Divisi</th>
                    <th>Jenis Surat</th>
                    <th>Pengirim</th>
                    <th>Tanggal</th>
                    <th>Nama File</th>
                    <th style="text-align:center">Jumlah Dilihat</th>
                    <th style="text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($filtered_surat)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;">Data tidak ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($filtered_surat as $surat): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($surat['no_surat']); ?></td>
                        <td><?php echo htmlspecialchars($surat['devisi']); ?></td>
                        <td><?php echo htmlspecialchars($surat['jenis_surat']); ?></td>
                        <td><?php echo htmlspecialchars($surat['pengirim']); ?></td>
                        <td><?php echo htmlspecialchars($surat['tanggal']); ?></td>
                        <td><?php echo htmlspecialchars(basename($surat['file_path'])); ?></td>
                        <td style="text-align:center"><?php echo htmlspecialchars($surat['jumlah_dilihat']); ?></td>
                        <td>
                            <div class="form-buttons">
                                <button type="button" class="detail" onclick="window.location.href='detail&id=<?php echo $surat['id']; ?>'">Detail</button>
                                <button type="button" class="download" onclick="window.location.href='donwload&id=<?php echo $surat['id']; ?>'"><i class="fas fa-download"></i></button>
                         </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <script>

    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const overlay = document.getElementById('sidebarOverlay');

    // Tampilkan sidebar & overlay
    toggleBtn.addEventListener('click', function() {
    sidebar.classList.add('active');
    overlay.classList.add('active');
    toggleBtn.style.display = 'none';
   });

    overlay.addEventListener('click', function() {
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
    toggleBtn.style.display = 'block';
   });

    // SIDEBAR JAVASCRIPT
    document.addEventListener('DOMContentLoaded', function() {
       var devisiToggle = document.getElementById('devisiToggle');
       var devisiSubmenu = document.getElementById('devisiSubmenu');
    devisiToggle.addEventListener('click', function(e) {
        e.preventDefault();
        devisiSubmenu.classList.toggle('active');
    });
});

</script>
</div>
</div>
</body>
</html>
