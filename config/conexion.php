<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "qdelicias_11/05/2026";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
$conn->query("SET time_zone = '-05:00'");
?>