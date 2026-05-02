<?php
$servername = 'localhost';
$dbusername = 'root';
$dbpassword = '';

$link = mysqli_connect("$servername", "$dbusername", "$dbpassword") 
        or die("Tidak dapat terhubung ke server");

if ($link) {
    echo "Koneksi Berhasil!";
}
?>