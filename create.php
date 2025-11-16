<?php

    require 'includes/auth.php';

    $message = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST"){
        if(!empty($_POST['title'])  && !empty($_POST['content'])){
            $sql = "INSERT INTO posts (title, content) VALUES (:title, :content)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':title', $_POST['title']);  
            $stmt->bindParam(':content', $_POST['content']);

            if ($stmt->execute()){
                header("Location: index.php");
                exit;
            } else{
                $message = 'Error Creating a Post!';
            }
        } else{
            $message = 'Please fill all the fields!';
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