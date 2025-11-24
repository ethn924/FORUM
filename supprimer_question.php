<?php
require_once 'config.php';

if (!est_connecte()) {
    header('Location: connexion.php');
    exit;
}

$question_id = $_GET['id'] ?? 0;

// Vérifier que l'utilisateur est bien le propriétaire ou admin
$sql = "SELECT user_id FROM forum_question WHERE q_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$question_id]);
$question = $stmt->fetch();

if ($question && ($question['user_id'] == $_SESSION['user_id'] || est_admin())) {
    // Supprimer d'abord les réponses associées
    $sql = "DELETE FROM forum_reponse WHERE r_fk_question_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$question_id]);
    
    // Puis supprimer la question
    $sql = "DELETE FROM forum_question WHERE q_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$question_id]);
}

header('Location: index.php');
exit;
?>