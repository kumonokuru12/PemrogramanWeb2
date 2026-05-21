<?php

include 'koneksi.php';

$nim      = $_POST['nim'];
$nama     = $_POST['nama'];
$jurusan  = $_POST['jurusan'];
$alamat   = $_POST['alamat'];
$no_telp  = $_POST['no_telp'];

$query = "INSERT INTO mahasiswa 
          (nim, nama, jurusan, alamat, no_telp)
          VALUES
          ('$nim', '$nama', '$jurusan', '$alamat', '$no_telp')";

if(mysqli_query($conn, $query)){

    echo "
    <script>
        alert('Data berhasil disimpan');
        window.location='index.php';
    </script>
    ";

}else{

    echo "Gagal simpan data";

}

?>