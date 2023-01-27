<?php
    session_start();
    putenv("NLS_LANG=polish_poland.utf8");
    putenv("NLS_DATE_FORMAT=YYYY-MM-DD");
    include('lacz.php');
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<script src="https://cdn.apple-mapkit.com/mk/5.x.x/mapkit.js"></script>
<style>
    #map {
        height: 777px;
    }
</style>
</head>
<body>
    <div id="map"></div>
<script>
    var MarkerAnnotation = mapkit.MarkerAnnotation, clickAnnotation;

    mapkit.init({
        authorizationCallback: function(done) {
            done("eyJhbGciOiJFUzI1NiIsInR5cCI6IkpXVCIsImtpZCI6IjQ1QTZRMktTWUoifQ.eyJpc3MiOiJEOTI3UkJQNko1IiwiaWF0IjoxNjcyNzYzNjg2LCJleHAiOjE5ODgzMzk2ODZ9.Jaoul6dBcMz9ioiufbfkt5ctfWlxPeFcnwymFEcSZvbw_kEVT5ottywt7iVWLCSNyuYAcg3Y1PK2uGe0Y7xasg");
        }
    });

    var map = new mapkit.Map("map");
        
    map.element.addEventListener("click", function(event) {
        if(!event.shiftKey) {
            return;
        }
        
        if(clickAnnotation) {
            map.removeAnnotation(clickAnnotation);
        }
        
        var coordinate = map.convertPointOnPageToCoordinate(new DOMPoint(event.pageX, event.pageY));
        clickAnnotation = new MarkerAnnotation(coordinate, {
            title: "Click!",
            color: "#c969e0"
        });
        map.addAnnotation(clickAnnotation);
    });

    var mGeocoder = new mapkit.Geocoder({ language: "pl", getsUserLocation: false });

    var dzisiaj = new Date();
    var annotations = [];
    var promises = [];

    <?php
        $id_uzytkownika = $_SESSION["id"];
        $data_pocz = $_SESSION["poczatek"];
        $data_kon = $_SESSION["koniec"];

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
            $p = strtotime($data_pocz);
            $q = strtotime($wiersz['DATA_POCZ']);
            $r = $q - $p;
            $kolor = "red";
		echo $data_pocz;
		echo $p;
            if ($r > 30 * 60 * 60 * 24) {
                $kolor = "green";
            } else if ($r > 7 * 60 * 60 * 24) {
                $kolor = "orange";
            }

            // synchronizacja wielu wyszukiwań przy użyciu Promise (wyszukiwania odbywają się współbieżnie)
            echo "
                promises.push(
                    new Promise(resolve => {
                        mGeocoder.lookup(\"".$wiersz['MIEJSCE']."\", (err, data) => {
                            if (err) {
                                resolve(false);
                            } else {
                                if (!data.results[0]) {
                                    resolve(false);
                                } else {
                                    console.log(data);
                                    var lat = data.results[0].coordinate.latitude;
                                    var lng = data.results[0].coordinate.longitude;
                                    var x = new mapkit.Coordinate(lat, lng);
                                    var annotacja_x = new MarkerAnnotation(x, { color: \"".$kolor."\", title: \"".$wiersz['NAZWA']."\" });
                                    annotations.push(annotacja_x);
                                    resolve(true);
                                }
                            }
                        })
                    })
                );\n";
        }
    ?>

    // po zakończeniu wszystkich wyszukiwań pokazujemy punkty na mapie
    Promise.all(promises).then(() => { map.showItems(annotations) });
</script>
</body>
</html>
