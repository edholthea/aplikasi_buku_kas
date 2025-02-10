<?php
$servername = "192.168.1.13";
$username = "root";
$password = "edinur12";
$dbname = "buku_kas";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
