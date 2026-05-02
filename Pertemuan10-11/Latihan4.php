<?php 
$con = mysqli_connect("localhost", "root", ""); 
mysqli_select_db($con, "lat_dbase"); 

$hasil = mysqli_query($con, "SELECT * FROM tbl_mhs"); 

echo "<h3>Data Mahasiswa:</h3>";
while($data = mysqli_fetch_array($hasil)) {
    echo $data['FirstName'] . " " . $data['LastName'] . " - " . $data['Age'] . "<br>"; 
}

$hit = mysqli_num_rows($hasil);
echo "<br>Jumlah record: $hit";
?>