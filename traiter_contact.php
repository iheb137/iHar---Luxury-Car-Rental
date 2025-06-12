<?php
// Inclusion du fichier de configuration
require_once 'config.php';

// Vérification que la requête est de type POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $nom = $_POST['nom'];
    $email = $_POST['email'];
    $telephone = $_POST['telephone'];
    $message = $_POST['message'];
    
    // Validation des données
    if (empty($nom) || empty($email) || empty($telephone) || empty($message)) {
        echo json_encode([
            'success' => false,
            'message' => 'Veuillez remplir tous les champs'
        ]);
        exit;
    }
    
    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Veuillez entrer une adresse email valide'
        ]);
        exit;
    }
    
    // Validation du téléphone (numéro tunisien)
    if (!preg_match('/^(2|5|9)\d{7}$/', $telephone)) {
        echo json_encode([
            'success' => false,
            'message' => 'Veuillez entrer un numéro de téléphone tunisien valide'
        ]);
        exit;
    }
    
    // Préparation de la requête
    $stmt = $conn->prepare("INSERT INTO contacts (nom, email, telephone, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nom, $email, $telephone, $message);
    
    // Exécution de la requête
    if ($stmt->execute()) {
        // Message enregistré avec succès
        echo json_encode([
            'success' => true,
            'message' => 'Merci pour votre message ! Nous vous recontacterons très prochainement.'
        ]);
    } else {
        // Erreur lors de l'enregistrement du message
        echo json_encode([
            'success' => false,
            'message' => 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer.'
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