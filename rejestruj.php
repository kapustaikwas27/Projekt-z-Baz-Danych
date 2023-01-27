<?php
    session_start();
    putenv("NLS_LANG=polish_poland.utf8");

    if (isset($_POST['przycisk_rejestracji'])) {
        include('lacz.php');

        $przydomek = $_POST['przydomek'];
        $stmt = oci_parse($conn, "SELECT * FROM uzytkownik WHERE przydomek = '$przydomek'");
        oci_execute($stmt, OCI_NO_AUTO_COMMIT);

        if (($row = oci_fetch_array($stmt, OCI_BOTH))) {
            $msg = "Instnieje już użytkownik o takim przydomku. Proszę wymyślić inny przydomek.";
            header("Location: rejestruj.php?error=".$msg);
            exit;
        } else {
            $haslo = $_POST['haslo'];
            $potwierdzenie_hasla = $_POST['potwierdzenie_hasla'];

            if (!empty($przydomek) && !empty($haslo) && !empty($potwierdzenie_hasla)) {
                if ($haslo == $potwierdzenie_hasla) {
                    $stmt = oci_parse($conn, "INSERT INTO uzytkownik(przydomek, haslo) VALUES ('$przydomek', '$haslo')");
                    oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
                    $msg = "Rejestracja zakończyła się sukcesem.";
                    header("Location: index.php?success=".$msg);
                    exit;
                } else {
                    $msg = "Podano dwa różne hasła.";
                    header("Location: rejestruj.php?error=".$msg);
                    exit;
                }
            } else {
                $msg = "Uzupełnij wszystkie pola.";
                header("Location: rejestruj.php?error=".$msg);
                exit;
            }
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
                <h3>Rejestracja</h3>
                <h5>Do spersonalizowanego kalendarza Imprez na Orientację</h5>
                <hr>
                <form class=""  method="post">
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="przydomek">Przydomek</label>
                                <input type="text" class="form-control" name="przydomek" id="przydomek" value="">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label for="password">Hasło</label>
                                <input type="password" class="form-control" name="haslo" id="haslo" value="">
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="form-group">
                                <label for="potwierdzenie_hasla">Potwierdź hasło</label>
                                <input type="password" class="form-control" name="potwierdzenie_hasla" id="potwierdzenie_hasla" value="">
                            </div>
                        </div>
                        <?php if (isset($_REQUEST['error'])): ?>
                            <div class="col-12">
                                <div class="alert alert-danger" role="alert">
                                    <?php echo $_REQUEST['error']; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="row">
                        <div class="col-12 col-sm-4">
                            <button type="submit" name="przycisk_rejestracji" class="btn btn-primary">Rejestruj</button>
                        </div>
                        <div class="col-12 col-sm-8 text-right">
                            <a href="index.php">Chcę się zalogować.</a>
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
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>
