<?php
// Ambil semua surat dari database lewat controller
require_once 'config/database.php';
require_once 'models/Surat.php';
require_once 'controllers/SuratController.php';

$db = connect_db();
$suratController = new SuratController($db);

// Hanya ambil surat yang belum dihapus
$semua_surat = $suratController->lihatSemuaSurat();

// Daftar devisi yang ingin ditampilkan
$daftar_devisi = [
    'Keuangan',
    'General Affairs (GA)',
    'Personalia',
    'Corporate Social Responsibility (CSR)',
    'Satuan Pengawas Internal',
    'Teknik dan Business Development',
    'Lainnya'
];

// Hitung jumlah surat per devisi (hanya yang belum dihapus)
$jumlah_per_devisi = array_fill_keys($daftar_devisi, 0);
foreach ($semua_surat as $surat) {
    $devisi = $surat['devisi'];
    // Handle data lama yang masih menggunakan '-'
    if ($devisi === '-') {
        $devisi = 'Lainnya';
    }
    if (isset($jumlah_per_devisi[$devisi])) {
        $jumlah_per_devisi[$devisi]++;
    }
}

// Hitung total surat (hanya yang belum dihapus)
$total_surat = count($semua_surat);

// Ambil data rekap status
$rekap_raw = $suratController->getRekapStatusPerDevisi();
$daftar_status = ['Dalam Proses ⏳', 'Ditinjau 🔄', 'Ditunda ▶', 'Ditolak ❌', 'Diterima ✅', 'Upload Sukses'];

// Format ulang data rekap (hanya yang belum dihapus)
$rekap = [];
foreach ($daftar_devisi as $devisi) {
    // Handle mapping untuk data lama
    $devisi_key = ($devisi === 'Lainnya') ? '-' : $devisi;
    foreach ($daftar_status as $status) {
        $rekap[$devisi][$status] = $rekap_raw[$devisi_key][$status] ?? 0;
    }
}

$msg = $_GET['msg'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Overview Surat</title>
    <link rel="stylesheet" href="viewCss/overviews.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="gambar/icon.png" type="image/png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

<div class="main-wrapper" style="display:flex; height:130vh;">
  <button class="sidebar-toggle" id="sidebarToggle">☰</button>
    <!-- SIDEBAR HTML -->
    <nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="gambar/Logo PT. ADS2.png" alt="Logo PT. ADS" width="500" height="300" class="sidebar-logo">
    </div>
    <ul class="sidebar-menu">
        <!-- Overview -->
        <li<?php if($is_overview) echo ' class="active"'; ?>>
            <a href="overviews"><span>OVERVIEW</span></a>
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
                <li<?php if($current_devisi=='-' || $current_devisi=='Lainnya') echo ' class="active"'; ?>>
                    <a href="daftar_surat?devisi=<?= urlencode('-') ?>"><span>LAINNYA</span></a>
                </li>
            </ul>
        </li>

        <!-- Logout -->
        <li>
            <form action="logout.php" method="get" style="margin:0;">
                <button type="submit" class="sidebar-logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span style="margin-left:8px;">LOGOUT</span>
                </button>
            </form>
        </li>
    </ul>
</nav>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
  <div class="responsive-table">
    <div class="main-content" id="mainContent">

        <h2>OVERVIEW SURAT</h2>
        <!-- Tabel pertama -->
        <h4>JUMLAH SURAT/FILE PADA SETIAP DIVISI</h4>
        <div class="table-responsive">
        <table class="overview-table">
        <thead class="thead-dark">
            <tr>
                <th>DIVISI</th>
                <th>JUMLAH SURAT</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($jumlah_per_devisi as $devisi => $jumlah): ?>
            <tr>
                <td><?= htmlspecialchars($devisi) ?></td>
                <td style="text-align:center;"><?= $jumlah ?></td>
            </tr>
            <?php endforeach; ?>
            <tr style="background:#f6f6f6;font-weight:bold;">
                <td>Total Surat</td>
                <td style="text-align:center;"><?= $total_surat ?></td>
            </tr>
         </tbody>   
        </table>
        </div>

        <!-- Tabel kedua -->
        <h4>JUMLAH SURAT/FILE PADA SETIAP STATUS DIVISI</h4>
        <div class="table-responsive">
        <table class="overview-table">
        <thead class="thead-dark">
        <tr>
            <th>DIVISI</th>
            <?php foreach ($daftar_status as $status): ?>
                <?php if ($status === 'Upload Sukses'): ?>
                    <th>Terupload</th>
                <?php else: ?>
                    <th><?= htmlspecialchars($status) ?></th>
                <?php endif; ?>
            <?php endforeach; ?>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($daftar_devisi as $devisi): ?>
            <tr>
                <td><?= htmlspecialchars($devisi) ?></td>
                <?php
                $total_devisi = 0;
                foreach ($daftar_status as $status) {
                    $total_devisi += $rekap[$devisi][$status];
                    ?>
                    <td><?= $rekap[$devisi][$status] ?></td>
                <?php } ?>
                <td><strong><?= $total_devisi ?></strong></td>
            </tr>
         <?php endforeach; ?>
       </tbody>
     </table>
     </div>
 </div>
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

<?php if ($msg == 'upload_success'): ?>
<script>
    Swal.fire('Sukses', 'Upload file berhasil!', 'success');
</script>
<?php elseif ($msg == 'upload_fail'): ?>
<script>
    Swal.fire('Gagal', 'Upload file gagal!', 'error');
</script>
<?php endif; ?>

<?php if ($msg == 'delete_success'): ?>
<script>
    Swal.fire('Sukses', 'Surat berhasil dipindahkan ke sampah!', 'success');
</script>
<?php elseif ($msg == 'delete_fail'): ?>
<script>
    Swal.fire('Gagal', 'Surat gagal dipindahkan ke sampah!', 'error');
</script>
<?php endif; ?>

</div>
</body>
</html>