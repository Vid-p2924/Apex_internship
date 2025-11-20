<?php
    require 'includes/db.php';
    
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); 
    }

    $message = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if (empty($username) || empty($password)) {
            $message = "Please fill username and password.";
        } else {
            $query = "SELECT id, username, password, role FROM users WHERE username = :username";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];      
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];       

                header("Location: index.php");
                exit;

            } else {
                $message = "Sorry, those credentials don't match.";
            }
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