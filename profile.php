<?php
require 'db_connection.php'; // Inclure la connexion à la base de données

// Récupérer l'ID de l'utilisateur depuis l'URL
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Récupérer les informations du vendeur
$sql_user = "SELECT username, email FROM Users WHERE id = :user_id";
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil du Vendeur</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        /* Style pour les cartes des annonces */
        .card {
            height: 400px; /* Hauteur uniforme pour toutes les cartes */
        }
        .card-img-top {
            height: 150px; /* Hauteur fixe des images */
            object-fit: cover; /* Recadre les images sans déformation */
        }
        .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        /* Badges pour indiquer le statut */
        .badge-vendu {
            background-color: #28a745;
            color: white;
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .badge-en-cours {
            background-color: #ffc107;
            color: black;
            font-size: 0.9rem;
            padding: 5px 10px;
            border-radius: 5px;
        }
        /* Ajustement de la carte du profil utilisateur */
        .profile-card {
            height: auto; /* Autoriser la hauteur automatique pour le profil */
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1 class="text-center">Profil du Vendeur</h1>

    <!-- Informations du vendeur -->
    <div class="card mt-4 profile-card">
        <div class="card-body">
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
                                <span class="badge badge-vendu">Vendu</span>
                            <?php else: ?>
                                <span class="badge badge-en-cours">En cours</span>
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
