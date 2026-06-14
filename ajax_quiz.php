<?php
require_once 'config/db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    echo json_encode(['statut' => 'erreur', 'message' => 'Accès refusé']);
    exit();
}

$id_etudiant = $_SESSION['user_id'];
$id_lecon = intval($_POST['id_lecon']);
$reponse_etudiant = trim($_POST['reponse']);

// Récupération de la bonne réponse attendue
$stmt = $pdo->prepare("SELECT reponse_correcte FROM evaluations WHERE id_lecon = ?");
$stmt->execute([$id_lecon]);
$eval = $stmt->fetch();

if ($eval) {
    $note = ($reponse_etudiant === $eval['reponse_correcte']) ? 20 : 0;
    
    // Insertion ou mise à jour de la progression de l'étudiant
    $stmt_prog = $pdo->prepare("
        INSERT INTO progressions (id_etudiant, id_lecon, note) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE note = ?
    ");
    $stmt_prog->execute([$id_etudiant, $id_lecon, $note, $note]);

    if ($note === 20) {
        echo json_encode(['statut' => 'succes', 'message' => 'Excellente réponse ! Évaluation validée (20/20).']);
    } else {
        echo json_encode(['statut' => 'echec', 'message' => 'Réponse incorrecte (0/20). Veuillez relire le cours et réessayer.']);
    }
} else {
    echo json_encode(['statut' => 'erreur', 'message' => 'Aucune évaluation trouvée pour cette leçon.']);
}
?>