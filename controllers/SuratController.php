<?php
class SuratController {
    private $suratModel;
    private $trackingModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->suratModel = new Surat($db);
        $this->trackingModel = new Tracking($db);
    }

    public function tambahSurat($post, $files) {
        // Ambil data POST
        $jenis_surat = $post['jenis_surat'] ?? '';
        $subjek = $post['subjek'] ?? '';
        $pengirim = $post['pengirim'] ?? '';
        $tujuan = $post['tujuan'] ?? '';
        $devisi = $post['devisi'] ?? '';
        $tanggal = $post['tanggal'] ?? '';
        $no_surat = $post['no_surat'] ?? '';

        // Validasi form
        if (empty($jenis_surat) || empty($subjek) || empty($pengirim) || empty($tujuan) ||empty($devisi) || empty($tanggal) || empty($no_surat)) {
            echo "⚠️ Form tidak lengkap!";
            return false;
        }

        // Validasi file upload
        if (!isset($files['file'])) {
            echo "⚠️ Tidak ada file diupload.";
            return false;
        }

        $file = $files['file'];

        if ($file['error'] !== 0) {
            echo "⚠️ Terjadi error saat upload file. Kode error: " . $file['error'];
            return false;
        }

        if (!file_exists($file['tmp_name'])) {
            echo "❌ File sementara tidak ditemukan.";
            return false;
        }

        // Batasi ukuran file (misalnya maksimal 10MB)
        if ($file['size'] > 10 * 1024 * 1024) { // 10MB batas ukuran file
            echo "⚠️ Ukuran file maksimal 10MB.";
            return false;
        }

        // Buat nama file unik menggunakan waktu saat ini agar tidak bentrok
        $safeFileName = time() . "_" . basename($file['name']);
        $file_path = "uploads/" . $safeFileName;

        // Pastikan folder uploads ada
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }

        // Pindahkan file ke folder uploads
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            echo "❌ Gagal memindahkan file ke folder uploads.";
            return false;
        }

        echo "✅ File berhasil diupload ke: $file_path<br>";

        // Simpan ke database
        $surat_id = $this->suratModel->tambahSurat(
            $jenis_surat, $subjek, $pengirim, $tujuan, $devisi, $file_path, $tanggal, $no_surat
        );

        // Cek apakah insert berhasil
        if ($surat_id) {
            echo "✅ Data surat berhasil disimpan ke database. ID: $surat_id<br>";
            $this->trackingModel->updateStatus($surat_id, "Upload Sukses");
            return true;
        } else {
            // Jika gagal, tampilkan error yang lebih jelas
            echo "❌ Gagal menyimpan data surat ke database. Periksa koneksi dan query.";
            return false;
        }
    }

    public function lihatDetail($id) {
        $surat = $this->suratModel->getSurat($id);
        $tracking = $this->trackingModel->getTracking($id);
        return ['surat' => $surat, 'tracking' => $tracking];
    }

    public function lihatSemuaSurat() {
        return $this->suratModel->getSemuaSurat();
    }

    public function hapusSuratPermanen($id) {
        $id = intval($id);
        if ($id <= 0) {
            return false;
        }
        return $this->suratModel->deleteSuratPermanen($id);
    }

     public function hapusSurat($id) {
        $id = intval($id);
        if ($id <= 0) {
            return false;
        }
        $user_id = $_SESSION['user_id'] ?? null;
        return $this->suratModel->deleteSurat($id, $user_id);
    }

    public function lihatSuratByDevisi($devisi, $filters = []) {
        return $this->suratModel->getSuratByDevisi($devisi, $filters);
    }    

   public function getRekapStatusPerDevisi() {
    $query = "
        SELECT 
            s.devisi,
            COALESCE(t.status, 'Belum Diproses') AS status,
            COUNT(*) AS jumlah
        FROM surat s
        LEFT JOIN (
            SELECT surat_id, status 
            FROM tracking 
            WHERE (surat_id, tanggal) IN (
                SELECT surat_id, MAX(tanggal) 
                FROM tracking 
                GROUP BY surat_id
            )
        ) t ON s.id = t.surat_id
        WHERE s.deleted_at IS NULL
        GROUP BY s.devisi, t.status
    ";

    $stmt = $this->db->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $rekap = [];
    while ($row = $result->fetch_assoc()) {
        $devisi = $row['devisi'];
        $status = $row['status'];
        $rekap[$devisi][$status] = $row['jumlah'];
    }

    return $rekap;
}

    public function restoreAllSurat() {
    $deleted_surat = $this->suratModel->getDeletedSurat();
    $success = true;
    
    foreach ($deleted_surat as $surat) {
        if (!$this->suratModel->restoreSurat($surat['id'])) {
            $success = false;
        }
    }
    
    return $success;
}

public function emptyTrash() {
    $deleted_surat = $this->suratModel->getDeletedSurat();
    $success = true;
    
    foreach ($deleted_surat as $surat) {
        if (!$this->suratModel->deleteSuratPermanen($surat['id'])) {
            $success = false;
        }
    }
    
    return $success;
}

public function restoreSurat($id) {
    $id = intval($id);
    if ($id <= 0) {
        return false;
    }
    return $this->suratModel->restoreSurat($id);
}

public function getDeletedSurat() {
    return $this->suratModel->getDeletedSurat();
}

 public function updateDivisi($surat_id, $devisi) {
        $stmt = $this->db->prepare("UPDATE surat SET devisi = ? WHERE id = ?");
        $stmt->bind_param("si", $devisi, $surat_id);
        return $stmt->execute();
    }
    
}
?>