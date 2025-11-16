<?php
    require_once 'db.php';

    if (!isset($_SESSION['username'])){
        header("Location: login.php");
        exit;
    }
?>


