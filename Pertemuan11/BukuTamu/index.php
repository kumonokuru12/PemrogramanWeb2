<?php
$conn = mysqli_connect("localhost", "root", "", "db_bukutamu");

if(!$conn){
    die("Koneksi gagal : " . mysqli_connect_error());
}

if(isset($_POST['simpan'])){

    $nama  = $_POST['nama'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $pesan = $_POST['pesan'];

    $query = "INSERT INTO buku_tamu(nama,email,no_hp,pesan)
              VALUES('$nama','$email','$no_hp','$pesan')";

    mysqli_query($conn, $query);

    echo "<script>alert('Data berhasil disimpan');</script>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buku Tamu</title>

    <style>

        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body{
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #4facfe, #00f2fe);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container{
            width: 400px;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        h2{
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        label{
            font-weight: bold;
        }

        input,
        textarea{
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        textarea{
            height: 100px;
            resize: none;
        }

        button{
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #4facfe;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover{
            background: #00c6fb;
        }

    </style>

</head>
<body>

<div class="container">

    <h2>Form Buku Tamu</h2>

    <form method="POST">

        <label>Nama</label>
        <input type="text" name="nama" placeholder="Masukkan nama" required>

        <label>Email</label>
        <input type="email" name="email" placeholder="Masukkan email" required>

        <label>No HP</label>
        <input type="text" name="no_hp" placeholder="Masukkan nomor HP" required>

        <label>Pesan</label>
        <textarea name="pesan" placeholder="Tulis pesan..." required></textarea>

        <button type="submit" name="simpan">
            Simpan
        </button>

    </form>

</div>

</body>
</html>