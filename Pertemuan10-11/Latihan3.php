<?php
$con = mysqli_connect("localhost", "root", ""); 
mysqli_select_db($con, "lat_dbase"); 

$sql = "CREATE TABLE tbl_mhs (
    mhsID int NOT NULL AUTO_INCREMENT,
    PRIMARY KEY(mhsID),
    FirstName varchar(15),
    LastName varchar(15),
    Age int
)";

mysqli_query($con, $sql) or die("Gagal membuat tabel: " . mysqli_error($con));
echo "Tabel tbl_mhs berhasil dibuat";
?>