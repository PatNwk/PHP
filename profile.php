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

// Vérifier si un ID utilisateur est passé
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];

    try {
        // Récupérer les informations du vendeur
        $stmt = $pdo->prepare("SELECT * FROM Users WHERE id = :user_id");
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo "Utilisateur non trouvé.";
            exit;
        }
    } catch (PDOException $e) {
        die("Erreur lors de la récupération du profil utilisateur : " . $e->getMessage());
    }
} else {
    echo "Aucun utilisateur sélectionné.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil du Vendeur</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

    <div class="container mt-5">
        <h1>Profil du Vendeur</h1>
        <div class="card mt-3">
            <div class="card-body">
                <h2>Nom d'utilisateur : <?php echo htmlspecialchars($user['username']); ?></h2>
                <p>Email : <?php echo htmlspecialchars($user['email']); ?></p>
                <!-- Ajoutez ici d'autres informations nécessaires -->
            </div>
        </div>

        <!-- Lien retour -->
        <a href="index.php" class="btn btn-primary mt-3">⬅ Retour à l'accueil</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$pdo = null;
?>
