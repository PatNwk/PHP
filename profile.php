<?php
session_start();
require 'db_connection.php'; 

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer l'ID de l'utilisateur connecté
$user_id = $_SESSION['user_id'];

// Récupérer les informations de l'utilisateur
$stmt_user = $pdo->prepare("SELECT * FROM Users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Utilisateur introuvable.";
    exit();
}

// Récupérer les articles postés par l'utilisateur
$stmt_articles = $pdo->prepare("SELECT * FROM Articles WHERE author_id = ? AND is_sold = 0");
$stmt_articles->execute([$user_id]);
$articles_posted = $stmt_articles->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les articles achetés par l'utilisateur
$stmt_purchased = $pdo->prepare("SELECT a.* FROM Articles a JOIN Cart c ON a.id = c.article_id WHERE c.user_id = ?");
$stmt_purchased->execute([$user_id]);
$purchased_articles = $stmt_purchased->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les factures de l'utilisateur
$stmt_invoices = $pdo->prepare("SELECT * FROM Invoices WHERE user_id = ?");
$stmt_invoices->execute([$user_id]);
$invoices = $stmt_invoices->fetchAll(PDO::FETCH_ASSOC);

// Traitement de la modification des informations de l'utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_info'])) {
    $new_email = $_POST['email'];
    $new_password = $_POST['password'];
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    // Mettre à jour les informations
    $stmt_update = $pdo->prepare("UPDATE Users SET email = ?, password = ? WHERE id = ?");
    if ($stmt_update->execute([$new_email, $new_password_hash, $user_id])) {
        echo "Informations mises à jour avec succès.";
        $_SESSION['email'] = $new_email;
    } else {
        echo "Erreur lors de la mise à jour.";
    }
}

// Traitement pour ajouter de l'argent au solde
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_balance'])) {
    $amount = floatval($_POST['amount']);
    if ($amount > 0) {
        $new_balance = $user['balance'] + $amount;
        $stmt_update_balance = $pdo->prepare("UPDATE Users SET balance = ? WHERE id = ?");
        if ($stmt_update_balance->execute([$new_balance, $user_id])) {
            echo "Solde mis à jour avec succès.";
            $user['balance'] = $new_balance; // Mettre à jour la variable locale
        } else {
            echo "Erreur lors de la mise à jour du solde.";
        }
    } else {
        echo "Veuillez entrer un montant valide.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<<<<<<< HEAD
    <link rel="stylesheet" href="profile.css">

=======
>>>>>>> 9462d51 (d)
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Mon Site</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="cart.php">Panier</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
<<<<<<< HEAD
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login.php">Se connecter</a></li>
                    <li class="nav-item"><a class="nav-link" href="register.php">S'inscrire</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
=======
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Mon Compte</h1>
        <div class="row">
            <div class="col-md-6">
                <h2>Informations de l'utilisateur</h2>
                <p><strong>Email :</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Nom d'utilisateur :</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Solde actuel :</strong> <?php echo number_format($user['balance'], 2, '.', ''); ?> €</p>
>>>>>>> 9462d51 (d)

                <!-- Formulaire pour ajouter de l'argent -->
                <h3>Ajouter au solde</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Montant à ajouter (€)</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                    </div>
                    <button type="submit" class="btn btn-success" name="add_balance">Ajouter au solde</button>
                </form>

                <!-- Formulaire de modification des informations -->
                <h3 class="mt-4">Modifier mes informations</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary" name="update_info">Mettre à jour</button>
                </form>
            </div>

            <div class="col-md-6">
                <h2>Mes Articles Publiés</h2>
                <?php if (count($articles_posted) > 0): ?>
                    <ul>
                        <?php foreach ($articles_posted as $article): ?>
                            <li>
                                <strong><?php echo htmlspecialchars($article['name']); ?></strong> - <?php echo htmlspecialchars($article['price']); ?> €
                                <p><?php echo htmlspecialchars($article['description']); ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Vous n'avez pas encore posté d'articles.</p>
                <?php endif; ?>

                <h2>Mes Factures</h2>
                <?php if (count($invoices) > 0): ?>
                    <ul>
                        <?php foreach ($invoices as $invoice): ?>
                            <li>
                                <strong>Facture #<?php echo $invoice['id']; ?></strong> - <?php echo htmlspecialchars(number_format($invoice['amount'], 2, '.', '')); ?> €
                                <p>Date : <?php echo htmlspecialchars($invoice['transaction_date']); ?></p>
                                <a href="invoices/invoice_<?php echo $invoice['id']; ?>.pdf" target="_blank" class="btn btn-info btn-sm">Voir la Facture</a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Vous n'avez pas encore de factures.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Fermer la connexion PDO (optionnel)
$pdo = null;
?>