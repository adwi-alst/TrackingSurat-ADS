<?php
class Tracking {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getTracking($surat_id) {
    $stmt = $this->db->prepare("SELECT * FROM tracking WHERE surat_id = ? ORDER BY tanggal DESC");
    $stmt->bind_param("i", $surat_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

   public function updateStatus($surat_id, $status, $keterangan = '') {
    $tanggal = date('Y-m-d H:i:s');
    $stmt = $this->db->prepare("INSERT INTO tracking (surat_id, status, keterangan, tanggal) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $surat_id, $status, $keterangan, $tanggal);
    if ($stmt->execute()) {
        return true;
    } else {
        echo "Error: " . $this->db->error;
        return false;
    }
}
    
}
?>
