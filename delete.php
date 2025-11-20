<?php
    require 'includes/auth.php';

    if ($_SESSION['role'] !== 'admin') {
        ?>
        <!DOCTYPE html>
        <html>
            <head>
                <title>Access Denied</title>
                <link rel="stylesheet" type="text/css" href="css/style.css">
            </head>
            <body>
                <div class="container">
                    <p class="message">Access Denied. You do not have permission to delete Post.</p>
                    <a href="index.php" class="back-link">Back to Posts</a>
                </div>
            </body>
        </html>
        <?php
        exit;
    }

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
