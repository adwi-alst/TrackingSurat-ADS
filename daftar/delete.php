<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Pastikan ID pengguna yang akan dihapus dikirimkan
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        
        // Query untuk menghapus data berdasarkan ID unik
        $query = "DELETE FROM users WHERE id = ?";
        
        // Persiapkan statement dan bind parameter
        $stmt = $db->prepare($query);
        $stmt->bind_param("i", $id);
        
        // Eksekusi query dan cek apakah berhasil
        if ($stmt->execute()) {
           header("Location: formregister&status=delete_success");
      } else {
          header("Location: formregister&status=delete_failed");
      }
        exit;
        
    }
}
?>
