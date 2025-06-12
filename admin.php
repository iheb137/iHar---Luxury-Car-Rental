<?php
session_start();

// Vérification si l'utilisateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
// Inclusion du fichier de configuration
require_once 'config.php';

// Fonction pour sécuriser les données
function secure($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Traitement des actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];
    
    if ($action === 'confirmer') {
        $stmt = $conn->prepare("UPDATE reservations SET statut = 'confirmee' WHERE id_reservation = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($action === 'annuler') {
        $stmt = $conn->prepare("UPDATE reservations SET statut = 'annulee' WHERE id_reservation = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    
    // Redirection pour éviter les soumissions multiples
    header('Location: admin.php');
    exit;
}

// Récupération des réservations
$sql = "
    SELECT r.*, c.nom, c.email, c.telephone, v.nom AS vehicule_nom 
    FROM reservations r
    JOIN clients c ON r.id_client = c.id_client
    JOIN vehicules v ON r.id_vehicule = v.id_vehicule
    ORDER BY r.date_reservation DESC
";
$result = $conn->query($sql);

// Récupération du nombre de messages non lus
$sql_messages = "SELECT COUNT(*) as count FROM contacts WHERE lu = FALSE";
$result_messages = $conn->query($sql_messages);
$row_messages = $result_messages->fetch_assoc();
$messages_non_lus = $row_messages['count'];

// Récupération du nombre de réclamations nouvelles
$sql_reclamations = "SELECT COUNT(*) as count FROM reclamations WHERE statut = 'nouvelle'";
$result_reclamations = $conn->query($sql_reclamations);
$row_reclamations = $result_reclamations->fetch_assoc();
$reclamations_nouvelles = $row_reclamations['count'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - IHAR Location de Voitures</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        header {
            background-color: #003366;
            color: white;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 0;
            position: relative;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 2rem;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        tr:hover {
            background-color: #f9f9f9;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        
        .status-pending {
            background-color: #FFF3CD;
            color: #856404;
        }
        
        .status-confirmed {
            background-color: #D4EDDA;
            color: #155724;
        }
        
        .status-cancelled {
            background-color: #F8D7DA;
            color: #721C24;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            display: inline-block;
        }
        
        .btn-confirm {
            background-color: #28A745;
            color: white;
        }
        
        .btn-cancel {
            background-color: #DC3545;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .empty-message {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
        
        /* Styles pour la navigation admin */
        .admin-nav {
            display: flex;
            justify-content: center;
            background-color: #004d99;
            padding: 1rem;
            margin-bottom: 0;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            margin: 0 0.5rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .admin-nav a:hover {
            background-color: #0066cc;
        }
        
        .admin-nav a.active {
            background-color: #0066cc;
        }
        
        .badge {
            display: inline-block;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 0.2rem 0.5rem;
            font-size: 0.8rem;
            margin-left: 5px;
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
        <a href="admin.php" class="active">Réservations</a>
        <a href="admin_contacts.php">
            Messages de Contact
            <?php if ($messages_non_lus > 0): ?>
                <span class="badge"><?= $messages_non_lus ?></span>
            <?php endif; ?>
        </a>
        <a href="admin_reclamations.php">
            Réclamations
            <?php if ($reclamations_nouvelles > 0): ?>
                <span class="badge"><?= $reclamations_nouvelles ?></span>
            <?php endif; ?>
        </a>
    </div>
    
    <div class="container">
        <h2>Gestion des Réservations</h2>
        
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Contact</th>
                        <th>Véhicule</th>
                        <th>Période</th>
                        <th>Jours</th>
                        <th>Prix</th>
                        <th>Statut</th>
                        <th>Date de réservation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id_reservation'] ?></td>
                            <td><?= secure($row['nom']) ?></td>
                            <td>
                                <?= secure($row['email']) ?><br>
                                <?= secure($row['telephone']) ?>
                            </td>
                            <td><?= secure($row['vehicule_nom']) ?></td>
                            <td>
                                Du <?= $row['date_debut'] ?><br>
                                Au <?= $row['date_fin'] ?>
                            </td>
                            <td><?= $row['nombre_jours'] ?></td>
                            <td><?= number_format($row['prix_total'], 2) ?> TND</td>
                            <td>
                                <?php if ($row['statut'] === 'en_attente'): ?>
                                    <span class="status status-pending">En attente</span>
                                <?php elseif ($row['statut'] === 'confirmee'): ?>
                                    <span class="status status-confirmed">Confirmée</span>
                                <?php else: ?>
                                    <span class="status status-cancelled">Annulée</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $row['date_reservation'] ?></td>
                            <td class="actions">
                                <?php if ($row['statut'] === 'en_attente'): ?>
                                    <a href="admin.php?action=confirmer&id=<?= $row['id_reservation'] ?>" class="btn btn-confirm">Confirmer</a>
                                    <a href="admin.php?action=annuler&id=<?= $row['id_reservation'] ?>" class="btn btn-cancel">Annuler</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty-message">Aucune réservation trouvée.</p>
        <?php endif; ?>
    </div>
    
    <script>
        // Ajouter un effet de survol pour les boutons
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('mouseover', function() {
                this.style.opacity = '0.8';
            });
            button.addEventListener('mouseout', function() {
                this.style.opacity = '1';
            });
        });
    </script>
</body>
</html>