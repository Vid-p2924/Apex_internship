<?php
    require 'includes/auth.php';

    $id = $_GET['id'] ?? null;

    if ($id) {
        try {
            $sql = "DELETE FROM posts WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            die();
        }
    }

    header("Location: index.php");
    exit;
?>