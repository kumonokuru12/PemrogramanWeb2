<?php
$conn = mysqli_connect("localhost","root","","dbtokoabc");

// INSERT
mysqli_query($conn, "INSERT INTO TbUser(nama)
VALUES('Arif')");

// SELECT
$data = mysqli_query($conn, "SELECT * FROM TbUser");

while($row = mysqli_fetch_assoc($data)){
    echo $row['nama'];
}
?>