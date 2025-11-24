<?php
require_once 'config.php';

if (!est_connecte()) {
    $_SESSION['erreur'] = "Vous devez être connecté pour répondre";
    header('Location: connexion.php');
    exit;
}

if ($_POST) {
    $question_id = $_POST['question_id'] ?? 0;
    $contenu = $_POST['contenu'] ?? '';
    
    // Vérifier que la question est ouverte
    $sql = "SELECT status FROM forum_question WHERE q_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$question_id]);
    $question = $stmt->fetch();
    
    if (!$question || $question['status'] != 'open') {
        $_SESSION['erreur'] = "Impossible de répondre à cette question (fermée ou inexistante)";
        header("Location: question.php?id=$question_id");
        exit;
    }
    
    if ($question_id && $contenu) {
        $sql = "INSERT INTO forum_reponse (r_contenu, r_date_ajout, r_fk_question_id, user_id) VALUES (?, NOW(), ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$contenu, $question_id, $_SESSION['user_id']])) {
            header("Location: question.php?id=$question_id");
            exit;
        }
    }
}

header('Location: index.php');
exit;
?>