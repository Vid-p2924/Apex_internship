<?php
require 'includes/db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password_raw = trim($_POST['password']);

    if (empty($username) || empty($password_raw)) {
        $message = 'Please fill out all fields.';
    } elseif (strlen($username) > 50) {
        $message = 'Username cannot be longer than 50 characters.';
    } elseif (strlen($password_raw) < 6) {
        $message = 'Password must be at least 6 characters long.';
    } else {
        $password = password_hash($password_raw, PASSWORD_DEFAULT);

        try {
            $sql_count = "SELECT COUNT(*) FROM users";
            $count_stmt = $conn->query($sql_count);
            $user_count = $count_stmt->fetchColumn();

            $role = ($user_count == 0) ? 'admin' : 'editor';
            //$role = 'editor';
            $sql = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':role', $role);

            if ($stmt->execute()) {
                $message = 'Successfully created new user.';
            } else {
                $message = 'Sorry, there was an issue creating your account.';
            }

        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $message = 'That username is already taken.';
            } else {
                $message = 'An error occurred: ' . $e->getMessage();
            }
        }
    }
}
?>



<!DOCTYPE html>
<html>
    <head>
        <title>Register</title>
        <link rel="stylesheet" type="text/css" href="css/style.css">
    </head>
    <body>
         <div class="container">
            <h1>Register</h1>
            <?php if(!empty($message)): ?>
            <p class="message"><?= $message ?></p>
        <?php endif; ?>
        <form action="register.php" method="POST">
            <input name="username" type="text" placeholder="Enter your username" required>
            <input name="password" type="password" placeholder="Password" required>
            <input type="submit" value="Submit">
            </form>
            <p>Already a member? <a href="login.php">Log in here</a>.</p>
         </div>
    </body>
</html>