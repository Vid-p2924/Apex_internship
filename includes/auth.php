<?php
    session_start();
    require_once 'db.php';

    if (!isset($_SESSION['username'])){
        header("Location: login.php");
        exit;
    }
?>
