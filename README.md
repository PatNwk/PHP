# Lemauvaiscoin - Système de gestion de commandes

Lemauvaiscoin est une plateforme de commerce en ligne où les utilisateurs peuvent acheter des articles et générer une facture en format PDF pour leurs achats. Ce projet utilise PHP pour la logique côté serveur, MySQL pour la gestion de la base de données, et FPDF pour la génération de factures au format PDF.

## Fonctionnalités principales

- **Système de panier d'achat** : Les utilisateurs peuvent ajouter des articles à leur panier, puis procéder à la validation de la commande.
- **Gestion des utilisateurs** : Les utilisateurs peuvent se connecter pour accéder à leur panier et effectuer des achats.
- **Validation de la commande** : Lors de la validation de la commande, l'utilisateur entre son adresse de facturation et choisit une méthode de paiement.
- **Vérification du solde de l'utilisateur** : L'achat est possible uniquement si l'utilisateur a suffisamment de crédit sur son compte.
- **Génération de facture en PDF** : Une fois l'achat effectué, une facture est générée au format PDF et contient les détails de la commande (articles, prix, etc.).

## Prérequis

Avant de commencer, assurez-vous d'avoir les outils suivants installés sur votre machine :

- PHP 7.4 ou version ultérieure
- MySQL ou MariaDB
- Composer (si vous avez besoin d'installer des dépendances PHP supplémentaires)
- FPDF (pour générer des PDF)
  
## Installation

1. **Clonez le dépôt Git**

   Clonez ce projet sur votre machine locale en utilisant Git :

   ```bash
   git clone https://github.com/PatNwk/PHP
   cd lemauvaiscoin
