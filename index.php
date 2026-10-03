<?php
session_start();
$timeout = 600;
if (!isset($_SESSION['user_id'])) {
    header('Location: verify');
    exit();
}

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
    session_unset();
    session_destroy();
    header("Location: verify?timeout=true");
    exit();
}


// Update waktu aktivitas terakhir
$_SESSION['last_activity'] = time();
require_once 'config/database.php';
require_once 'models/Surat.php';
require_once 'models/Tracking.php';
require_once 'controllers/SuratController.php';
$db = connect_db();
$suratController = new SuratController($db);
$suratModel = new Surat($db);
$suratModel->cleanupTrash();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Pilih folder tampilan berdasarkan user_id
$view_folder = $_SESSION['user_id'] == 1 ? 'views' : 'views2';

// Tangani request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? ''; // Hindari warning

    if ($postAction === 'update_status') {
        $surat_id = $_POST['surat_id'] ?? null;
        $status = $_POST['status'] ?? '';
        $keterangan = $_POST['keterangan'] ?? '';

        if ($surat_id && $status) {
            $trackingModel = new Tracking($db);
            if ($trackingModel->updateStatus($surat_id, $status, $keterangan)) {
                header("Location: detail&id=" . $surat_id . "&msg=success");
                exit();
            } else {
                header("Location: detail&id=" . $surat_id . "&msg=fail");
                exit();
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? ''; // Hindari warning

    if ($postAction === 'update_status') {
        // ... (kode update status tetap sama)
    } 
    elseif ($postAction === 'update_divisi') {
        $surat_id = $_POST['surat_id'] ?? null;
        $devisi = $_POST['devisi'] ?? '';

        if ($surat_id && $devisi) {
            if ($suratController->updateDivisi($surat_id, $devisi)) {
                header("Location: detail&id=" . $surat_id . "&msg=divisi_updated");
                exit();
            } else {
                header("Location: detail&id=" . $surat_id . "&msg=divisi_update_failed");
                exit();
            }
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] == 'loginAdmin') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Query hanya untuk user id = 1
    $stmt = $db->prepare("SELECT * FROM users WHERE id = 1 AND username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        // Login sukses, bisa set session atau redirect
        $_SESSION['admin_login'] = true;
        header("Location: formregister&login=success");
        exit();
    } else {
        // Login gagal, kembali ke halaman dan beri pesan error
        header("Location: overviews&login=fail");
        exit();
    }
}


// Tangani request GET dan POST(routing utama)
switch ($action) {
    case 'tambah_surat':
        if ($suratController->tambahSurat($_POST, $_FILES)) {
            header("Location: overviews&msg=upload_success");
        } else {
            header("Location: overviews&msg=upload_fail");
            exit();
        }
        break;

    case 'detail':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $data = $suratController->lihatDetail($id);
            $surat = $data['surat'];
            $tracking = $data['tracking'];
            include $view_folder . '/detail_df.php';
        } else {
            echo "ID surat tidak ditemukan.";
        }
        break;

    case 'tambah_surat_form':
        include $view_folder . '/Tdocumenfile.php';
        break;

    case 'daftar_surat':
        $filters = $_GET ?? [];
        $devisi = $_GET['devisi'] ?? null;
    
        if ($devisi) {
            $semua_surat = $suratController->lihatSuratByDevisi($devisi, $filters);
        } else {
            $semua_surat = $suratController->lihatSemuaSurat();
        }        
        include $view_folder . '/Ddocumenfile.php';
        break;
        
        
    case 'overviews':
        // Ambil data rekap dari controller
        $rekap_raw = $suratController->getRekapStatusPerDevisi();
            
        // Daftar devisi dan status (sesuaikan dengan data di database)
        $daftar_devisi = ['Keuangan', 'General Affairs (GA)', 'Personalia', 'Corporate Social Responsibility (CSR)', 'Satuan Pengawas Internal', 'Teknik dan Business Development', '-'];
        $daftar_status = ['Dalam Proses ⏳', 'Ditinjau 🔄', 'Ditunda ▶', 'Ditolak ❌', 'Diterima ✅', 'Upload Sukses'];
            
              
        // Format ulang data rekap
        $rekap = [];
        foreach ($daftar_devisi as $devisi) {
        foreach ($daftar_status as $status) {
        $rekap[$devisi][$status] = $rekap_raw[$devisi][$status] ?? 0;
        }
    }
    
        include $view_folder . '/Odocumenfile.php';
        break;
        

    case 'hapus_suratPermanen':
        $id = $_GET['id'] ?? null;
        if ($id && $suratController->hapusSuratPermanen($id)) {
            header("Location: trash&msg=deletePsucces");
            
        } else {
             header("Location: trash&msg=deletePfail");
            exit();
        }
        break;

    case 'hapus_surat':
    $id = $_GET['id'] ?? null;
    if ($id && $suratController->hapusSurat($id)) {
        // Kembali ke halaman sebelumnya dengan pesan sukses
        $redirect = isset($_GET['devisi']) ? 
                   'daftar_surat&devisi='.urlencode($_GET['devisi']).'&msg=delete_success' : 
                   'overviews&msg=delete_success';
        header("Location: ".$redirect);
    } else {
        $redirect = isset($_GET['devisi']) ? 
                   'daftar_surat&devisi='.urlencode($_GET['devisi']).'&msg=delete_fail' : 
                   'overviews&msg=delete_fail';
        header("Location: ".$redirect);
    }
    break;

    case 'formregister':
        include __DIR__ .'/daftar/formdaftarakun.php'; // atau file form register Anda
        break;

    case 'register':
        include __DIR__ .'/daftar/register.php'; // atau file form register Anda
        break;

    case 'update':
        include __DIR__ .'/daftar/update.php'; // atau file form register Anda
        break;

    case 'delete':
        include __DIR__ .'/daftar/delete.php'; // atau file form register Anda
        break;

    case 'donwload':
        include __DIR__ .'/download_file.php'; // atau file form register Anda
        break;

    case 'lihatfile':
        include __DIR__ .'/lihat_file.php'; // atau file form register Anda
        break;

    case 'trash':
    $semua_surat = $suratController->getDeletedSurat();
    if ($semua_surat === false) {
        error_log("Gagal mengambil data surat terhapus");
        die("Gagal memuat data sampah");
    }
    include $view_folder . '/Sdocumenfile.php';
    break;

   case 'restore_all':
    if ($suratController->restoreAllSurat()) {
        header("Location: trash&msg=restore_all_success");
    } else {
        header("Location: trash&msg=restore_fail");
    }
    break;

case 'empty_trash':
    if ($suratController->emptyTrash()) {
        header("Location: trash&msg=empty_trash_success");
    } else {
        header("Location: trash&msg=permanent_delete_fail");
    }
    break;

case 'restore':
    $id = $_GET['id'] ?? null;
    if ($id && $suratController->restoreSurat($id)) {
        header("Location: trash&msg=restore_success");
    } else {
        header("Location: trash&msg=restore_fail");
    }
    break;
                
}
?>
