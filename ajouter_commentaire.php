<?php
require_once 'config.php';

if (!est_connecte()) {
    $_SESSION['erreur'] = "Vous devez être connecté pour commenter";
    header('Location: connexion.php');
    exit;
}

if ($_POST) {
    try {
        $r_id = valider_entree($_POST['r_id'] ?? 0, 'int');
        $contenu = valider_entree($_POST['contenu'] ?? '');
        
        if (!$r_id || !$contenu) {
            throw new Exception("Données manquantes");
        }
        
        // Valider la longueur
        if (strlen($contenu) < 5) {
            throw new Exception("Le commentaire doit contenir au moins 5 caractères");
        }
        
        if (strlen($contenu) > 1000) {
            throw new Exception("Le commentaire ne peut pas dépasser 1000 caractères");
        }
        
        // Vérifier que la réponse existe et que la question est ouverte
        $sql = "SELECT r.*, q.q_id, q.status, q.user_id as auteur_question_id 
                FROM forum_reponse r 
                JOIN forum_question q ON r.r_fk_question_id = q.q_id 
                WHERE r.r_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$r_id]);
        $reponse = $stmt->fetch();
        
        if (!$reponse) {
            throw new Exception("Réponse non trouvée");
        }
        
        if ($reponse['status'] != 'open') {
            throw new Exception("Impossible de commenter une question fermée");
        }
        
        // Insérer le commentaire
        $sql = "INSERT INTO forum_commentaires (contenu, user_id, r_id) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$contenu, $_SESSION['user_id'], $r_id]);
        
        // Mettre à jour la dernière activité de la question
        $sql = "UPDATE forum_question SET last_activity = NOW() WHERE q_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$reponse['q_id']]);
        
        // Logger l'action
        logger('ajout_commentaire', "Commentaire sur réponse $r_id");
        
        // Notifier l'auteur de la réponse (sauf si c'est le commentateur lui-même)
        if ($reponse['user_id'] != $_SESSION['user_id']) {
            creer_notification(
                $reponse['user_id'],
                'Nouveau commentaire sur votre réponse',
                htmlspecialchars($_SESSION['login']) . " a commenté votre réponse",
                "question.php?id=" . $reponse['q_id']
            );
        }
        
        // Notifier l'auteur de la question (s'il n'est ni l'auteur de la réponse ni le commentateur)
        if ($reponse['auteur_question_id'] != $_SESSION['user_id'] && $reponse['auteur_question_id'] != $reponse['user_id']) {
            creer_notification(
                $reponse['auteur_question_id'],
                'Nouveau commentaire sur une réponse',
                "Nouveau commentaire sur une réponse à votre question",
                "question.php?id=" . $reponse['q_id']
            );
        }
        
        $_SESSION['succes'] = "Commentaire ajouté avec succès";
        header("Location: question.php?id=" . $reponse['q_id']);
        exit;
        
    } catch (Exception $e) {
        error_log("Erreur ajout commentaire: " . $e->getMessage());
        $_SESSION['erreur'] = $e->getMessage();
        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
        exit;
    }
}

header('Location: index.php');
exit;
?>