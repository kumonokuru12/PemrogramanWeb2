```php id="v91lm4"
<?php
/****************************************************
Halaman ini merupakan contoh pemeriksaan session.
Halaman ini hanya dapat diakses jika user sudah login.
****************************************************/
session_start();

// Pemeriksaan session
if (isset($_SESSION['login'])) {

    // Jika sudah login
    $user = $_SESSION['login'];

    echo "<h1>Selamat Datang $user</h1>";
    echo "<h2>Halaman ini hanya bisa diakses jika Anda sudah login</h2>";
    echo "<h2>
            Klik <a href='session3.php'>di sini (session3.php)</a>
            untuk LOGOUT
          </h2>";

} else {

    // Jika session belum ada
    die("
        <h2>Anda belum login!</h2>
        <p>
            Anda tidak berhak masuk ke halaman ini.<br>
            Silahkan login
            <a href='session1.php'>di sini</a>
        </p>
    ");
}
?>
```
