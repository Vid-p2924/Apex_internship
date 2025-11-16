<?php
    require 'includes/db.php';
    $messages = '';

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(!empty($_POST['username'] && !empty($_POST['password']))){
            $username = $_POST['username'];
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

            try{
                $sql = "INSERT INTO users(username, password) VALUES (:username, :password)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':password', $password);

                if ($stmt->execute()) {
                    $message = 'Successfully created new user';
                } else {
                    $message = 'Sorry, there must have been an issue creating your account';
                }
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) {
                    $message = 'That username is already taken.';
                } else {
                    $message = 'An error occurred: ' . $e->getMessage();
                }
            }
        } else {
            $message = 'Please fill out all fields.';
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