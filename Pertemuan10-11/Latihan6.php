<?php
$con = mysqli_connect("localhost", "root", "");
if (!$con) { die('Koneksi Gagal: ' . mysqli_connect_error()); }

mysqli_select_db($con, "lat_dbase");

$sql = "INSERT INTO tbl_mhs (FirstName, LastName, Age) 
        VALUES ('$_POST[firstname]', '$_POST[lastname]', '$_POST[age]')";

if (mysqli_query($con, $sql)) {
    echo "1 record added! <br>";
    echo "<a href='Latihan4.php'>Lihat Hasil Data</a>";
} else {
    echo "Error: " . mysqli_error($con);
}

mysqli_close($con);
?>