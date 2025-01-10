<?php
session_start();
require 'db_connection.php'; 

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur
$stmt_user = $pdo->prepare("SELECT * FROM Users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

// Récupérer les articles postés par l'utilisateur
$stmt_articles = $pdo->prepare("SELECT * FROM Articles WHERE author_id = ?");
$stmt_articles->execute([$user_id]);
$articles_posted = $stmt_articles->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les articles achetés par l'utilisateur
$stmt_purchased = $pdo->prepare("SELECT a.* FROM Articles a JOIN Cart c ON a.id = c.article_id WHERE c.user_id = ?");
$stmt_purchased->execute([$user_id]);
$purchased_articles = $stmt_purchased->fetchAll(PDO::FETCH_ASSOC);

// Traitement de la modification des informations de l'utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_info'])) {
    $new_email = $_POST['email'];
    $new_password = $_POST['password'];
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    // Mettre à jour les informations
    $stmt_update = $pdo->prepare("UPDATE Users SET email = ?, password = ? WHERE id = ?");
    if ($stmt_update->execute([$new_email, $new_password_hash, $user_id])) {
        echo "Informations mises à jour avec succès.";
        // Mettre à jour la session avec le nouvel email
        $_SESSION['email'] = $new_email;
    } else {
        echo "Erreur lors de la mise à jour.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Mon Site</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="cart.php">Panier</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Mon Compte</h1>
        <div class="row">
            <div class="col-md-6">
                <h2>Informations de l'utilisateur</h2>
                <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Nom d'utilisateur :</strong> <?php echo htmlspecialchars($user['username']); ?></p>

                <!-- Formulaire de modification des informations -->
                <h3>Modifier mes informations</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary" name="update_info">Mettre à jour</button>
                </form>
            </div>

            <div class="col-md-6">
                <h2>Mes Articles Publiés</h2>
                <?php if (count($articles_posted) > 0): ?>
                    <ul>
                        <?php foreach ($articles_posted as $article): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($article['name']); ?></strong> - <?php echo htmlspecialchars($article['price']); ?> €
                                <p><?php echo htmlspecialchars($article['description']); ?></p>
                                <a href="details.php?article_id=<?php echo $article['id']; ?>" class="btn btn-info btn-sm">Voir les détails</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Vous n'avez pas encore posté d'articles.</p>
                <?php endif; ?>

                <h2>Mes Articles Achetés</h2>
                <?php if (count($purchased_articles) > 0): ?>
                    <ul>
                        <?php foreach ($purchased_articles as $article): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($article['name']); ?></strong> - <?php echo htmlspecialchars($article['price']); ?> €
                                <p><?php echo htmlspecialchars($article['description']); ?></p>
                                <a href="details.php?article_id=<?php echo $article['id']; ?>" class="btn btn-info btn-sm">Voir les détails</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Vous n'avez pas encore acheté d'articles.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Fermer la connexion PDO (optionnel, mais une bonne pratique)
$pdo = null;
?>
