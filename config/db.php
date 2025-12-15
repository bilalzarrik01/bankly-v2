<?php
$connect = mysqli_connect("localhost", "root", "", "bankly_v2", 3307);

if (!$connect) {
    die("Erreur connexion DB : " . mysqli_connect_error());
}
?>
