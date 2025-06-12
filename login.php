<?php
session_start();

// Vérification si l'utilisateur est déjà connecté
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin.php');
    exit;
}

// Définition des identifiants (à remplacer par une base de données dans un environnement de production)
$admin_username = "admin";
$admin_password = "admin123"; // À remplacer par un mot de passe fort

$error = '';

// Traitement du formulaire de connexion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $admin_username && $password === $admin_password) {
        // Connexion réussie
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Identifiants incorrects';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - IHAR Location de Voitures</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        
        .login-container {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        h1 {
            text-align: center;
            color: #003366;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        button {
            width: 100%;
            padding: 0.8rem;
            background-color: #003366;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        button:hover {
            background-color: #004d99;
        }
        
        .error {
            color: #dc3545;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Connexion Administration</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>