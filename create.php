<?php
    require 'includes/auth.php';



    if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor') {

        ?>

        <!DOCTYPE html>

        <html>

            <head>

                <title>Access Denied</title>

                <link rel="stylesheet" type="text/css" href="css/style.css">

            </head>

            <body>

                <div class="container">

                    <p class="message">Access Denied. You do not have permission to create a post.</p>

                    <a href="index.php" class="back-link">Back to Posts</a>

                </div>

            </body>

        </html>

        <?php

        exit;

    }

        $message = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);

        if (empty($title) || empty($content)) {
            $message = 'Please fill in all fields.';
        } elseif (strlen($title) > 255) {
            $message = 'Title cannot be longer than 255 characters.';
        } else {
            $sql = "INSERT INTO posts (title, content) VALUES (:title, :content)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':content', $content);

            if ($stmt->execute()) {
                header("Location: index.php");
                exit;
            } else {
                $message = 'Error creating post.';
            }
        }
    }
?>


<!DOCTYPE html>
<html>
    <head> 
        <title> CREATE A NEW POST </title>
        <link rel="stylesheet" type="text/css" href="css/style.css">
    </head>

    <body>
        <div class = "container">
            <a href="index.php" class="back-link">Back to Posts</a>
            <h1> CREATE A NEW POST </h1>

            <?php if (!empty($message)): ?>
                <p class = "message"> <?= $message ?></p>
            <?php endif; ?>

            <form action = "create.php" method = "POST">
                <input type = "text" name = "title" placeholder = "Post Title" required>
                <textarea name = "content"  placeholder = "Post Content" required></textarea>
                <input type = "submit" value = "Create Post">
            </form>
        </div> 
    </body>
</html>