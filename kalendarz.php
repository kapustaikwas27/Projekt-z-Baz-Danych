<!DOCTYPE html>
<html lang="pl-PL" dir="ltr">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js"></script>
    <title></title>
</head>
<body>
<style>
    body { height: 100%; padding: 0; margin: 0; overflow: scroll; font: 1rem monospace; }
    div { position: fixed; padding-left: 0%; }
    #W { width: 30%; height: 100%; top: 0; left: 0; }
    #E { width: 70%; height: 100%; top: 0; left: 30%; }

    .scrollable {
        height: 80%;
        overflow-y: scroll;
        border-bottom: 1px solid #ddd;
    }

    th {
        position: sticky;
        background-color: lightblue;
        z-index: 2;
        top: 0;
        text-transform: uppercase;
        border: solid 1px #777;
    }

    td {
        border: solid 1px #777;
    }

    input[type = submit] { border-color: green; background-color: green; }
    input[type = submit]:hover { border-color: green; background-color: green;  }
</style>
<div id="W">
    <?php
        session_start();
        putenv("NLS_LANG=polish_poland.utf8");
	putenv("NLS_DATE_FORMAT=YYYY-MM-DD");
        include('lacz.php');

        $stmt = oci_parse($conn, "SELECT * FROM typ");
        oci_execute($stmt, OCI_NO_AUTO_COMMIT);
    ?>
    <h3>Ustawienia</h3>
    <form method="post" action="">
    <table>
    <thead>
        <tr>
            <th>Nr</th>
            <th>Typ</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php
            while (($wiersz = oci_fetch_array($stmt, OCI_BOTH))) {
                $id_typu = $wiersz['ID'];

                echo "<tr>
                        <td>".$wiersz['ID'].".</td>
                        <td>".$wiersz['NAZWA']."</td>
                        <td><input type='checkbox' name='preferencje[]' value='$id_typu'></td>
                    </tr>\n";
            }
        ?>
    </tbody>
    </table>
    <br><br>
    <input type="submit" name="zatwierdzacz_typow" value="Zatwierdź" class="btn btn-primary">
    <br><br>
    <label for="poczatek">Data rozpoczęcia imprezy:</label>
    <input type="date" id="poczatek" name="poczatek" placeholder="yyyy-mm-dd">
    <label for="koniec">Data zakończenia imprezy:</label>
    <input type="date" id="koniec" name="koniec" placeholder="yyyy-mm-dd">
    <br><br>
    <input type="submit" name="zatwierdzacz_dat" value="Zatwierdź" class="btn btn-primary">
    <br><br>
    </form>
    <?php
        if (isset($_POST['zatwierdzacz_typow'])) {
            $id_uzytkownika = $_SESSION["id"];
            $stmt = oci_parse($conn, "DELETE FROM uzytkownik_typ WHERE id_uzytkownika = '$id_uzytkownika'");
            oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);

            if (!empty($_POST['preferencje'])) {
                foreach ($_POST['preferencje'] as $preferencja) {
                    $stmt = oci_parse($conn, "INSERT INTO uzytkownik_typ VALUES ('$id_uzytkownika', '$preferencja')");
                    oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
                }
            }
        }
    ?>
    <form action="mapa.php">
    <input type="submit" name="pokaznik" value="Pokaż na mapie" class="btn btn-primary">
    </form>
    <br>
    <h3>Wybrane</h3>
    <table class="tb">
    <thead>
        <tr>
            <th>Typ</th>
        </tr>
    </thead>
    <tbody>
        <?php
            $id_uzytkownika = $_SESSION["id"];
            $stmt = oci_parse($conn, "SELECT nazwa FROM uzytkownik_typ JOIN typ ON id_typu = id WHERE id_uzytkownika = '$id_uzytkownika'");
            oci_execute($stmt, OCI_NO_AUTO_COMMIT);

            while (($wiersz = oci_fetch_array($stmt, OCI_BOTH))) {
                echo "<tr>
                        <td>".$wiersz['NAZWA']."</td>
                    </tr>\n";
            }
        ?>
    </tbody>
    </table>
    <br>
    <form method="post" action="wyloguj.php">
    <input type="submit" name="wylogowywacz" value="Wyloguj" class="btn btn-primary">
    </form>
    <?php
        if (isset($_POST['wylogowywacz'])) {
            unset($_SESSION["id"]);
            unset($_SESSION["przydomek"]);
            header("Location: index.php");
        }
    ?>
</div>
<div id="E">
    <h3>Propozycje</h3>
    <div class="scrollable">
    <table class="tb">
    <thead>
        <tr>
            <th>Nr</th>
            <th>Data rozpoczęcia</th>
            <th>Data zakończenia</th>
            <th>Nazwa</th>
            <th>Miejsce</th>
            <th>Opis</th>
        </tr>
    </thead>
    <tbody>
    <?php
        if (isset($_POST['zatwierdzacz_dat'])) {
            $id_uzytkownika = $_SESSION["id"];
            $data_pocz = $_POST['poczatek'];
            $data_kon = $_POST['koniec'];
            $_SESSION["poczatek"] = $data_pocz;
            $_SESSION["koniec"] = $data_kon;

            $stmt = oci_parse($conn,
            "SELECT DISTINCT
                impreza.id,
                impreza.data_pocz,
                impreza.data_kon,
                impreza.nazwa,
                impreza.url_imprezy,
                impreza.miejsce,
                impreza.url_miejsca,
                impreza.opis
            FROM
                impreza
            JOIN
                impreza_typ
            ON
                impreza.id = impreza_typ.id_imprezy
            WHERE
                id_typu IN (SELECT id_typu FROM uzytkownik_typ JOIN typ ON id_typu = id WHERE id_uzytkownika = '$id_uzytkownika')
            AND
                data_pocz >= DATE '$data_pocz'
            AND
                data_kon <= DATE '$data_kon'
            ORDER BY
                data_pocz");

            oci_execute($stmt, OCI_NO_AUTO_COMMIT);

            while (($wiersz = oci_fetch_array($stmt, OCI_BOTH))) {
                echo "<tr>
                        <td>".$wiersz['ID']."</td>
                        <td>".$wiersz['DATA_POCZ']."</td>
                        <td>".$wiersz['DATA_KON']."</td>";

                if (!empty($wiersz['URL_IMPREZY'])) {
                    echo "<td><a href=\"".$wiersz['URL_IMPREZY']."\"</a>".$wiersz['NAZWA']."</td>";
                } else {
                    echo "<td>".$wiersz['NAZWA']."</td>";
                }

                if (!empty($wiersz['URL_MIEJSCA'])) {
                    echo "<td><a href=\"".$wiersz['URL_MIEJSCA']."\"</a>".$wiersz['MIEJSCE']."</td>";
                } else {
                    echo "<td>".$wiersz['MIEJSCE']."</td>";
                }

                echo "<td>".$wiersz['OPIS']."</td></tr>\n";
            }
        }
    ?>
    </tbody>
    </table>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>
