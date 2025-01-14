<?php
session_start();
require 'db_connection.php'; 

// Vérification si l'utilisateur est administrateur
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'];

// Vérification du rôle de l'utilisateur
$stmt_role = $pdo->prepare("SELECT role FROM Users WHERE id = ?");
$stmt_role->execute([$user_id]);
$user_role = $stmt_role->fetch(PDO::FETCH_ASSOC);

if (!$user_role || $user_role['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Récupérer tous les utilisateurs
$stmt_users = $pdo->query("SELECT id, username, email, role, balance, created_at FROM Users");
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Récupérer tous les articles
$stmt_articles = $pdo->query("SELECT * FROM Articles");
$articles = $stmt_articles->fetchAll(PDO::FETCH_ASSOC);

// Récupérer toutes les factures
$stmt_invoices = $pdo->query("SELECT * FROM Invoices");
$invoices = $stmt_invoices->fetchAll(PDO::FETCH_ASSOC);

// Suppression d'un utilisateur
if (isset($_GET['delete_user'])) {
    $delete_user_id = (int)$_GET['delete_user'];
    $stmt_delete_user = $pdo->prepare("DELETE FROM Users WHERE id = ?");
    $stmt_delete_user->execute([$delete_user_id]);
    header("Location: admin_account.php");
    exit();
}

// Suppression d'un article
if (isset($_GET['delete_article'])) {
    $delete_article_id = (int)$_GET['delete_article'];
    $stmt_delete_article = $pdo->prepare("DELETE FROM Articles WHERE id = ?");
    $stmt_delete_article->execute([$delete_article_id]);
    header("Location: admin_account.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte Administrateur</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Administration</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Tableau de Bord Administrateur</h1>

        <!-- Gestion des utilisateurs -->
        <div class="my-5">
            <h2>Utilisateurs</h2>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nom d'utilisateur</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Solde (€)</th>
                        <th>Date de création</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']); ?></td>
                            <td><?= htmlspecialchars($user['username']); ?></td>
                            <td><?= htmlspecialchars($user['email']); ?></td>
                            <td><?= htmlspecialchars($user['role']); ?></td>
                            <td><?= htmlspecialchars($user['balance']); ?></td>
                            <td><?= htmlspecialchars($user['created_at']); ?></td>
                            <td>
                                <a href="admin_account.php?delete_user=<?= $user['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Gestion des articles -->
        <div class="my-5">
            <h2>Articles</h2>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Prix (€)</th>
                        <th>Auteur</th>
                        <th>Vendu</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td><?= htmlspecialchars($article['id']); ?></td>
                            <td><?= htmlspecialchars($article['name']); ?></td>
                            <td><?= htmlspecialchars($article['description']); ?></td>
                            <td><?= htmlspecialchars($article['price']); ?></td>
                            <td><?= htmlspecialchars($article['author_id']); ?></td>
                            <td><?= $article['is_sold'] ? 'Oui' : 'Non'; ?></td>
                            <td>
                                <a href="admin_account.php?delete_article=<?= $article['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet article ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Gestion des factures -->
        <div class="my-5">
            <h2>Factures</h2>
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Utilisateur</th>
                        <th>Date de transaction</th>
                        <th>Montant (€)</th>
                        <th>Adresse</th>
                        <th>Ville</th>
                        <th>Code Postal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><?= htmlspecialchars($invoice['id']); ?></td>
                            <td><?= htmlspecialchars($invoice['user_id']); ?></td>
                            <td><?= htmlspecialchars($invoice['transaction_date']); ?></td>
                            <td><?= htmlspecialchars($invoice['amount']); ?></td>
                            <td><?= htmlspecialchars($invoice['billing_address']); ?></td>
                            <td><?= htmlspecialchars($invoice['billing_city']); ?></td>
                            <td><?= htmlspecialchars($invoice['billing_zip']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Fermer la connexion PDO (optionnel)
$pdo = null;
?>
