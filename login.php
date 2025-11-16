<?php
    require 'includes/db.php';
    $message = '';

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(!empty($_POST['username'] && !empty($_POST['password']))) {
            $query = "SELECT id, username, password FROM users WHERE username = :username";

            $stmt = $conn->prepare($query);
            $stmt ->bindParam(':username', $_POST['username']);
            $stmt -> execute();

            $user = $stmt -> fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($_POST['password'], $user['password'])){
                $_SESSION['username'] = $user['id'];
                header("Location: index.php");
                exit;
            } else{
                $message = " Sorry, those credentials don't match.";
            }
        } else{
            $message = "Please fill username and password.";
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Login</title>
        <link rel="stylesheet" type="text/css" href="css/style.css">
    </head>
    <body>
        <div class="container">
            <h1>Login</h1>
            <?php if (!empty($message)): ?>
                <p class ="message"><?= $message ?></p> 
            <?php endif; ?>

            <form action="login.php" method ="POST">
                <input name="username" type="text" placeholder="USERNAME" required>
                <input name="password" type="password" placeholder="PASSWORD" required>
                <input type="submit" value="Login" required>
            </form>

            <p> Not a member? <a href = "register.php" > Register here</a>.</p>
        </div>
    </body>
</html>