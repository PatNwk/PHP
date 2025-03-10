<?php
session_start(); // Démarrer la session

require 'db_connection.php'; // Inclure le fichier de connexion à la base de données

// Récupérer les articles disponibles (exclut les articles vendus et trie par la date de publication)
$sql = "SELECT * FROM Articles WHERE is_sold = 0 ORDER BY publication_date DESC";
$stmt = $pdo->query($sql);

// Vérifier si l'utilisateur est connecté
$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .card {
            height: 400px; /* Hauteur fixe pour toutes les cartes */
        }
        .card-img-top {
            height: 200px; /* Hauteur fixe pour les images */
            object-fit: cover; /* Ajuste l'image pour remplir la hauteur sans déformation */
        }
        .card-body {
            overflow: hidden; /* Cache tout contenu excédentaire dans la description */
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="index.php">Lemauvaiscoin</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="cart.php">Panier</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
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

<div class="container text-center mt-5">
    <h1 class="mb-3">Bienvenue sur Lemauvaiscoin</h1>
    <p class="lead">Achetez et vendez en toute simplicité !</p>

    <!-- Afficher les articles -->
    <div class="row mt-5">
        <?php if ($stmt->rowCount() > 0): ?>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($row['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($row['description']); ?></p>
                            <p class="card-text"><strong>Prix:</strong> <?php echo htmlspecialchars($row['price']); ?> €</p>
                            <a href="details.php?article_id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-primary">Voir les détails</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>Aucun article disponible.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


<?php
// Fermer la connexion PDO
$pdo = null;
?>
