<?php
require_once 'config.php';
require_once 'fonctions.php';

if (!est_connecte()) {
    header('Location: connexion.php');
    exit;
}

$comment_id = $_GET['id'] ?? 0;

// Récupérer le commentaire
$sql = "SELECT c.*, r.r_fk_question_id FROM forum_commentaires c 
        JOIN forum_reponse r ON c.r_id = r.r_id 
        WHERE c.comment_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$comment_id]);
$commentaire = $stmt->fetch();

if (!$commentaire) {
    die("Commentaire non trouvé");
}

// Vérifier que l'utilisateur est l'auteur ou admin
if ($commentaire['user_id'] != $_SESSION['user_id'] && !est_admin()) {
    die("Vous n'êtes pas autorisé à supprimer ce commentaire");
}

$question_id = $commentaire['r_fk_question_id'];

// Supprimer le commentaire
$sql = "DELETE FROM forum_commentaires WHERE comment_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$comment_id]);

logger('suppression_commentaire', "Commentaire $comment_id supprimé");

// Rediriger vers la question
header('Location: forum_question.php?id=' . $question_id);
exit;
?>
