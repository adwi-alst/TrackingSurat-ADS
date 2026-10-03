<?php
class Surat {
    private $db;

    // Constructor menerima koneksi database
    public function __construct($db) {
        $this->db = $db;
    }

    // Method untuk menambah surat baru
    public function tambahSurat($jenis_surat, $subjek, $pengirim, $tujuan, $devisi, $file_path, $tanggal, $no_surat) {
        $stmt = $this->db->prepare("INSERT INTO surat (jenis_surat, subjek, pengirim, tujuan, devisi, file_path, tanggal, no_surat) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Bind parameter untuk query
        $stmt->bind_param("ssssssss", $jenis_surat, $subjek, $pengirim, $tujuan, $devisi, $file_path, $tanggal, $no_surat);
        
        // Cek apakah query berhasil dijalankan
        if ($stmt->execute()) {
            return $this->db->insert_id;
        } else {
            // Jika gagal, tampilkan error query
            echo "Error insert surat: " . $this->db->error;  // Menampilkan error dari MySQL
            return false;
        }
    }

    // Method untuk mengambil data surat berdasarkan ID
    public function getSurat($id) {
        $stmt = $this->db->prepare("SELECT * FROM surat WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $surat = $result->fetch_assoc();
        $stmt->close();
        return $surat;
    }

    // Method untuk mengambil semua surat
    public function getSemuaSurat() {
    $result = $this->db->query("SELECT * FROM surat WHERE deleted_at IS NULL ORDER BY tanggal DESC");
    return $result->fetch_all(MYSQLI_ASSOC);
}

    // Method untuk update jumlah dilihat
    public function updateJumlahDilihat($id) {
        $stmt = $this->db->prepare("UPDATE surat SET jumlah_dilihat = jumlah_dilihat + 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    // Method untuk update jumlah jumlah di download
    public function updateJumlahDownload($id) {
        $stmt = $this->db->prepare("UPDATE surat SET jumlah_download = jumlah_download + 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }    

    // Method untuk menghapus surat dan file fisiknya
    public function deleteSuratPermanen($id) {
        // Ambil informasi file dari database
        $stmt = $this->db->prepare("SELECT file_path FROM surat WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $surat = $result->fetch_assoc();
        $stmt->close();

        if (!$surat) {
            return false; // Surat tidak ditemukan
        }

        $file_path = $surat['file_path'];

        // Hapus data surat dari database
        $stmt = $this->db->prepare("DELETE FROM surat WHERE id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();

        if ($result) {
            // Hapus file fisik jika ada
            if (file_exists($file_path)) {
                if (!unlink($file_path)) {
                    // Gagal menghapus file, tapi data sudah dihapus dari DB
                    // Anda bisa log error di sini jika perlu
                    return false;
                }
            }
            return true; // Berhasil menghapus data dan file (atau file tidak ada)
        } else {
            return false; // Gagal menghapus data dari database
        }
    }

    public function getSuratByDevisi($devisi, $filters = []) {
        $query = "SELECT * FROM surat WHERE devisi = ? AND deleted_at IS NULL";
        $params = [$devisi];
        $types = "s";
    
        if (!empty($filters['tanggal'])) {
            $query .= " AND tanggal = ?";
            $params[] = $filters['tanggal'];
            $types .= "s";
        }
    
        if (!empty($filters['jenis_surat'])) {
            $query .= " AND jenis_surat = ?";
            $params[] = $filters['jenis_surat'];
            $types .= "s";
        }

        if (!empty($filters['no_surat'])) {
            $query .= " AND no_surat LIKE ?";
            $params[] = '%' . $filters['no_surat'] . '%';
            $types .= "s";
        }
        
        if (!empty($filters['pengirim'])) {
            $query .= " AND pengirim LIKE ?";
            $params[] = '%' . $filters['pengirim'] . '%';
            $types .= "s";
        }
        
        if (!empty($filters['nama_file'])) {
            $query .= " AND file_path LIKE ?";
            $params[] = '%' . $filters['nama_file'] . '%';
            $types .= "s";
        }

        // Sorting Jumlah Dilihat (opsional)
        if (!empty($filters['sort_jumlah_dilihat']) && in_array($filters['sort_jumlah_dilihat'], ['asc', 'desc'])) {
        $query .= " ORDER BY jumlah_dilihat " . strtoupper($filters['sort_jumlah_dilihat']);
        } else {
        $query .= " ORDER BY tanggal DESC"; // default
        }

        $stmt = $this->db->prepare($query);
    
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
    
        $stmt->execute();
        $result = $stmt->get_result();
    
        $surat = [];
        while ($row = $result->fetch_assoc()) {
            $surat[] = $row;
        }
    
        return $surat;
    }

    // Method untuk menghapus surat (pindah ke sampah)
    public function deleteSurat($id, $user_id = null) {
    if ($user_id) {
        $stmt = $this->db->prepare("UPDATE surat SET deleted_at = NOW(), deleted_by = ? WHERE id = ?");
        $stmt->bind_param("ii", $user_id, $id);
    } else {
        $stmt = $this->db->prepare("UPDATE surat SET deleted_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $id);
    }
    return $stmt->execute();
}

    // Method untuk memulihkan surat dari sampah
    public function restoreSurat($id) {
        $stmt = $this->db->prepare("UPDATE surat SET deleted_at = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // Method untuk mendapatkan surat yang ada di sampah
   public function getDeletedSurat() {
    $query = "SELECT s.*, u.username as deleted_by_name 
              FROM surat s 
              LEFT JOIN users u ON s.deleted_by = u.id 
              WHERE s.deleted_at IS NOT NULL 
              ORDER BY s.deleted_at DESC";
              
    $result = $this->db->query($query);
    if (!$result) {
        error_log("Database error: " . $this->db->error);
        return false;
    }
    return $result->fetch_all(MYSQLI_ASSOC);
}

    // Method untuk membersihkan sampah yang sudah lebih dari 30 hari
    public function cleanupTrash() {
        $query = "SELECT id, file_path FROM surat 
                  WHERE deleted_at IS NOT NULL 
                  AND deleted_at < DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $result = $this->db->query($query);
        $old_trash = $result->fetch_all(MYSQLI_ASSOC);

        foreach ($old_trash as $surat) {
            // Hapus file fisik
            if (file_exists($surat['file_path'])) {
                unlink($surat['file_path']);
            }
            // Hapus dari database
            $stmt = $this->db->prepare("DELETE FROM surat WHERE id = ?");
            $stmt->bind_param("i", $surat['id']);
            $stmt->execute();
            $stmt->close();
        }

        return count($old_trash);
    }

}
?>
