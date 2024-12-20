<?php
// db_connection.php

// Configuration générale de la base de données
$host = 'localhost';
$dbname = 'ecomerce_site';
$username = 'userr';
$password = 'mdp';

try {
    // Création de l'objet PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>
