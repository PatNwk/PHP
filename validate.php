<?php
session_start();
require 'db_connection.php'; // Connexion à la base de données
require('fpdf186/fpdf.php'); // Charger la bibliothèque FPDF

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo "Vous devez être connecté pour valider votre commande.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Récupérer les articles du panier
try {
    $sql = "SELECT Cart.id, Articles.id AS article_id, Articles.name, Articles.price, Articles.image_url 
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

// Vérifier le solde de l'utilisateur
$sql = "SELECT balance FROM Users WHERE id = :user_id";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_balance = $user['balance'];

// Finaliser l'achat (valider la commande)
if (isset($_POST['confirm_checkout'])) {
    $billing_address = $_POST['billing_address'];
    $billing_city = $_POST['billing_city'];
    $billing_zip = $_POST['billing_zip'];
    $payment_method = $_POST['payment_method'];

    // Vérifier si l'utilisateur a suffisamment de solde
    if ($user_balance < $total_price) {
        echo "Solde insuffisant pour effectuer cet achat.";
    } else {
        try {
            $pdo->beginTransaction();

            // Ajouter une entrée dans la table Invoices (factures)
            $sql = "INSERT INTO Invoices (user_id, amount, billing_address, billing_city, billing_zip, transaction_date) 
                    VALUES (:user_id, :amount, :billing_address, :billing_city, :billing_zip, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':amount', $total_price, PDO::PARAM_STR);
            $stmt->bindParam(':billing_address', $billing_address, PDO::PARAM_STR);
            $stmt->bindParam(':billing_city', $billing_city, PDO::PARAM_STR);
            $stmt->bindParam(':billing_zip', $billing_zip, PDO::PARAM_STR);
            $stmt->execute();

            // Récupérer l'ID de la facture générée
            $invoice_id = $pdo->lastInsertId();

            // Supprimer tous les articles du panier après l'achat
            $sql = "DELETE FROM Cart WHERE user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            // Mettre à jour le solde de l'utilisateur
            $new_balance = $user_balance - $total_price;
            $sql = "UPDATE Users SET balance = :new_balance WHERE id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':new_balance', $new_balance, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            // Marquer les articles comme vendus
            foreach ($cart_items as $item) {
                $sql = "UPDATE Articles SET is_sold = 1 WHERE id = :article_id";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':article_id', $item['article_id'], PDO::PARAM_INT);
                $stmt->execute();
            }

            // Commit de la transaction
            $pdo->commit();

            // Générer la facture PDF
            generateInvoicePDF($invoice_id);

            echo "Achat effectué avec succès! Votre facture a été générée.";
            header("Location: index.php"); // Rediriger vers la page d'index après l'achat
            exit;
        } catch (PDOException $e) {
            // Rollback en cas d'erreur
            $pdo->rollBack();
            echo "Erreur lors de l'achat : " . $e->getMessage();
        }
    }
}

// Fonction pour générer la facture en PDF
function generateInvoicePDF($invoice_id) {
    global $pdo;

    // Récupérer les détails de la facture
    $sql = "SELECT * FROM Invoices WHERE id = :invoice_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':invoice_id', $invoice_id, PDO::PARAM_INT);
    $stmt->execute();
    $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récupérer les articles de la commande
    $sql = "SELECT Articles.name, Articles.price FROM Cart 
            JOIN Articles ON Cart.article_id = Articles.id
            WHERE Cart.user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $invoice['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Création du PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 12);

    // Titre de la facture
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Facture - Commande #' . $invoice['id'], 0, 1, 'C');
    $pdf->Ln(10);

    // Détails de la facture avec bordures
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, 'Date: ', 0, 0, 'L');
    $pdf->Cell(0, 10, $invoice['transaction_date'], 0, 1, 'L');
    
    $pdf->Cell(40, 10, 'Adresse de facturation: ', 0, 0, 'L');
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(0, 10, wordwrap($invoice['billing_address'], 50), 0, 'L');  // Réduction à 50 caractères pour mieux gérer les adresses longues
    $pdf->SetFont('Arial', '', 12);
    
    $pdf->Cell(40, 10, 'Ville: ', 0, 0, 'L');
    $pdf->Cell(0, 10, $invoice['billing_city'], 0, 1, 'L');
    
    $pdf->Cell(40, 10, 'Code postal: ', 0, 0, 'L');
    $pdf->Cell(0, 10, $invoice['billing_zip'], 0, 1, 'L');
    
    $pdf->Cell(40, 10, 'Total: ', 0, 0, 'L');
    $pdf->Cell(0, 10, number_format($invoice['amount'], 2, '.', '') . ' e', 0, 1, 'L');
    $pdf->Ln(10);

    // Liste des articles avec tableau
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(90, 10, 'Article', 1, 0, 'C');
    $pdf->Cell(40, 10, 'Prix unitaire', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Nombre', 1, 0, 'C');
    $pdf->Cell(30, 10, 'Prix total', 1, 1, 'C');
    
    $pdf->SetFont('Arial', '', 12);

    $total_amount = 0;

    foreach ($cart_items as $item) {
        $article_name = $item['name'];
        $article_price = $item['price'];
        $quantity = 1; // Quantité par défaut
        $item_total = $article_price * $quantity;

        // Affichage de chaque article dans le tableau
        $pdf->Cell(90, 10, $article_name, 1, 0, 'L');
        $pdf->Cell(40, 10, number_format($article_price, 2, '.', '') . ' e', 1, 0, 'C');
        $pdf->Cell(30, 10, $quantity, 1, 0, 'C');
        $pdf->Cell(30, 10, number_format($item_total, 2, '.', '') . ' e', 1, 1, 'C');
        
        $total_amount += $item_total;
    }

    // Afficher le total général avec un fond pour faire ressortir le montant
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(160, 10, 'Total de la commande:', 1, 0, 'R');
    $pdf->Cell(0, 10, number_format($invoice['amount'], 2, '.', '') . ' e', 1, 1, 'L');
    $pdf->Ln(10);

    // Sortie du PDF
    $pdf->Output('F', 'invoices/invoice_' . $invoice_id . '.pdf');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>Valider la Commande</title>
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Lemauvaiscoin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="text-center mb-4">Valider votre commande</h1>
        <form method="POST">
            <div class="mb-3">
                <label for="billing_address" class="form-label">Adresse de facturation</label>
                <input type="text" class="form-control" id="billing_address" name="billing_address" required>
            </div>
            <div class="mb-3">
                <label for="billing_city" class="form-label">Ville</label>
                <input type="text" class="form-control" id="billing_city" name="billing_city" required>
            </div>
            <div class="mb-3">
                <label for="billing_zip" class="form-label">Code postal</label>
                <input type="text" class="form-control" id="billing_zip" name="billing_zip" required>
            </div>
            <div class="mb-3">
                <label for="payment_method" class="form-label">Méthode de paiement</label>
                <select class="form-control" id="payment_method" name="payment_method" required>
                    <option value="credit_card">Carte de crédit</option>
                    <option value="paypal">PayPal</option>
                </select>
            </div>
            <button type="submit" name="confirm_checkout" class="btn btn-success w-100">Confirmer la commande</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
