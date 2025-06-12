<?php
session_start();

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Inclusion du fichier de configuration
require_once 'config.php';

// Marquer un message comme lu
if (isset($_GET['action']) && $_GET['action'] == 'marquer_lu' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("UPDATE contacts SET lu = TRUE WHERE id_contact = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_contacts.php');
    exit;
}

// Supprimer un message
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM contacts WHERE id_contact = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: admin_contacts.php');
    exit;
}

// Récupération des messages de contact
$sql = "SELECT * FROM contacts ORDER BY date_envoi DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Messages de Contact - IHAR Location de Voitures</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        header {
            background-color: #003366;
            color: white;
            padding: 1.5rem;
            text-align: center;
            position: relative;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .admin-nav {
            display: flex;
            justify-content: center;
            background-color: #004d99;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            margin: 0 0.5rem;
            border-radius: 4px;
        }
        
        .admin-nav a:hover {
            background-color: #0066cc;
        }
        
        .admin-nav a.active {
            background-color: #0066cc;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f2f2f2;
        }
        
        tr:hover {
            background-color: #f9f9f9;
        }
        
        .message-content {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .btn-primary {
            background-color: #003366;
            color: white;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .unread {
            font-weight: bold;
            background-color: #f0f7ff;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-size: 1.5rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>
        <h1>Administration - IHAR Location de Voitures</h1>
        <div style="position: absolute; top: 1.5rem; right: 1.5rem;">
            <a href="logout.php" style="color: white; text-decoration: none; background-color: #004d99; padding: 0.5rem 1rem; border-radius: 4px;">Déconnexion</a>
        </div>
    </header>
    
    <div class="admin-nav">
        <a href="admin.php">Réservations</a>
        <a href="admin_contacts.php" class="active">Messages de Contact</a>
    </div>
    
    <div class="container">
        <h2 style="margin-bottom: 1.5rem;">Messages de Contact</h2>
        
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Message</th>
               