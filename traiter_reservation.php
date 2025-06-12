<?php
// Inclusion du fichier de configuration
require_once 'config.php';

// Vérification que la requête est de type POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $vehicule_code = $_POST['vehicule'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    
    // Calcul du nombre de jours
    $date1 = new DateTime($date_debut);
    $date2 = new DateTime($date_fin);
    $interval = $date1->diff($date2);
    $nombre_jours = $interval->days;
    
    // Si le nombre de jours est 0 (même jour), on compte 1 jour
    if ($nombre_jours == 0) {
        $nombre_jours = 1;
    }
    
    // Récupération du prix du véhicule
    $stmt = $conn->prepare("SELECT id_vehicule, prix_jour FROM vehicules WHERE code = ?");
    $stmt->bind_param("s", $vehicule_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $vehicule = $result->fetch_assoc();
        $id_vehicule = $vehicule['id_vehicule'];
        $prix_jour = $vehicule['prix_jour'];
        
        // Calcul du prix total
        $prix_total = $prix_jour * $nombre_jours;
        
        // Application de la remise si la location est de 10 jours ou plus
        if ($nombre_jours >= 10) {
            $prix_total *= 0.9; // Remise de 10%
        }
        
        // Vérification si le client existe déjà
        $stmt = $conn->prepare("SELECT id_client FROM clients WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Le client existe déjà
            $client = $result->fetch_assoc();
            $id_client = $client['id_client'];
        } else {
            // Création d'un nouveau client
            $stmt = $conn->prepare("INSERT INTO clients (nom, email, telephone) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nom, $email, $telephone);
            $stmt->execute();
            $id_client = $conn->insert_id;
        }
        
        // Vérification de la disponibilité du véhicule pour les dates demandées
        $stmt = $conn->prepare("
            SELECT id_reservation FROM reservations 
            WHERE id_vehicule = ? 
            AND statut != 'annulee'
            AND ((date_debut BETWEEN ? AND ?) 
                OR (date_fin BETWEEN ? AND ?)
                OR (date_debut <= ? AND date_fin >= ?))
        ");
        $stmt->bind_param("issssss", $id_vehicule, $date_debut, $date_fin, $date_debut, $date_fin, $date_debut, $date_fin);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Le véhicule n'est pas disponible pour ces dates
            echo json_encode([
                'success' => false,
                'message' => 'Ce véhicule n\'est pas disponible pour les dates sélectionnées.'
            ]);
        } else {
            // Création de la réservation
            $stmt = $conn->prepare("
                INSERT INTO reservations (id_client, id_vehicule, date_debut, date_fin, nombre_jours, prix_total) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iissid", $id_client, $id_vehicule, $date_debut, $date_fin, $nombre_jours, $prix_total);
            
            if ($stmt->execute()) {
                // Réservation créée avec succès
                echo json_encode([
                    'success' => true,
                    'message' => 'Réservation confirmée',
                    'reservation_id' => $conn->insert_id,
                    'details' => [
                        'vehicule' => $vehicule_code,
                        'date_debut' => $date_debut,
                        'date_fin' => $date_fin,
                        'nombre_jours' => $nombre_jours,
                        'prix_total' => $prix_total
                    ]
                ]);
            } else {
                // Erreur lors de la création de la réservation
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de la création de la réservation: ' . $stmt->error
                ]);
            }
        }
    } else {
        // Véhicule non trouvé
        echo json_encode([
            'success' => false,
            'message' => 'Véhicule non trouvé'
        ]);
    }
    
    $stmt->close();
    $conn->close();
} else {
    // Méthode non autorisée
    echo json_encode([
        'success' => false,
        'message' => 'Méthode non autorisée'
    ]);
}
?>