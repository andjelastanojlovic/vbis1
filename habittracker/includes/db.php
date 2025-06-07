<?php
$host = 'localhost';
$db = 'habit_tracker';
$user = 'root';
$pass = ''; // po potrebi promeni

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Greška sa bazom: " . $e->getMessage());
}
?>
