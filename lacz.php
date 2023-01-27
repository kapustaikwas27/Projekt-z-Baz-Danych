<?php
    $conn = oci_connect("mz438836", "baza123", "//labora.mimuw.edu.pl/LABS");

    if (!$conn) {
        echo "oci_connect failed\n";
        $e = oci_error();
        echo $e['message'];
    }
?>
