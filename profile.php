<?php
session_start(); // Démarrer la session
require 'db_connection.php'; // Inclure la connexion à la base de données

// Récupérer l'ID de l'utilisateur depuis l'URL
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Récupérer les informations du vendeur
$sql_user = "SELECT username, email, profile_picture FROM Users WHERE id = :user_id";
$stmt_user = $pdo->prepare($sql_user);
$stmt_user->execute(['user_id' => $user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Utilisateur introuvable.");
}

// Récupérer toutes les annonces publiées par ce vendeur (vendues et en cours)
$sql_articles = "SELECT * FROM Articles WHERE author_id = :user_id";
$stmt_articles = $pdo->prepare($sql_articles);
$stmt_articles->execute(['user_id' => $user_id]);
$articles = $stmt_articles->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil du Vendeur</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="profile.css">
</head>
<body class="bg-light">

<!-- Barre de navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Lemauvaiscoin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="cart.php">Panier</a></li>
                <?php if ($is_logged_in): ?>
                    <li class="nav-item"><a class="nav-link" href="account.php">Mon Compte</a></li>
                    <li class="nav-item"><a class="nav-link" href="sell.php">Vendre un article</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Se connecter</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">S'inscrire</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h1 class="text-center">Profil du Vendeur</h1>

    <!-- Informations du vendeur -->
    <div class="card mt-4 profile-card">
        <div class="card-body text-center">
            <!-- Photo de profil -->
            <img src="<?php echo htmlspecialchars($user['profile_picture'] ? $user['profile_picture'] : 'uploads/profile_pictures/default.png'); ?>" 
                 alt="Photo de profil" class="rounded-circle img-thumbnail mb-3" style="width: 150px; height: 150px;">
            
            <h3 class="card-title">Nom d'utilisateur : <?php echo htmlspecialchars($user['username']); ?></h3>
            <p>Email : <?php echo htmlspecialchars($user['email']); ?></p>
        </div>
    </div>

    <!-- Toutes les annonces -->
    <h2 class="mt-5">Toutes les annonces de <?php echo htmlspecialchars($user['username']); ?></h2>
    <div class="row">
        <?php if (count($articles) > 0): ?>
            <?php foreach ($articles as $article): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($article['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($article['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($article['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($article['description']); ?></p>
                            <p class="card-text"><strong>Prix :</strong> <?php echo htmlspecialchars($article['price']); ?> €</p>
                            <!-- Badge pour indiquer le statut -->
                            <?php if ($article['is_sold']): ?>
                                <span class="badge bg-danger">Vendu</span>
                            <?php else: ?>
                                <span class="badge bg-success">En cours</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">Aucune annonce publiée par cet utilisateur.</p>
        <?php endif; ?>
    </div>

    <a href="index.php" class="btn btn-primary mt-3">⬅ Retour à l'accueil</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Fermer la connexion PDO (bonne pratique)
$pdo = null;
?>
