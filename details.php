<?php

$host = "localhost";
$dbname = "ecommerce_site";
$username = "root";
$password = "root";

try {
    // Connexion à la base de données avec PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Vérifier si un ID d'article a été passé en paramètre dans l'URL
if (isset($_GET['article_id'])) {
    $article_id = $_GET['article_id'];

    try {
        // Récupérer les informations détaillées de l'article
        $sql = "SELECT * FROM Articles WHERE id = :article_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
        $stmt->execute();
        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$article) {
            echo "Article non trouvé.";
            exit;
        }
    } catch (PDOException $e) {
        die("Erreur lors de la récupération de l'article : " . $e->getMessage());
    }
} else {
    echo "Aucun article sélectionné.";
    exit;
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>Détails de l'Article</title>
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
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>

<body>
    <h1>Détails de l'Article</h1>

    <!-- Afficher les détails de l'article -->
    <div class="article-details">
        <h2><?php echo htmlspecialchars($article['name']); ?></h2>
        <p><strong>Description :</strong> <?php echo htmlspecialchars($article['description']); ?></p>
        <p><strong>Prix :</strong> <?php echo htmlspecialchars($article['price']); ?> €</p>
        <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['name']); ?>" style="max-width: 300px;">
        <!-- Formulaire pour ajouter l'article au panier -->
        <form method="POST" action="sell.php">
            <input type="hidden" name="article_id" value="<?php echo htmlspecialchars($article['id']); ?>">
            <button type="submit" name="add_to_cart">Ajouter au Panier</button>
        </form>
    </div>

    <!-- Lien pour revenir à la page d'index -->
    <a href="index.php">
        <button type="button">Retour à l'Index</button>
    </a>

</body>
</html>

<?php
// Fermer la connexion PDO (optionnel, mais une bonne pratique)
$pdo = null;
?>
