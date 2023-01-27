<?php
    session_start();
    putenv("NLS_LANG=polish_poland.utf8");

    if (isset($_POST['przycisk_logowania'])) {
        include('lacz.php');

        $przydomek = $_POST['przydomek'];
        $haslo = $_POST['haslo'];

        if(!empty($przydomek) && !empty($haslo)){
            $stmt = oci_parse($conn, "SELECT * FROM uzytkownik WHERE przydomek = '$przydomek' AND haslo = '$haslo'");
            oci_execute($stmt, OCI_NO_AUTO_COMMIT);

            if (($wiersz = oci_fetch_array($stmt, OCI_BOTH))) {
                $_SESSION["id"] = $wiersz['ID'];
                $_SESSION["przydomek"] = $wiersz['PRZYDOMEK'];

                header("Location: kalendarz.php");
            } else {
                $msg = "Nie istnieje użytkownik o takich danych logowania.";
                header("Location: index.php?error=".$msg);
                exit;
            }
        } else {
            $msg = "Uzupełnij wszystkie pola.";
            header ("Location: index.php?error=".$msg);
            exit;
        }
    }
?>

<!DOCTYPE html>
<html lang="pl-PL" dir="ltr">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title></title>
</head>
<body>
<style>
    body { font: 1rem monospace; }
    button[type = submit] { border-color: green; background-color: green; }
    button[type = submit]:hover{ border-color: green; background-color: green;  }
</style>
<div class="container">
    <div class="row">
        <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 mt-5 pt-3 pb-3 bg-white from-wrapper">
            <div class="container">
                <h3>Logowanie</h3>
                <h5>Do spersonalizowanego kalendarza Imprez na Orientację</h5>
                <hr>
                <?php if (isset($_REQUEST['success'])): ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $_REQUEST['success']; ?>
                    </div>
                <?php endif; ?>
                <form class="" method="post">
                    <div class="form-group">
                        <label for="przydomek">Przydomek</label>
                        <input type="text" class="form-control" name="przydomek" id="przydomek" value="">
                    </div>
                    <div class="form-group">
                        <label for="haslo">Hasło</label>
                        <input type="password" class="form-control" name="haslo" id="haslo" value="">
                    </div>
                    <?php if (isset($_REQUEST['error'])): ?>
                        <div class="col-12">
                            <div class="alert alert-danger" role="alert">
                                <?php echo $_REQUEST['error']; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-12 col-sm-4">
                            <button type="submit" name="przycisk_logowania" class="btn btn-primary">Loguj</button>
                        </div>
                        <div class="col-12 col-sm-8 text-right">
                            <a href="rejestruj.php">Jesteś po raz pierwszy?</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div style="text-align: center;">
    <img src="http://skleppttk.pl/wp-content/uploads/2020/04/ino.png" alt="Odznaka InO" width="200" height="200">
    <img src="https://naszaszkoladomowa.pl/wp-content/uploads/2017/08/pttk_logo.jpg" alt="Logo PTTK" width="200" height="200">
    <img src="https://akinokrakow.files.wordpress.com/2022/03/275071248_5058894090799853_260576110565438726_n.jpg" alt="Jubileusz InO" width="200" height="200">
</div>

</body>
</html>
