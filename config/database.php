<?php
// config/database.php

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_sia'; // ganti dengan nama database kamu

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
