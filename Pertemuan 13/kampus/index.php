<!DOCTYPE html>
<html>
<head>
    <title>Form Input Data Mahasiswa</title>

    <style>

        body{
            font-family: Arial;
            background-color: #f5f5f5;
        }

        .container{
            width: 600px;
            margin: 50px auto;
            background: white;
            padding: 30px;
            border-radius: 5px;
        }

        h2{
            text-align: center;
            color: orange;
            margin-bottom: 40px;
        }

        table{
            width: 100%;
        }

        td{
            padding: 10px;
        }

        input, select{
            width: 100%;
            padding: 8px;
        }

        .button{
            width: 100px;
            padding: 8px;
        }

    </style>

</head>
<body>

<div class="container">

    <h2>Form Input Data Mahasiswa</h2>

    <form action="proses.php" method="POST">

        <table>

            <tr>
                <td>ID Mahasiswa / NIM</td>
                <td>
                    <input type="text" name="nim" required>
                </td>
            </tr>

            <tr>
                <td>Nama</td>
                <td>
                    <input type="text" name="nama" required>
                </td>
            </tr>

            <tr>
                <td>Jurusan</td>
                <td>
                    <select name="jurusan" required>
                        <option value="">- Pilih Jurusan -</option>
                        <option>Teknik Informatika</option>
                        <option>Sistem Informasi</option>
                        <option>Manajemen Informatika</option>
                        <option>Teknik Komputer</option>
                    </select>
                </td>
            </tr>

            <tr>
                <td>Alamat</td>
                <td>
                    <input type="text" name="alamat" required>
                </td>
            </tr>

            <tr>
                <td>No. Telp</td>
                <td>
                    <input type="text" name="no_telp" required>
                </td>
            </tr>

            <tr>
                <td></td>
                <td>
                    <input type="submit" value="Submit" class="button">
                    <input type="reset" value="Cancel" class="button">
                </td>
            </tr>

        </table>

    </form>

</div>

</body>
</html>