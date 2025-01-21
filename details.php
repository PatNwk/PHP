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

// Vérifier si un article a été sélectionné
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

// Ajouter l'article au panier si le bouton est cliqué
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $article_id = intval($_POST['article_id']);

    try {
        // Vérifier si l'article est déjà dans le panier
        $sql = "SELECT * FROM Cart WHERE user_id = :user_id AND article_id = :article_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $user_id,
            ':article_id' => $article_id
        ]);

        if ($stmt->rowCount() === 0) {
            // Ajouter l'article au panier
            $sql = "INSERT INTO Cart (user_id, article_id) VALUES (:user_id, :article_id)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id' => $user_id,
                ':article_id' => $article_id
            ]);
        }

        // Rediriger vers le panier après l'ajout
        header("Location: cart.php");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de l'ajout au panier : " . $e->getMessage());
    }
}

// Vérifier si l'utilisateur connecté est le propriétaire de l'article ou un administrateur
$is_owner = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $article['seller_id'];
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Gestion de la modification de l'article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_article']) && ($is_owner || $is_admin)) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $image_url = $_POST['image_url'];

    try {
        $sql = "UPDATE Articles SET name = :name, description = :description, price = :price, image_url = :image_url WHERE id = :article_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':description' => $description,
            ':price' => $price,
            ':image_url' => $image_url,
            ':article_id' => $article_id
        ]);

        header("Location: details.php?article_id=$article_id");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de la mise à jour de l'article : " . $e->getMessage());
    }
}

// Gestion de la suppression de l'article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_article']) && ($is_owner || $is_admin)) {
    try {
        $sql = "DELETE FROM Articles WHERE id = :article_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':article_id' => $article_id]);

        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        die("Erreur lors de la suppression de l'article : " . $e->getMessage());
    }
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
                        <form method="POST" class="mt-4">
                            <input type="hidden" name="article_id" value="<?php echo htmlspecialchars($article['id']); ?>">
                            <button type="submit" name="add_to_cart" class="btn btn-success btn-lg">🛒 Ajouter au Panier</button>
                        </form>

                        <!-- Section pour les propriétaires ou administrateurs -->
                        <?php if ($is_owner || $is_admin): ?>
                            <hr>
                            <h3>Modifier ou Supprimer l'Article</h3>
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($article['name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required><?php echo htmlspecialchars($article['description']); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="price" class="form-label">Prix (€)</label>
                                    <input type="number" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($article['price']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="image_url" class="form-label">URL de l'image</label>
                                    <input type="text" class="form-control" id="image_url" name="image_url" value="<?php echo htmlspecialchars($article['image_url']); ?>" required>
                                </div>
                                <button type="submit" name="update_article" class="btn btn-primary">Mettre à jour</button>
                                <button type="submit" name="delete_article" class="btn btn-danger">Supprimer</button>
                            </form>
                        <?php endif; ?>

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
