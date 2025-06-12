<?php
// Informations de connexion à la base de données
$servername = "localhost";
$username = "root"; // Nom d'utilisateur par défaut de XAMPP
$password = ""; // Mot de passe par défaut de XAMPP (vide)
$dbname = "ihar_location";

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("La connexion a échoué: " . $conn->connect_error);
}

// Configuration du jeu de caractères
$conn->set_charset("utf8mb4");
?>