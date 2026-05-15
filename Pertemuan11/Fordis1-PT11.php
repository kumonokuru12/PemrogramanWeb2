<?php
$conn = mysqli_connect("localhost","root","","dbtokoabc");

$data = mysqli_query($conn, "SELECT * FROM TbUser");

while($row = mysqli_fetch_assoc($data)){
    echo $row['nama'];
}
?>