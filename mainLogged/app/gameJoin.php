<?php
    include_once "../../base.php";
    $mainIndexPath = "../../index.php";
    session_start();
    if (!isset($_SESSION['nickname']) || !isset($_SESSION['authCode'])) {
        session_destroy();
        header("Location: ../../index.php");
        exit();
    } else {
        @include_once "../../user/loggedCheck.php";
    }
    if (!isset($_GET['name']) || empty($_GET['name']) || strlen($_GET['name']) < 3 || trim($_GET['name'],";") != $_GET['name']) {
        $_SESSION['error'] = "Coś się nie udało! 1";
        header("Location: ../gameCreate.php");
        exit();
    }
    $connection = @new mysqli($db_host,$db_user,$db_password,$db_name);
    if ($connection -> connect_errno > 0) {
        $_SESSION['error'] = "Coś się nie udało! 2";
        header("Location: ../index.php");
        exit();
    } else {
        $sql = "SELECT inGame FROM users WHERE BINARY nickname = BINARY ?";
        $stmt = $connection -> prepare($sql);
        $stmt -> bind_param("s", $_SESSION['nickname']);
        $stmt -> execute();
        $stmt -> store_result();
        $stmt -> bind_result($inGame);
        $stmt -> fetch();
        if (intval($inGame) != 0) {
            $_SESSION['error'] = "Error";
            header('Location: ../index.php');
            $stmt -> close();
            mysqli_close($connection);
            exit();
        }
        $stmt -> close();
        $sql = "SELECT password, privacy, status, players, playersNicks, id FROM games WHERE BINARY name = BINARY ?";
        $stmt = $connection -> prepare($sql);
        $stmt -> bind_param("s", $_GET['name']);
        $stmt -> execute();
        $stmt -> store_result();
        $rows = $stmt -> num_rows;
        $stmt -> bind_result($password, $privacy, $status, $players, $playersNicks, $id);
        $stmt -> fetch();
        $stmt -> close();
        if (intval($status) == 4) {
            header('Location: ../../game/postGame.php?serverName='.$_GET['name']);
            mysqli_close($connection);
            exit();
        }
        if ($rows == 0) {
            mysqli_close($connection);
            $_SESSION['error'] = "Coś się nie udało! 3";
            header("Location: ../../index.php");
            exit();
        }
        if (intval($privacy) == 2) {
            if (!isset($_GET['password'])) {
                mysqli_close($connection);
                $_SESSION['error'] = "Coś się nie udało!";
                header("Location: ../../index.php");
                exit();
            } else if ($_GET['password'] != $password) {
                mysqli_close($connection);
                $_SESSION['error'] = "Coś się nie udało! 4";
                header("Location: ../../index.php");
                exit();
            }
        }
        if (intval($players) == 2) {
            mysqli_close($connection);
            $_SESSION['error'] = "Lobby jest pełne!";
            header("Location: ../../index.php");
            exit();
        }
        $playersNicks = explode(";", $playersNicks);
        if ($playersNicks[0] == "") {
            $playersNicks[0] = $_SESSION['nickname'];
        } else if ($playersNicks[1] == "") {
            $playersNicks[1] = $_SESSION['nickname'];
        } else {
            mysqli_close($connection);
            $_SESSION['error'] = "Coś się nie udało! 5";
            header("Location: ../../index.php");
            exit();
        }
        $playersNicks = implode(";", $playersNicks);
        $sql = "UPDATE games SET players = players+1, playersNicks = ? WHERE name = ?";
        $stmt = $connection -> prepare($sql);
        $stmt -> bind_param("ss", $playersNicks, $_GET['name']);
        $stmt -> execute();
        $stmt -> close();
        $sql = "UPDATE users SET inGame = ? WHERE nickname = ?";
        $stmt = $connection -> prepare($sql);
        $stmt -> bind_param("is", $id, $_SESSION['nickname']);
        $stmt -> execute();
        $stmt -> close();
        $_SESSION['serverName'] = $_GET['name'];
        mysqli_close($connection);
        header("Location: ../../game/gameQueue.php");
        exit();
    }
    mysqli_close($connection);
?>