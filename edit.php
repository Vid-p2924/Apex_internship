<?php
    require 'includes/auth.php';
    
    $message = '';
    $id = $_GET['id'] ?? null;

    if (! $id) {
        header("Location: index.php");
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM  posts WHERE id = :id");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        echo 'Post not found';
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if(!empty($_POST['title']) && !empty($_POST['content'])) {
            $sql = "UPDATE posts SET title = :title, content = :content WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':title', $_POST['title']);
            $stmt->bindParam(':content', $_POST['content']);
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                header("Location: index.php");
                exit;
            } else {
                $message = 'Error updating post.';
            }
        }   else {
            $message = 'Please fill in all fields.';
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Edit Post</title>
        <link rel="stylesheet" type="text/css" href="css/style.css">
    </head>
    <body>
        <div class="container">
            <a href="index.php" class="back-line">back to posts</a>
            <h1>Edit Post</h1>
             
            <?php if(!empty($message)): ?>
                <p class="message"><?= $message ?></p>
            <?php endif; ?> 

            <form action="edit.php?id=<?= $id ?>" method="POST">
                <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
                <textarea name="content" required><?= htmlspecialchars($post['content']) ?></textarea>
                <input type="submit" value="Update Post">
            </form>
        </div>
    </body>
</html>