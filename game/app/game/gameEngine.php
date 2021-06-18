<?php
    session_start();
    if (!isset($_SESSION['nickname']) || !isset($_SESSION['authCode'])) {
        session_destroy();
        header("Location: ../../index.php");
        exit();
    } else {
        @include_once "../../user/loggedCheck.php";
    }
    if (!isset($_SESSION['serverName']) || !isset($_SESSION['serverName']) || !isset($_GET['action'])) {
        echo "error 1";
        exit();
    }
    $action = intval($_GET['action']);
    include_once "../../../base.php";
    $connection = new mysqli($db_host, $db_user, $db_password, $db_name);
    if ($connection -> connect_errno > 0) {
        echo "error";
        exit();
    }
    switch ($action) {
        case 0:
            //getGameData
            $sql = "SELECT name, playersNicks, whosTour, timeout, lastAction, shipsP1, shipsP2, gameShips FROM games WHERE name = ?";
            $stmt = $connection -> prepare($sql);
            $stmt -> bind_param("s", $_SESSION['serverName']);
            $stmt -> execute();
            $stmt -> store_result();
            $stmt -> bind_result($name, $playersNicks, $whosTour, $timeout, $lastAction, $shipsP1, $shipsP2, $gameShips);
            $stmt -> fetch();
            $let = explode(";",$playersNicks);
            $me;
            if ($let[0] != $_SESSION['nickname']) {
                $shipsP1 = str_replace("1","0", $shipsP1);
            } else {
                $me = 0;
            }
            if ($let[1] != $_SESSION['nickname']) {
                $shipsP2 = str_replace("1","0", $shipsP2);
            } else {
                $me = 1;
            }
            echo $name.";;;".$playersNicks.";;;".$whosTour.";;;".$timeout.";;;".$lastAction.";;;".$shipsP1.";;;".$shipsP2.";;;".$gameShips.";;;".$me.";;;".time();
        break;
        case 1:
            //shoting
            $cord = intval($_GET['cord']);
            if (!empty($cord) && $cord > -1 && $cord < 100) {
                $playersNicks; $whosTour; $ships= array();
                $sql = "SELECT playersNicks, whosTour, shipsP1, shipsP2 FROM games WHERE name = ?";
                $stmt = $connection -> prepare($sql);
                $stmt -> bind_param("s", $_SESSION['serverName']);
                $stmt -> execute();
                $stmt -> store_result();
                $stmt -> bind_result($playersNicks, $whosTour, $ships[0], $ships[1]);
                $stmt -> fetch();
                $stmt->close();
                $playersNicks = explode(";",$playersNicks);
                if ($playersNicks[$whosTour] == $_SESSION["nickname"]) {
                    $target;
                    switch ($whosTour) {
                        case "0":
                            $target = 1;
                        break;
                        case "1":
                            $target = 0;
                        break;
                    }
                    $hit = false;
                    $ships[$target] = explode(";",$ships[$target]);
                    if ($ships[$target][$cord] == "1") {
                        $ships[$target][$cord] = 3;
                        $hit = true;
                    } else if ($ships[$target][$cord] == "0") {
                        $ships[$target][$cord] = 2;
                    }
                    $ships[$target] = implode(";",$ships[$target]);
                    $dateNow = time();
                    if (!$hit) {
                        if ($whosTour == "0") {
                            $whosTour = "1";
                        } else {
                            $whosTour = "0";
                        }
                    }
                    $sql = "UPDATE games SET shipsP1 = ?, shipsP2 = ?, lastAction = ?, whosTour = ? WHERE name = ?";
                    $stmt = $connection -> prepare($sql);
                    $stmt -> bind_param("ssdss", $ships[0],$ships[1],$dateNow,$whosTour,$_SESSION['serverName']);
                    $stmt -> execute();
                    $stmt->close();
                }
            }
        break;
    }
    mysqli_close($connection);
?>