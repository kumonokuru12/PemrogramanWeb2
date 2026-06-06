<?php

include "koneksi.php";

$query = "SELECT * FROM userr";

$result = mysqli_query($conn,$query);

if(!$result){
    die("Error Query : ".mysqli_error($conn));
}

while($data = mysqli_fetch_assoc($result)){
    echo $data['nama']."<br>";
}

?>