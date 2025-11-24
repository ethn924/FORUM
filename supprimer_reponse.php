<?php
require_once 'config.php';

if (!est_connecte()) {
    header('Location: connexion.php');
    exit;
}

$reponse_id = $_GET['id'] ?? 0;

// Récupérer l'ID de la question avant suppression
$sql = "SELECT r_fk_question_id FROM forum_reponse WHERE r_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$reponse_id]);
$reponse = $stmt->fetch();

if ($reponse) {
    // Vérifier les droits (propriétaire ou admin)
    $sql = "SELECT user_id FROM forum_reponse WHERE r_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$reponse_id]);
    $reponse_user = $stmt->fetch();
    
    if ($reponse_user && ($reponse_user['user_id'] == $_SESSION['user_id'] || est_admin())) {
        $sql = "DELETE FROM forum_reponse WHERE r_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$reponse_id]);
    }
    
    header("Location: question.php?id=" . $reponse['r_fk_question_id']);
} else {
    header('Location: index.php');
}
exit;
?>