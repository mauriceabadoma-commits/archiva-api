-- Archiva — schéma de base de données
-- À importer dans phpMyAdmin (fourni par XAMPP) : onglet "Importer",
-- ou coller directement dans l'onglet "SQL".

CREATE DATABASE IF NOT EXISTS archiva CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE archiva;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    level VARCHAR(10) NOT NULL DEFAULT 'X1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    level VARCHAR(10) NOT NULL DEFAULT 'X1',
    image VARCHAR(255) NOT NULL DEFAULT 'web-intro.svg',
    author VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Données de démonstration (les mêmes CERs que l'ancien cers.json)
INSERT INTO cers (title, description, level, image, author) VALUES
('Prosit 3.2 Annuaire Active Directory',
 "L'Annuaire Active Directory (AD) est un service de gestion des identités et des accès utilisé principalement dans les environnements Windows. Il permet de centraliser les comptes utilisateurs, les groupes, les ordinateurs et les stratégies de sécurité d'une organisation.",
 'X2', 'active-directory.svg', 'Sadjo Mamadou'),

('Prosit 2.2 Modélisation UML',
 "Le Langage de Modélisation Unifié, de l'anglais Unified Modeling Language, est un langage de modélisation graphique à base de pictogrammes conçu comme une méthode normalisée de visualisation dans les domaines du développement logiciel.",
 'X1', 'uml.svg', 'Pauline Lock'),

('Prosit 4.1 Développement avancé',
 "Advance Web Development fait référence au processus de création de sites Web dynamiques et interactifs qui vont au-delà des pages Web statiques. Cela implique l'utilisation de techniques et de technologies de codage avancées.",
 'X3', 'advanced-dev.svg', 'Providence Djekoun.'),

('Prosit 3.3 API et Webservice',
 "Les API sont principalement axées sur la communication entre applications pour l'accès aux fonctionnalités. L'EDI se concentre sur l'échange de documents entre systèmes d'information.",
 'X2', 'api.svg', 'Daryl Noupik'),

('Prosit 4.5 Architecture microservices',
 "Une architecture de microservices est un type d'architecture d'application dans laquelle l'application est développée sous la forme d'un ensemble de services indépendants, déployables séparément.",
 'X3', 'microservices.svg', 'Providence Djekoun.'),

('Prosit 4.5 Architecture distribuée',
 "L'architecture distribuée ou l'informatique distribuée désigne un système d'information ou un réseau pour lequel l'ensemble des ressources disponibles ne se trouvent pas au même endroit.",
 'X3', 'distributed.svg', 'Sadjo Mamadou'),

('Prosit 1.7 Introduction au développement Web',
 "Ce prosit présente les fondamentaux du développement Web : structurer une page avec HTML, la mettre en forme avec CSS, appliquer les bonnes pratiques DRY et BEM, et adapter l'interface à tous les écrans.",
 'X1', 'web-intro.svg', 'Providence Djekoun.');
