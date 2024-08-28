<?php
$host = 'localhost';
$dbname = 'poketest';
$user = 'root'; // Cambia 'root' por tu usuario de MySQL
$pass = ''; // Cambia '' por tu contraseña de MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}
?>