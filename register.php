<?php
session_start();
require 'db_connection.php'; 

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash du mot de passe

    // Vérifier si l'email existe déjà
    $stmt = $pdo->prepare("SELECT id FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $error_message = "Cet email est déjà utilisé.";
    } else { 
        // Insérer le nouvel utilisateur
        $stmt = $pdo->prepare("INSERT INTO Users (username, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $password])) {
            // Récupérer l'ID de l'utilisateur nouvellement créé
            $user_id = $pdo->lastInsertId();
            
            // Stocker l'utilisateur dans la session
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;
            $_SESSION['username'] = $username;

            // Redirection vers la page du compte
            header("Location: index.php");
            exit();
        } else {
            $error_message = "Erreur lors de l'inscription.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <h1>Lemauvaiscoin</h1>
            <h2>Inscription</h2>
            <?php if ($error_message): ?>
                <p class="error-message"><?= htmlspecialchars($error_message) ?></p>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <p class="success-message"><?= $success_message ?></p>
            <?php endif; ?>
            <form method="post" action="">
                <input type="text" name="username" placeholder="Nom d'utilisateur" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Mot de passe" required>
                <button type="submit" class="register-button">S'inscrire</button>
            </form>
        </div>
    </div>
</body>
</html>
