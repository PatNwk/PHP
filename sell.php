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
            echo "Article mis en vente avec succès.";
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
<body>
    <h1>Vendre des Articles</h1>

    <!-- Bouton pour revenir à l'index -->
    <a href="index.php">
        <button type="button">Retour à l'Index</button>
    </a>
    
    <!-- Formulaire pour mettre un article en vente -->
    <h2>Ajouter un nouvel article en vente</h2>
    <form method="POST" action="sell.php">
        <label for="name">Nom de l'article :</label><br>
        <input type="text" id="name" name="name" required><br>

        <label for="description">Description :</label><br>
        <textarea id="description" name="description" required></textarea><br>

        <label for="price">Prix (€) :</label><br>
        <input type="number" id="price" name="price" step="0.01" required><br>

        <label for="image_url">URL de l'image :</label><br>
        <input type="text" id="image_url" name="image_url" required><br>

        <button type="submit" name="submit_article">Mettre l'article en vente</button>
    </form>
    
    <h2>Articles Disponibles à l'achat</h2>
    <div class="articles">
        <?php
        if ($stmt->rowCount() > 0) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Afficher chaque article
                echo "<div class='article'>";
                echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
                echo "<p>" . htmlspecialchars($row['description']) . "</p>";
                echo "<p>Prix: " . htmlspecialchars($row['price']) . " €</p>";
                echo "<img src='" . htmlspecialchars($row['image_url']) . "' alt='" . htmlspecialchars($row['name']) . "'>";
                echo "<form method='POST' action='sell.php'>";
                echo "<input type='hidden' name='article_id' value='" . htmlspecialchars($row['id']) . "'>";
                echo "<button type='submit' name='add_to_cart'>Ajouter au Panier</button>";
                echo "</form>";
                echo "</div>";
            }
        } else {
            echo "Aucun article disponible à la vente.";
        }
        ?>
    </div>
</body>
</html>

<?php
// Fermer la connexion PDO (optionnel, mais une bonne pratique)
$pdo = null;
?>
