<?php
    session_start();
    if (isset($_SESSION['nickname'])) {
        header("Location: mainLogged/index.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="pl">
    <head>
        <meta charset="utf-8">
        <link href="styles/index/index.css" rel="stylesheet">
        <script src="user/indexManagment/app.js"></script>
    </head>
    <body>
        <div id="indexMainContainer">
            <div id="mainContent">
                <h1>Witaj</h1>
                <hr>
                <nav>
                    <div class="selected">Strona główna</div>|<div onclick='load("Logging")'>Logowanie</div>|<div onclick='load("Register")'>Rejestracja</div>
                </nav>
                <hr>
                <main>
                    Darmowa gra w Statki!
                </main>
            </div>
        </div>
    </body>
    <script>
        let error = <?php echo ((isset($_SESSION['error'])) ? '"'.$_SESSION['error'].'"' : "undefined");?>;
        if (error != undefined) {
            alert(error);
        }
    </script>
</html>
<?php
    if (isset($_SESSION['error'])) {
        unset($_SESSION['error']);
    }
?>