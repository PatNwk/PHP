<?php

$host = "localhost";
$dbname = "ecommerce_site";
$username = "root";
$password = "root";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if (isset($_GET['article_id'])) {
    $article_id = $_GET['article_id'];

    try {
        $sql = "SELECT a.*, u.username AS seller_username, u.id AS seller_id, u.email AS seller_email 
                FROM Articles a 
                JOIN Users u ON a.author_id = u.id 
                WHERE a.id = :article_id";
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
    <title>Détails de l'Article</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

    <!-- Navbar -->
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

    <!-- Contenu principal -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg">
                    <div class="card-body text-center">
                        <h1 class="card-title"><?php echo htmlspecialchars($article['name']); ?></h1>
                        <img src="<?php echo htmlspecialchars($article['image_url']); ?>" alt="<?php echo htmlspecialchars($article['name']); ?>" class="img-fluid rounded mt-3" style="max-width: 100%; height: auto; max-height: 400px;">
                        <p class="mt-3"><strong>Description :</strong> <?php echo nl2br(htmlspecialchars($article['description'])); ?></p>
                        <h3 class="text-danger"><?php echo htmlspecialchars($article['price']); ?> €</h3>

                        <!-- Informations sur le vendeur -->
                        <div class="mt-4">
                            <a href="profile.php?user_id=<?php echo htmlspecialchars($article['seller_id']); ?>" class="btn btn-info btn-sm">Voir le profil du vendeur</a>
                        </div>

                        <!-- Formulaire pour ajouter au panier -->
                        <form method="POST" action="cart.php" class="mt-4">
                            <input type="hidden" name="article_id" value="<?php echo htmlspecialchars($article['id']); ?>">
                            <button type="submit" name="add_to_cart" class="btn btn-success btn-lg">🛒 Ajouter au Panier</button>
                        </form>


                        <!-- Bouton retour -->
                        <a href="index.php" class="btn btn-outline-primary mt-3">⬅ Retour à l'Index</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

<?php
$pdo = null;
?>
