<?php
$host = "localhost";
$user = "root";
$pass = "password";
$db = "qdelicias_pos";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>