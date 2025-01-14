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

// Vérifier si l'utilisateur est connecté
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "Vous devez être connecté pour vendre des articles.";
    exit;
}

// Ajouter un article au panier
if (isset($_POST['add_to_cart'])) {
    $article_id = $_POST['article_id'];
    $user_id = $_SESSION['user_id'];

    try {
        // Ajouter l'article au panier
        $sql = "INSERT INTO Cart (user_id, article_id) VALUES (:user_id, :article_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Marquer l'article comme vendu
            $update_sql = "UPDATE Articles SET is_sold = 1 WHERE id = :article_id";
            $update_stmt = $pdo->prepare($update_sql);
            $update_stmt->bindParam(':article_id', $article_id, PDO::PARAM_INT);
            $update_stmt->execute();

            echo "Article ajouté au panier.";
        } else {
            echo "Erreur lors de l'ajout au panier.";
        }
    } catch (PDOException $e) {
        echo "Erreur: " . $e->getMessage();
    }
}

// Ajouter un nouvel article en vente
if (isset($_POST['submit_article'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image_url = $_POST['image_url'];
    $author_id = $_SESSION['user_id'];  // L'ID de l'utilisateur connecté

    try {
        // Insérer le nouvel article dans la base de données
        $sql = "INSERT INTO Articles (name, description, price, image_url, author_id) 
                VALUES (:name, :description, :price, :image_url, :author_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':author_id', $author_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Redirection vers la page index.php
            header("Location: index.php");
            exit;
        } else {
            echo "Erreur lors de la mise en vente de l'article.";
        }
    } catch (PDOException $e) {
        echo "Erreur: " . $e->getMessage();
    }
}

// Récupérer tous les articles disponibles
try {
    $sql = "SELECT * FROM Articles";
    $stmt = $pdo->query($sql);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des articles: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="sell.css">
    <title>Vendre des Articles</title>
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

    <div class="container">
        <h1>Vendre des Articles</h1>

        <!-- Bouton pour revenir à l'index -->
        <a href="index.php">
            <button type="button" class="btn btn-secondary mb-3">Retour à l'Index</button>
        </a>
        
        <!-- Formulaire pour mettre un article en vente -->
        <h2>Ajouter un nouvel article en vente</h2>
        <form method="POST" action="sell.php">
            <div class="mb-3">
            <label for="description" class="form-label">Titre</label>
                <input type="text" id="name" name="name" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Prix (€)</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" required>
            </div>

            <div class="mb-3">
                <label for="image_url" class="form-label">URL de l'image</label>
                <input type="text" id="image_url" name="image_url" class="form-control" required>
            </div>

            <button type="submit" name="submit_article" class="btn btn-primary">Mettre l'article en vente</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Fermer la connexion PDO (optionnel, mais une bonne pratique)
$pdo = null;
?>
