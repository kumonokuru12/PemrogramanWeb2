<HTML>
<HEAD>
    <TITLE>Tanggal</TITLE>
</HEAD>
<BODY>
    <font size="10px">
    <?php
        // d: Tanggal (01-31), F: Nama Bulan, Y: Tahun 4 digit [cite: 191, 195, 241]
        echo "Sekarang tanggal ";
        echo date('d-F-Y'); 
        
        echo "<br>dan jam ";
        // h: Jam (01-12), i: Menit, s: Detik, A: AM/PM [cite: 203, 207, 227, 189]
        echo date('h:i:s A');
    ?>
    </font>
</BODY>
</HTML>