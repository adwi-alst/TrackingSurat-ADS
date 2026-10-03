<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        if (!$stmt) {
            die("Prepare failed: " . $db->error);
        }

        $stmt->bind_param("ss", $username, $hashed_password);

        if ($stmt->execute()) {
            $stmt->close();
            // 🔁 Redirect ke formregister.php setelah berhasil
             header("Location: formregister&status=add_success");           
        }  else {
             header("Location: formregister&status=add_failed");
        }
            exit;
    } else {
        die("Username atau password tidak boleh kosong.");
    }
}
?>
