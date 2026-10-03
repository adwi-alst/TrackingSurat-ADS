<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = trim($_POST['email']);

    // Hash password jika diisi
    $hashedPassword = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

    if ($hashedPassword && !empty($email)) {
        $query = "UPDATE users SET username = ?, password = ?, email = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("sssi", $username, $hashedPassword, $email, $id);
    } elseif ($hashedPassword && empty($email)) {
        $query = "UPDATE users SET username = ?, password = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ssi", $username, $hashedPassword, $id);
    } elseif (!$hashedPassword && !empty($email)) {
        $query = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("ssi", $username, $email, $id);
    } else {
        $query = "UPDATE users SET username = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->bind_param("si", $username, $id);
    }

    if ($stmt->execute()) {
        header("Location: formregister&status=success");
    } else {
        header("Location: formregister&status=error");
    }
    exit;
}
?>
