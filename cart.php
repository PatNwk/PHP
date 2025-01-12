<?php
session_start();
require 'db_connection.php'; // Connexion à la base de données

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo "Vous devez être connecté pour voir votre panier.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les articles du panier
try {
    $sql = "SELECT Cart.id, Articles.name, Articles.price, Articles.image_url 
            FROM Cart 
            JOIN Articles ON Cart.article_id = Articles.id 
            WHERE Cart.user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération des articles du panier : " . $e->getMessage());
}

// Calculer le total du panier
$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'];
}

// Suppression d'un article du panier
if (isset($_POST['remove_from_cart'])) {
    $cart_item_id = $_POST['cart_item_id'];
    try {
        $sql = "DELETE FROM Cart WHERE id = :cart_item_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':cart_item_id', $cart_item_id, PDO::PARAM_INT);
        $stmt->execute();
        header("Location: cart.php");  // Rediriger après la suppression
        exit;
    } catch (PDOException $e) {
        echo "Erreur lors de la suppression de l'article du panier: " . $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>Mon Panier</title>
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">lamauvaiscoin</a>
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
    <h1>Mon Panier</h1>

    <!-- Bouton pour revenir à l'index -->
    <a href="index.php">
        <button type="button">Retour à l'Index</button>
    </a>

    <?php
    if (count($cart_items) > 0) {
        // Afficher les articles dans le panier
        echo "<h2>Articles dans votre panier :</h2>";
        foreach ($cart_items as $item) {
            echo "<div class='cart-item'>";
            echo "<h3>" . htmlspecialchars($item['name']) . "</h3>";
            echo "<img src='" . htmlspecialchars($item['image_url']) . "' alt='" . htmlspecialchars($item['name']) . "'>";
            echo "<p>Prix: " . htmlspecialchars($item['price']) . " €</p>";
            echo "<form method='POST' action='cart.php'>";
            echo "<input type='hidden' name='cart_item_id' value='" . htmlspecialchars($item['id']) . "'>";
            echo "<button type='submit' name='remove_from_cart'>Supprimer</button>";
            echo "</form>";
            echo "</div>";
        }

        // Afficher le total
        echo "<h3>Total: " . number_format($total_price, 2, '.', '') . " €</h3>";

        // Formulaire pour passer à l'achat
        echo "<form method='GET' action='validate.php'>";
        echo "<button type='submit' name='checkout'>Passer à l'achat</button>";
        echo "</form>";
    } else {
        echo "<p>Votre panier est vide.</p>";
    }
    ?>
</body>
</html>

<?php
// Fermer la connexion PDO (optionnel, mais une bonne pratique)
$pdo = null;
?>
