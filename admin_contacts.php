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
    
    if ($action === 'marquer_lu') {
        $stmt = $conn->prepare("UPDATE contacts SET lu = TRUE WHERE id_contact = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($action === 'marquer_non_lu') {
        $stmt = $conn->prepare("UPDATE contacts SET lu = FALSE WHERE id_contact = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    } elseif ($action === 'supprimer') {
        $stmt = $conn->prepare("DELETE FROM contacts WHERE id_contact = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
    
    // Redirection pour éviter les soumissions multiples
    header('Location: admin_contacts.php');
    exit;
}

// Récupération des messages de contact
$sql = "SELECT * FROM contacts ORDER BY date_envoi DESC";
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
        
        .status-unread {
            background-color: #FFF3CD;
            color: #856404;
        }
        
        .status-read {
            background-color: #D4EDDA;
            color: #155724;
        }
        
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
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
        
        .btn-read {
            background-color: #28A745;
            color: white;
        }
        
        .btn-unread {
            background-color: #FFC107;
            color: #212529;
        }
        
        .btn-delete {
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
        
        .message-content {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
        <a href="admin_contacts.php" class="active">
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
        <h2>Gestion des Messages de Contact</h2>
        
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Sujet</th>
                        <th>Message</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id_contact'] ?></td>
                            <td><?= htmlspecialchars($row['date_envoi']) ?></td>
                            <td><?= htmlspecialchars($row['nom']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['telephone']) ?></td>
                            <td><?= htmlspecialchars($row['sujet']) ?></td>
                            <td class="message-content"><?= htmlspecialchars($row['message']) ?></td>
                            <td>
                                <?php if ($row['lu'] == 0): ?>
                                    <span class="status status-unread">Non lu</span>
                                <?php else: ?>
                                    <span class="status status-read">Lu</span>
                                <?php endif; ?>
                            </td>
                            <td class="actions">
                                <button class="btn btn-read" onclick="afficherMessage('<?= htmlspecialchars($row['nom']) ?>', '<?= htmlspecialchars($row['email']) ?>', '<?= htmlspecialchars($row['telephone']) ?>', '<?= htmlspecialchars($row['sujet']) ?>', '<?= htmlspecialchars(str_replace("'", "\\'", $row['message'])) ?>', '<?= htmlspecialchars($row['date_envoi']) ?>')">Voir</button>
                                
                                <?php if ($row['lu'] == 0): ?>
                                    <a href="admin_contacts.php?action=marquer_lu&id=<?= $row['id_contact'] ?>" class="btn btn-read">Marquer comme lu</a>
                                <?php else: ?>
                                    <a href="admin_contacts.php?action=marquer_non_lu&id=<?= $row['id_contact'] ?>" class="btn btn-unread">Marquer comme non lu</a>
                                <?php endif; ?>
                                
                                <a href="admin_contacts.php?action=supprimer&id=<?= $row['id_contact'] ?>" class="btn btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">Supprimer</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="empty-message">Aucun message de contact trouvé.</p>
        <?php endif; ?>
    </div>
    
    <!-- Modal pour afficher le message complet -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fermerModal()">&times;</span>
            <h2 style="margin-bottom: 1.5rem;">Détails du message</h2>
            <div id="messageDetails"></div>
        </div>
    </div>
    
    <script>
        function afficherMessage(nom, email, telephone, sujet, message, date) {
            const modal = document.getElementById('messageModal');
            const messageDetails = document.getElementById('messageDetails');
            
            messageDetails.innerHTML = `
                <p><strong>Date:</strong> ${date}</p>
                <p><strong>Nom:</strong> ${nom}</p>
                <p><strong>Email:</strong> ${email}</p>
                <p><strong>Téléphone:</strong> ${telephone}</p>
                <p><strong>Sujet:</strong> ${sujet}</p>
                <p><strong>Message:</strong></p>
                <div style="background-color: #f9f9f9; padding: 1rem; border-radius: 4px; margin-top: 0.5rem;">
                    ${message.replace(/\n/g, '<br>')}
                </div>
            `;
            
            modal.style.display = 'flex';
        }
        
        function fermerModal() {
            const modal = document.getElementById('messageModal');
            modal.style.display = 'none';
        }
        
        // Fermer le modal si on clique en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('messageModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
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