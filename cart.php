<?php
session_start();
require 'db_connection.php'; // Connexion à la base de données

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Suppression d'un article du panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $article_id = intval($_POST['article_id']); // Sécuriser l'entrée utilisateur

    try {
        // Supprimez l'article du panier (table Cart uniquement)
        $sql = "DELETE FROM Cart WHERE user_id = :user_id AND article_id = :article_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':article_id' => $article_id
        ]);

        header("Location: cart.php"); // Rafraîchir la page pour afficher les changements
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de la suppression de l'article du panier : " . $e->getMessage());
    }
}

// Récupérez les articles dans le panier
try {
    $sql = "SELECT Articles.id, Articles.name, Articles.price, Articles.image_url 
            FROM Cart 
            JOIN Articles ON Cart.article_id = Articles.id 
            WHERE Cart.user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $user_id]);
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur lors de la récupération du panier : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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

    <!-- Contenu principal -->
    <div class="container mt-5">
        <h1>Mon Panier</h1>
        <?php if (count($cart_items) > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Article</th>
                        <th>Prix</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td>
                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 50px; height: auto; margin-right: 10px;">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </td>
                            <td><?php echo number_format($item['price'], 2, '.', ''); ?> €</td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="article_id" value="<?php echo htmlspecialchars($item['id']); ?>">
                                    <button type="submit" name="remove_item" class="btn btn-danger btn-sm">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p>Total : 
                <strong>
                    <?php
                        $total = array_reduce($cart_items, function($sum, $item) {
                            return $sum + $item['price'];
                        }, 0);
                        echo number_format($total, 2, '.', '') . ' €';
                    ?>
                </strong>
            </p>
            <a href="validate.php" class="btn btn-success">Passer à l'achat</a>
        <?php else: ?>
            <p>Votre panier est vide.</p>
        <?php endif; ?>
        <a href="index.php" class="btn btn-primary mt-3">Retour à l'Index</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
