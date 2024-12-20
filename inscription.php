<?php
// Inclure la connexion à la base de données
require_once 'db_connection.php';

// Vérification de la méthode POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupération et nettoyage des données du formulaire
    $user = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $pass = trim($_POST['password']);

    // Validation des champs obligatoires
    if (empty($user) || empty($email) || empty($pass)) {
        die("Tous les champs sont obligatoires.");
    }

    // Vérification si l'email ou le nom d'utilisateur existe déjà
    $query = $pdo->prepare("SELECT * FROM Users WHERE email = :email OR username = :username");
    $query->execute(['email' => $email, 'username' => $user]);
    if ($query->rowCount() > 0) {
        die("Nom d'utilisateur ou email déjà utilisé.");
    }

    // Hashage sécurisé du mot de passe
    $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);

    // Insertion des données dans la table Users
    $insert = $pdo->prepare("INSERT INTO Users (username, email, password, created_at) VALUES (:username, :email, :password, NOW())");
    $insert->execute([
        'username' => $user,
        'email' => $email,
        'password' => $hashedPassword,
    ]);

    // Confirmation d'inscription
    echo "Inscription réussie. Vous pouvez maintenant vous connecter.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
</head>
<body>
    <h2>Créer un compte</h2>
    <form action="inscription.php" method="post">
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" id="username" name="username" required><br><br>
        
        <label for="email">Email :</label>
        <input type="email" id="email" name="email" required><br><br>
        
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" required><br><br>
        
        <input type="submit" value="S'inscrire">
    </form>
</body>
</html>
