-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : ven. 10 jan. 2025 à 14:50
-- Version du serveur : 5.7.39
-- Version de PHP : 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `ecommerce_site`
--

-- --------------------------------------------------------

--
-- Structure de la table `Articles`
--

CREATE TABLE `Articles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `publication_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `author_id` int(11) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_sold` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `Articles`
--

INSERT INTO `Articles` (`id`, `name`, `description`, `price`, `publication_date`, `author_id`, `image_url`, `is_sold`) VALUES
(1, 'feu', 'fiefs', '111.00', '2025-01-10 13:10:12', 1, 'zefzfzf', 1),
(2, 'feu', 'fiefs', '111.00', '2025-01-10 13:10:19', 1, 'zefzfzf', 0),
(3, 'Laurene ', 'est souvent chaude ', '1000.00', '2025-01-10 13:12:11', 1, 'scqsc', 1),
(4, 'car', 'sqscqscqsc', '12.00', '2025-01-10 13:36:56', 1, 'spa', 0);

-- --------------------------------------------------------

--
-- Structure de la table `Cart`
--

CREATE TABLE `Cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `Invoices`
--

CREATE TABLE `Invoices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `amount` decimal(10,2) NOT NULL,
  `billing_address` text NOT NULL,
  `billing_city` varchar(100) NOT NULL,
  `billing_zip` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `Invoices`
--

INSERT INTO `Invoices` (`id`, `user_id`, `transaction_date`, `amount`, `billing_address`, `billing_city`, `billing_zip`) VALUES
(1, 1, '2025-01-10 14:31:23', '1111.00', '3030 Chemin du boulot ', 'antibes', '09277'),
(2, 1, '2025-01-10 14:33:25', '111.00', '3030 chemin du gcfgd', 'zeidu', 'zefbhu'),
(3, 1, '2025-01-10 14:41:01', '1000.00', 'drttdf', 'guugy', 'fgcyfg'),
(4, 2, '2025-01-10 15:44:03', '111.00', 'gr', 'gr', 'gre');

-- --------------------------------------------------------

--
-- Structure de la table `Stock`
--

CREATE TABLE `Stock` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `Users`
--

CREATE TABLE `Users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `balance` decimal(10,2) DEFAULT '0.00',
  `profile_picture` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `Users`
--

INSERT INTO `Users` (`id`, `username`, `email`, `password`, `balance`, `profile_picture`, `role`, `created_at`) VALUES
(1, 'toto', 'toto@gmail.com', '$2y$10$L61F45y8R1..bYobb.0iSup2S2r.TzOlQHiDKgjx168VLLUlF4PHi', '997778.00', NULL, 'user', '2025-01-04 17:51:54'),
(2, 'patr', 'pat@gmail.com', '$2y$10$AB4gh4HCy2CAWb0Zpyu6sOy7GWs.T04LhXZDhBQf9kNtZ50hQiDAm', '999889.00', NULL, 'user', '2025-01-10 11:40:34');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `Articles`
--
ALTER TABLE `Articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Index pour la table `Cart`
--
ALTER TABLE `Cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `Invoices`
--
ALTER TABLE `Invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `Stock`
--
ALTER TABLE `Stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `Articles`
--
ALTER TABLE `Articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `Cart`
--
ALTER TABLE `Cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `Invoices`
--
ALTER TABLE `Invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `Stock`
--
ALTER TABLE `Stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `Users`
--
ALTER TABLE `Users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `Articles`
--
ALTER TABLE `Articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Cart`
--
ALTER TABLE `Cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`article_id`) REFERENCES `Articles` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Invoices`
--
ALTER TABLE `Invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `Stock`
--
ALTER TABLE `Stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `Articles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
