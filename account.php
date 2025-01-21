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

// Récupérer les achats de l'utilisateur
$stmt_purchases = $pdo->prepare("SELECT * FROM Purchases WHERE user_id = ?");
$stmt_purchases->execute([$user_id]);
$purchases = $stmt_purchases->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les factures de l'utilisateur
$stmt_invoices = $pdo->prepare("SELECT * FROM Invoices WHERE user_id = ?");
$stmt_invoices->execute([$user_id]);
$invoices = $stmt_invoices->fetchAll(PDO::FETCH_ASSOC);

// Traitement de la modification des informations de l'utilisateur
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_info'])) {
    $new_email = $_POST['email'];
    $new_password = $_POST['password'];
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    $profile_image_path = $user['profile_picture']; // Conserver l'image actuelle par défaut

    // Gestion de l'upload de l'image
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['profile_image'];
        $image_name = time() . "_" . basename($image['name']);
        $target_dir = "uploads/profiles/";
        $target_file = $target_dir . $image_name;

        // Vérifier et déplacer l'image uploadée
        if (move_uploaded_file($image['tmp_name'], $target_file)) {
            $profile_image_path = $target_file; // Mettre à jour le chemin de l'image
        } else {
            echo "Erreur lors de l'upload de l'image.";
        }
    }

    // Mettre à jour les informations dans la base de données
    $stmt_update = $pdo->prepare("UPDATE Users SET email = ?, password = ?, profile_picture = ? WHERE id = ?");
    if ($stmt_update->execute([$new_email, $new_password_hash, $profile_image_path, $user_id])) {
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
    <style>
        body {
            background-color: #f8f9fa;
        }
        .profile-card {
            background: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        .profile-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .profile-info h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #333;
        }
        .form-control {
            border-radius: 10px;
        }
        .btn-custom {
            background: #0d6efd;
            color: white;
            border-radius: 10px;
        }
        .btn-custom:hover {
            background: #0056b3;
        }
        .section-title {
            margin-top: 40px;
            margin-bottom: 20px;
            font-size: 1.8rem;
            color: #495057;
        }
    </style>
</head>
<body>

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
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-4">
                <div class="profile-card text-center">
                    <?php if ($user['profile_picture']): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Image de profil" class="profile-image">
                    <?php else: ?>
                        <img src="default-profile.png" alt="Image de profil par défaut" class="profile-image">
                    <?php endif; ?>
                    <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>
            <div class="col-md-8">
                <h1 class="section-title">Modifier mes informations</h1>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="profile_image" class="form-label">Image de profil</label>
                        <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-custom" name="update_info">Mettre à jour</button>
                </form>

                <h1 class="section-title">Mes Achats</h1>
                <ul class="list-group">
                    <?php if (count($purchases) > 0): ?>
                        <?php foreach ($purchases as $purchase): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Achat du <?php echo date("d/m/Y", strtotime($purchase['purchase_date'])); ?>
                                <?php 
                                $pdf_path = 'invoices/' . $purchase['pdf_filename'];
                                if (file_exists($pdf_path)): ?>
                                    <a href="<?php echo $pdf_path; ?>" class="btn btn-primary btn-sm" target="_blank" download>Télécharger le PDF</a>
                                <?php else: ?>
                                    <span class="text-danger">PDF non trouvé</span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item">Aucun achat trouvé.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <footer class="text-center mt-5 py-4 bg-light">
        <p class="mb-0">© 2025 Mon Site. Tous droits réservés.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
