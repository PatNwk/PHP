<?php
require_once 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = htmlspecialchars(trim($_POST['username']));
    $pass = trim($_POST['password']);

    if (empty($user) || empty($pass)) {
        die("Tous les champs sont obligatoires.");
    }

    $query = $pdo->prepare("SELECT * FROM Users WHERE username = :username");
    $query->execute(['username' => $user]);
    $userData = $query->fetch();

    if ($userData && password_verify($pass, $userData['password'])) {
        echo "Connexion réussie. Bienvenue, " . htmlspecialchars($userData['username']) . "!";
    } else {
        echo "Nom d'utilisateur ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de Connexion</title>
</head>
<body>
    <div>
        <h2>Connexion</h2>
        <form action="connection.php" method="post">
            <label for="username">Nom d'utilisateur:</label>
            <input type="text" id="username" name="username" required>
            <br>
            <label for="password">Mot de passe:</label>
            <input type="password" id="password" name="password" required>
            <br>
            <input type="submit" value="Se connecter">
        </form>
    </div>
</body>
</html>
