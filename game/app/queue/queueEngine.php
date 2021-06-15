<?php
    session_start();
    if (!isset($_SESSION['serverName']) || !isset($_GET['action'])) {
        echo "error 1";
        exit();
    }
    $action = intval($_GET['action']);
    // 0 = wczytanie 1 = opuszczenie gry 2 wyrzucenie gracza 3 start gry
    if ($action != 0 && $action != 1 && $action != 2 && $action != 3) {
        echo "error 2";
        exit();
    }
    include_once "../../../base.php";
    $connection = new mysqli($db_host, $db_user, $db_password, $db_name);
    switch ($action) {
        case 0:
            $players;
            $sql = "SELECT name, status, playersNicks, privacy, players FROM games WHERE BINARY name = BINARY ?";
            $stmt = $connection -> prepare($sql);
            $stmt -> bind_param("s", $_SESSION['serverName']);
            $stmt -> execute();
            $stmt -> store_result();
            $rows = $stmt -> num_rows;
            $stmt -> bind_result($name, $status, $playersNicks, $privacy, $playersINT);
            $stmt -> fetch();
            if ($rows == 1) {
                $players = explode(";", $playersNicks);
                echo $name.";".$status.";".$privacy.";".$playersINT.";".$players[0];
            } else {
                echo "error 3";
                $stmt -> close();
                mysqli_close($connection);
                exit();
            }
            $stmt -> close();
            echo ";;;"; // great separator;
            $sql = "SELECT nickname, descryption, avatar, Sgames, SgamesWin, SgamesLose FROM users WHERE BINARY nickname = BINARY ?";
            $stmt = $connection -> prepare($sql);
            foreach ($players as $key) {
                if ($key != "" && !empty($key)) {
                    $stmt -> bind_param("s", $key);
                    $stmt -> execute();
                    $stmt -> store_result();
                    $stmt -> bind_result($nickname, $descryption, $avatar, $Sgames, $SgamesWin, $SgamesLose);
                    $stmt -> fetch();
                    if ($avatar == "" || empty($avatar)) {
                        $avatar = "false";
                    }
                    echo (($nickname == $_SESSION['nickname']) ? "true" : "false").";".$nickname.";".$descryption.";".$Sgames.";".$SgamesWin.";".$SgamesLose.";".$avatar.";;";
                }
            }
            $stmt -> close();
        break;
        case 1:
            $sql = 'SELECT playersNicks, status FROM games WHERE BINARY name = BINARY ?';
            $stmt = $connection -> prepare($sql);
            $stmt -> bind_param("s", $_SESSION['serverName']);
            $stmt -> execute();
            $stmt -> store_result();
            $rows = $stmt -> num_rows;
            if ($rows != 1) {
                echo "error 4";
                mysqli_close($connection);
                exit();
            }
            $stmt -> bind_result($playersNicks, $status);
            $stmt -> fetch();
            if ($status != "1") {
                echo "error 5";
                mysqli_close($connection);
                exit();
            }
            $playersNicks = explode(";", $playersNicks);
            if ($playersNicks[0] == $_SESSION['nickname']) {
                if ($playersNicks[1] == "") {
                    $playersNicks = ";";
                } else {
                    $playersNicks = $playersNicks[1].";";        
                }
            } else if ($playersNicks[1] == $_SESSION['nickname']) {
                $playersNicks = $playersNicks[0].";";
            } else {
                echo "error 6";
                mysqli_close($connection);
                exit();
            }
            $stmt -> close();
            $sql = 'UPDATE games SET playersNicks = ?, players = players - 1 WHERE BINARY name = BINARY ?';
            $stmt = $connection -> prepare($sql);
            $stmt -> bind_param("ss", $playersNicks, $_SESSION['serverName']);
            $stmt -> execute();
            $stmt -> close();
            $sql = "UPDATE users SET inGame = 0 WHERE BINARY nickname = BINARY ?";
            $stmt = $connection -> prepare($sql);
            $stmt -> bind_param("s", $_SESSION['nickname']);
            $stmt -> execute();
            $stmt -> close();
        break;
        case 2:
            $sql = 'SELECT playersNicks, status FROM games WHERE BINARY name = BINARY ?';
            $stmt = $connection -> prepare($sql);
            $stmt -> bind_param("s", $_SESSION['serverName']);
            $stmt -> execute();
            $stmt -> store_result();
            $rows = $stmt -> num_rows;
            if ($rows != 1) {
                echo "error 4";
                mysqli_close($connection);
                exit();
            }
            $stmt -> bind_result($playersNicks, $status);
            $stmt -> fetch();
            if ($status != "1") {
                echo "error 5";
                mysqli_close($connection);
                exit();
            }
            $kickedNick;
            $playersNicks = explode(";", $playersNicks);
            if ($playersNicks[0] != $_SESSION['nickname']) {
                echo "error 6";
                mysqli_close($connection);
                exit();
            } else if ($playersNicks[1] != "") {
                $kickedNick = $playersNicks[1];
                $playersNicks = $playersNicks[0].";";
            } else {
                echo "error 6";
                mysqli_close($connection);
                exit();
            }
            $stmt -> close();
            $sql = 'UPDATE games SET playersNicks = ?, players = players - 1 WHERE BINARY name = BINARY ?';
            $stmt = $connection -> prepare($sql);
            $stmt -> bind_param("ss", $playersNicks, $_SESSION['serverName']);
            $stmt -> execute();
            $stmt -> close();
            $sql = "UPDATE users SET inGame = 0 WHERE BINARY nickname = BINARY ?";
            $stmt = $connection -> prepare($sql);
            $stmt -> bind_param("s", $kickedNick);
            $stmt -> execute();
            $stmt -> close();
        break;
        case 3:
            $sql = 'SELECT playersNicks, status, players FROM games WHERE BINARY name = BINARY ?';
            $stmt = $connection -> prepare($sql);
            $stmt -> bind_param("s", $_SESSION['serverName']);
            $stmt -> execute();
            $stmt -> store_result();
            $rows = $stmt -> num_rows;
            if ($rows != 1) {
                echo "error 4";
                mysqli_close($connection);
                exit();
            }
            $stmt -> bind_result($playersNicks, $status, $players);
            $stmt -> fetch();
            $playersNicks = explode(";", $playersNicks);
            if ($status != "1") {
                echo "error 5";
                mysqli_close($connection);
                exit();
            }
            if ($playersNicks[0] != $_SESSION['nickname']) {
                echo "error 6";
                mysqli_close($connection);
                exit();
            }
            if ($players != "2") {
                echo "error 7";
                mysqli_close($connection);
                exit();
            }
            $stmt -> close();
            $sql = 'UPDATE games SET status = 2 WHERE BINARY name = BINARY ?';
            $stmt = $connection -> prepare($sql);
            $stmt -> bind_param("s", $_SESSION['serverName']);
            $stmt -> execute();
            $stmt -> close();
        break;
    }
    mysqli_close($connection);
?>