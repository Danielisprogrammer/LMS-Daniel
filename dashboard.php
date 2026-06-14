<?php
session_start();

// Protection d'accès : si aucune session n'est ouverte, retour à la connexion
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: index.php');
    exit();
}

// Routage selon le rôle utilisateur
switch ($_SESSION['user_role']) {
    case 'etudiant':
        header('Location: etudiant.php');
        exit();
    case 'enseignant':
        header('Location: enseignant.php');
        exit();
    case 'promoteur':
        header('Location: promoteur.php');
        exit();
    default:
        // En cas de rôle inconnu, destruction et déconnexion forcée
        header('Location: logout.php');
        exit();
}
?>