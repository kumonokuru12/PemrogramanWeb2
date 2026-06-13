<?php

$username = $_POST['username'];
$password = $_POST['password'];

if($username == "admin" && $password == "123")
{
    echo "Login Berhasil";
}
else
{
    echo "Error : Username atau Password salah";
}

?>