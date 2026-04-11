<?php
// Nama peralatan
$brg1 = "Buku";
$brg2 = "Mouse";
$brg3 = "FlashDisk";
$brg4 = "Pulpen";

// Harga per unit
$harga1 = 17500;
$harga2 = 30000;
$harga3 = 70000;
$harga4 = 22300;

// Jumlah peralatan
$jmlbrg1 = 2;
$jmlbrg2 = 5;
$jmlbrg3 = 1;
$jmlbrg4 = 3;

// Total harga per jenis
$th1 = $jmlbrg1 * $harga1;
$th2 = $jmlbrg2 * $harga2;
$th3 = $jmlbrg3 * $harga3;
$th4 = $jmlbrg4 * $harga4;

// Grand total
$tharga = $th1 + $th2 + $th3 + $th4;

// Diskon
$diskon = 5;
$tdiskon = ($diskon * $tharga)/100;

// Jumlah bayar
$tdibayar = $tharga - $tdiskon;
?>
<html>
<head><title>Daftar Peralatan Yang Dibeli</title></head>
<body>
<center>
<h2>Contoh Perhitungan dengan PHP</h2>
<table border="1" cellpadding="5">
<tr><th>Nama Peralatan</th><th>Jumlah</th><th>Harga Satuan</th><th>Jumlah Harga</th></tr>
<tr><td><?php echo $brg1; ?></td><td><?php echo $jmlbrg1; ?></td><td><?php echo $harga1; ?></td><td><?php echo $th1; ?></td></tr>
<tr><td><?php echo $brg2; ?></td><td><?php echo $jmlbrg2; ?></td><td><?php echo $harga2; ?></td><td><?php echo $th2; ?></td></tr>
<tr><td><?php echo $brg3; ?></td><td><?php echo $jmlbrg3; ?></td><td><?php echo $harga3; ?></td><td><?php echo $th3; ?></td></tr>
<tr><td><?php echo $brg4; ?></td><td><?php echo $jmlbrg4; ?></td><td><?php echo $harga4; ?></td><td><?php echo $th4; ?></td></tr>
<tr><td colspan="3" align="right">Total Harga</td><td><?php echo $tharga; ?></td></tr>
<tr><td colspan="3" align="right">Diskon (<?php echo $diskon; ?>%)</td><td><?php echo $tdiskon; ?></td></tr>
<tr><td colspan="3" align="right">Jumlah Bayar</td><td><?php echo $tdibayar; ?></td></tr>
</table>
</center>
</body>
</html>