```php id="j82ks7"
<?php
/****************************************************
Halaman ini merupakan halaman logout,
digunakan untuk menghapus session yang ada.
*****************************************************/
session_start();

// Periksa apakah user sudah login
if (isset($_SESSION['login'])) {

    // Menghapus semua session
    session_unset();
    session_destroy();

    echo "<h1>Anda berhasil LOGOUT</h1>";

    echo "<h2>
            Klik <a href='session1.php'>di sini</a>
            untuk LOGIN kembali
          </h2>";

    echo "<h2>
            Anda sekarang tidak bisa masuk ke halaman
            <a href='session2.php'>session2.php</a> lagi
          </h2>";

} else {

    echo "<h2>Anda belum login.</h2>";
}
?>
```
