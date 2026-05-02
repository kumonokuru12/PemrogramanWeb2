<HTML>
<HEAD>
    <TITLE>Getdate</TITLE>
</HEAD>
<BODY>
    <center>
    <h1>
    <?php
        $sekarang = getdate(); 
        $bulan = $sekarang['month']; // Nama bulan tekstual [cite: 262]
        $hari = $sekarang['mday'];   // Hari dalam bulan [cite: 255]
        $tahun = $sekarang['year'];  // Tahun numeris [cite: 259]
        $jam = $sekarang['hours'];   // Jam format 24 jam [cite: 256]

        // Logika penentuan ucapan selamat berdasarkan jam [cite: 267]
        if ($jam <= 11) {
            echo "Selamat Pagi";
        } elseif ($jam > 11 and $jam <= 15) {
            echo "Selamat Siang";
        } elseif ($jam > 15 and $jam <= 18) {
            echo "Selamat Sore";
        } elseif ($jam > 18) {
            echo "Selamat Malam";
        }
    ?>
    </h1>
    <h2>Selamat datang</h2>
    <h3>Kazhem Avie Al Hakim</h3>
    <h4>Sekarang adalah tanggal <?php echo "$hari $bulan $tahun"; ?></h4>
    </center>
</BODY>
</HTML>