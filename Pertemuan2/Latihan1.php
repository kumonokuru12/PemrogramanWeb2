<?php
$a = 1;
$b = 2;

$c = $a.$b;   // menggabungkan angka 1 dan 2 → "12"
$d = $c + 1;  // "12" dianggap angka → 12 + 1 = 13
echo $d . "<br>";

$e = "Number";
$f = $e.$d;   // "Number" + "13" → "Number13"
echo $f;
?>