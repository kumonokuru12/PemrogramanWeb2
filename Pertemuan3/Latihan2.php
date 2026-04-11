<html>
<head>
<title>Contoh Penggunaan IF</title>
</head>
<body>
<form method="post">
    Besar Pembelian :
    <input type="text" name="total_beli"><br><br>
    <input type="submit" value="Tentukan Diskon">
</form>

<?php
if (isset($_POST['total_beli'])) {
    $total_beli = intval($_POST['total_beli']);
    $diskon = 0;

    if ($total_beli >= 200000) {
        $diskon = 0.1;
    } elseif ($total_beli >= 100000) {
        $diskon = 0.05;
    } else {
        $diskon = 0.01;
    }

    $diskon = $diskon * $total_beli;
    printf("Diskon = %s <br>\n", $diskon);
    printf("Pembayaran = %s <br>\n", $total_beli - $diskon);
}
?>
</body>
</html>