<?php
$host = 'localhost';
$db   = 'trening_app';
$user = 'root'; // Zmień jeśli masz innego użytkownika
$pass = '';     // Zmień jeśli masz hasło

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą: " . $e->getMessage());
}

session_start();
?>