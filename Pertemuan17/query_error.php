<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "dbkampus"
);

try {

    $sql = "SELECT * FROM mahasiswa";

    $result = mysqli_query($conn, $sql);

    echo "Query berhasil dijalankan";

}
catch(mysqli_sql_exception $e)
{
    echo "Terjadi Error Database<br>";
    echo $e->getMessage();
}

?>