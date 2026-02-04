<?php
$servername = "localhost";
$username = "admin";
$password = "admin";
$dbname = "agilizapdvbd";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=UTF8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $erro) {
    die("Ocorreu o seguinte erro na conexão: " . $erro->getMessage());
}
?>