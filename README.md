# UniLMS — Système de Gestion d'Apprentissage Dynamique

UniLMS est une application de gestion académique légère et performante développée en PHP natif et stylisée avec Tailwind CSS. Elle permet de structurer les interactions entre l'administration, le corps enseignant et les étudiants.

## 🚀 Fonctionnalités Clés

- **Espace Promoteur (Admin) :** Gestion centralisée des comptes utilisateurs et customisation globale de l'établissement (Nom, Année académique).
- **Espace Enseignant :** Publication de modules de cours, assignation de devoirs textuels avec date limite et carnet de correction/notation des copies.
- **Espace Étudiant :** Catalogue d'inscription aux cours, soumission des travaux en ligne et consultation en temps réel du bulletin de notes.

## 🛠️ Technologies Utilisées

- **Back-End :** PHP 8 (Architecture MVC simplifiée, requêtes préparées PDO)
- **Front-End :** Tailwind CSS via CDN, Bootstrap Icons
- **Base de données :** MySQL / MariaDB

## 📦 Installation en Local

1. Clonez le dépôt : `git clone https://github.com/danielisprogrammer/LMS-Daniel-tp.git`
2. Déplacez le projet dans votre répertoire `htdocs` ou `www`.
3. Importez le fichier SQL fourni (ou créez les tables via l'interface d'administration).
4. Configurez vos accès dans `config/db.php`.
