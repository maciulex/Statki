<?php
    session_start();
    if (!isset($_SESSION['nickname']) || !isset($_SESSION['authCode'])) {
        session_destroy();
        header("Location: ../../index.php");
        exit();
    } else {
        @include_once "../../user/loggedCheck.php";
    }
    if (!isset($_SESSION['serverName']) || !isset($_GET['data'])) {
        echo "error 1";
        exit();
    }
    $action = intval($_GET['action']);
    include_once "../../../base.php";
    $connection = new mysqli($db_host, $db_user, $db_password, $db_name);
   
    mysqli_close($connection);
?>