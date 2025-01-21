<?php
session_start();
require 'db_connection.php'; 

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Simuler un processus d'achat (exemple de produit et prix)
$product_name = "Produit Exemple";
$product_price = 50.00;

// Nom du fichier PDF généré pour l'achat
$pdf_filename = "facture_" . time() . ".pdf";  
$pdf_path = "invoices/" . $pdf_filename;  // Chemin du fichier PDF

// Génération du PDF (en utilisant FPDF ou TCPDF, ici on fait juste un fichier texte pour l'exemple)
file_put_contents($pdf_path, "Facture pour l'achat de " . $product_name . "\nPrix : " . $product_price . " €");

// Insérer l'achat dans la base de données
$stmt = $pdo->prepare("INSERT INTO Purchases (user_id, pdf_path, purchase_date) VALUES (?, ?, ?)");
$purchase_date = date("Y-m-d H:i:s");  // Date actuelle

if ($stmt->execute([$user_id, $pdf_filename, $purchase_date])) {
    echo "Achat enregistré avec succès.";
} else {
    echo "Erreur lors de l'enregistrement de l'achat.";
}
?>
