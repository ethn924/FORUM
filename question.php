<?php
require_once 'config.php';
require_once 'fonctions.php';

$question_id = $_GET['id'] ?? 0;

try {
    // Incrémenter le compteur de vues
    $sql = "UPDATE forum_question SET views_count = views_count + 1 WHERE q_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$question_id]);

    // Récupérer la question
    $sql = "SELECT q.*, u.login as auteur_question, u.avatar as auteur_avatar, u.reputation as auteur_reputation 
            FROM forum_question q 
            JOIN forum_utilisateur u ON q.user_id = u.user_id 
            WHERE q.q_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$question_id]);
    $question = $stmt->fetch();

    if (!$question) {
        throw new Exception("Question non trouvée");
    }

    // Fermer la question si demandé
    if (isset($_POST['fermer_question']) && est_connecte() && $_SESSION['user_id'] == $question['user_id']) {
        $sql = "UPDATE forum_question SET status = 'closed' WHERE q_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$question_id]);
        $question['status'] = 'closed';
        
        logger('fermeture_question', "Question {$question_id} fermée");
        creer_notification(
            $question['user_id'],
            'Question fermée',
            'Votre question a été marquée comme résolue.',
            "question.php?id=$question_id"
        );
    }

    // Marquer comme solution
    if (isset($_POST['marquer_solution']) && est_connecte()) {
        $r_id = $_POST['r_id'] ?? 0;
        if (marquer_comme_solution($pdo, $r_id, $_SESSION['user_id'])) {
            $succes = "Réponse marquée comme solution !";
            
            // Mettre à jour la dernière activité
            $sql = "UPDATE forum_question SET last_activity = NOW() WHERE q_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$question_id]);
        }
    }

    // Récupérer les réponses triées par solutions puis likes
    $sql = "SELECT r.*, u.login as auteur_reponse, u.avatar as auteur_avatar, u.reputation as auteur_reputation,
            (SELECT COUNT(*) FROM forum_likes WHERE r_id = r.r_id) as likes_count,
            (SELECT COUNT(*) FROM forum_commentaires c WHERE c.r_id = r.r_id) as commentaires_count
            FROM forum_reponse r 
            JOIN forum_utilisateur u ON r.user_id = u.user_id 
            WHERE r.r_fk_question_id = ? 
            ORDER BY r.is_solution DESC, likes_count DESC, r.r_date_ajout ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$question_id]);
    $reponses = $stmt->fetchAll();

    // Récupérer les commentaires pour chaque réponse
    $commentaires_par_reponse = [];
    foreach ($reponses as $reponse) {
        $commentaires_par_reponse[$reponse['r_id']] = get_commentaires($pdo, $reponse['r_id']);
    }

} catch (Exception $e) {
    $erreur = gerer_exception($e);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($question['q_titre'] ?? 'Question non trouvée') ?></title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="question.css">
</head>
<body>
    <?php afficher_header(); ?>

    <main>
        <?php if (isset($erreur)): ?>
            <?= afficher_erreur($erreur) ?>
        <?php endif; ?>

        <?php if (isset($succes)): ?>
            <?= afficher_succes($succes) ?>
        <?php endif; ?>

        <?php if ($question): ?>
            <article class="question">
                <div class="user-info">
                    <?php if ($question['auteur_avatar']): ?>
                        <img src="<?= htmlspecialchars($question['auteur_avatar']) ?>" alt="Avatar" class="user-avatar">
                    <?php endif; ?>
                    <div>
                        <strong><?= htmlspecialchars($question['auteur_question']) ?></strong>
                        <div class="user-reputation">🏆 <?= $question['auteur_reputation'] ?> points</div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: start;">
                    <div>
                        <h2 style="margin-top: 0;"><?= htmlspecialchars($question['q_titre']) ?></h2>
                        
                        <div style="color: #666; margin-bottom: 20px;">
                            <span class="timestamp">
                                <?= formater_date_fr($question['q_date_ajout']) ?>
                                • 👁️ <?= $question['views_count'] ?> vues
                            </span>
                            <span class="status-<?= $question['status'] ?>" style="margin-left: 15px;">
                                <?= $question['status'] == 'open' ? '🟢 Ouverte' : '🔴 Fermée' ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if (est_connecte()): ?>
                        <button class="btn-favoris" data-q-id="<?= $question['q_id'] ?>" style="background: none; border: 2px solid #f39c12; color: #f39c12; padding: 8px 12px; font-size: 20px;" title="Ajouter aux favoris">
                            <?php if (est_en_favoris($pdo, $_SESSION['user_id'], $question['q_id'])): ?>
                                ⭐
                            <?php else: ?>
                                ☆
                            <?php endif; ?>
                        </button>
                    <?php endif; ?>
                </div>

                <div style="line-height: 1.6;">
                    <?= nl2br(htmlspecialchars($question['q_contenu'])) ?>
                </div>
                
                <?php if (est_connecte()): ?>
                    <div class="response-actions">
                        <div class="response-action-left">
                            <?php if ($_SESSION['user_id'] == $question['user_id'] && $question['status'] == 'open'): ?>
                                <form method="post" style="display: inline;">
                                    <button type="submit" name="fermer_question" 
                                            onclick="return confirm('Êtes-vous sûr de vouloir fermer cette question ?')"
                                            class="danger">
                                        🔒 Marquer comme résolu
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        
                        <div class="response-action-right">
                            <?php if ($_SESSION['user_id'] == $question['user_id'] || est_admin()): ?>
                                <a href="modifier_question.php?id=<?= $question['q_id'] ?>" class="btn">
                                    ✏️ Modifier
                                </a>
                                <a href="supprimer_question.php?id=<?= $question['q_id'] ?>" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette question ?')"
                                   class="btn danger">
                                    🗑️ Supprimer
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </article>

            <section class="reponses">
                <h2>Réponses (<?= count($reponses) ?>)</h2>
                
                <?php if (empty($reponses)): ?>
                    <div class="no-responses">
                        <p>🤔 Aucune réponse pour le moment.</p>
                        <p>Soyez le premier à répondre à cette question !</p>
                    </div>
                <?php else: ?>
                    <?php foreach($reponses as $reponse): ?>
                        <div class="reponse <?= $reponse['is_solution'] ? 'reponse-solution' : '' ?>">
                            <?php if ($reponse['is_solution']): ?>
                                <div class="solution-accepted">
                                    ✅ Solution acceptée
                                </div>
                            <?php endif; ?>

                            <div class="user-info">
                                <?php if ($reponse['auteur_avatar']): ?>
                                    <img src="<?= htmlspecialchars($reponse['auteur_avatar']) ?>" alt="Avatar" class="user-avatar">
                                <?php endif; ?>
                                <div>
                                    <strong><?= htmlspecialchars($reponse['auteur_reponse']) ?></strong>
                                    <div class="user-reputation">🏆 <?= $reponse['auteur_reputation'] ?> points</div>
                                </div>
                                <span class="timestamp">
                                    <?= formater_date_relative($reponse['r_date_ajout']) ?>
                                </span>
                            </div>

                            <div style="line-height: 1.6; margin: 15px 0;">
                                <?= nl2br(htmlspecialchars($reponse['r_contenu'])) ?>
                            </div>
                            
                            <div class="response-actions">
                                <div class="response-action-left">
                                    <button class="like-btn <?= user_has_liked($pdo, $_SESSION['user_id'] ?? 0, $reponse['r_id']) ? 'liked' : '' ?>" 
                                            data-r-id="<?= $reponse['r_id'] ?>" 
                                            <?= !est_connecte() ? 'disabled' : '' ?>>
                                        👍 <span class="likes-count"><?= $reponse['likes_count'] ?></span>
                                    </button>
                                    
                                    <?php if (est_connecte()): ?>
                                        <button class="btn-commenter" 
                                                data-r-id="<?= $reponse['r_id'] ?>">
                                            💬 Commenter
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <div class="response-action-right">
                                    <?php if (est_connecte() && ($_SESSION['user_id'] == $question['user_id'] || est_admin()) && !$reponse['is_solution'] && $question['status'] == 'open'): ?>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="r_id" value="<?= $reponse['r_id'] ?>">
                                            <button type="submit" name="marquer_solution" 
                                                    onclick="return confirm('Marquer cette réponse comme solution ?')"
                                                    class="success">
                                                ✅ Marquer comme solution
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Commentaires existants -->
                            <?php if (!empty($commentaires_par_reponse[$reponse['r_id']])): ?>
                                <div style="margin-top: 20px;">
                                    <h4>💬 Commentaires (<?= count($commentaires_par_reponse[$reponse['r_id']]) ?>)</h4>
                                    <?php foreach($commentaires_par_reponse[$reponse['r_id']] as $commentaire): ?>
                                        <div class="commentaire">
                                            <div class="user-info">
                                                <?php if ($commentaire['avatar']): ?>
                                                    <img src="<?= htmlspecialchars($commentaire['avatar']) ?>" alt="Avatar" class="user-avatar" style="width: 30px; height: 30px;">
                                                <?php endif; ?>
                                                <div>
                                                    <strong><?= htmlspecialchars($commentaire['login']) ?></strong>
                                                    <div class="user-reputation">🏆 <?= $commentaire['reputation'] ?> points</div>
                                                </div>
                                                <span class="timestamp">
                                                    <?= formater_date_relative($commentaire['created_at']) ?>
                                                </span>
                                            </div>
                                            <div style="line-height: 1.5; margin-top: 10px;">
                                                <?= nl2br(htmlspecialchars($commentaire['contenu'])) ?>
                                            </div>
                                            
                                            <?php if (est_connecte() && ($_SESSION['user_id'] == $commentaire['user_id'] || est_admin())): ?>
                                                <div style="margin-top: 10px; display: flex; gap: 10px;">
                                                    <a href="modifier_commentaire.php?id=<?= $commentaire['comment_id'] ?>" style="font-size: 12px; color: #3498db; text-decoration: none;">✏️ Modifier</a>
                                                    <a href="supprimer_commentaire.php?id=<?= $commentaire['comment_id'] ?>" onclick="return confirm('Êtes-vous sûr ?')" style="font-size: 12px; color: #e74c3c; text-decoration: none;">🗑️ Supprimer</a>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Formulaire de commentaire -->
                            <?php if (est_connecte() && $question['status'] == 'open'): ?>
                                <form id="form-commentaire-<?= $reponse['r_id'] ?>" 
                                      action="ajouter_commentaire.php" 
                                      method="post" 
                                      class="comment-form">
                                    <input type="hidden" name="r_id" value="<?= $reponse['r_id'] ?>">
                                    <div>
                                        <label><strong>Votre commentaire :</strong></label>
                                        <textarea name="contenu" class="comment-textarea" 
                                                  placeholder="Répondez à cette réponse..." required></textarea>
                                    </div>
                                    <div class="comment-buttons">
                                        <button type="submit">💬 Poster le commentaire</button>
                                        <button type="button" 
                                                onclick="document.getElementById('form-commentaire-<?= $reponse['r_id'] ?>').style.display='none'">
                                            Annuler
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <section class="ajouter-reponse">
                <?php if (est_connecte()): ?>
                    <?php if ($question['status'] == 'open'): ?>
                        <h3>✍️ Répondre à cette question</h3>
                        <form action="ajouter_reponse.php" method="post" class="card">
                            <input type="hidden" name="question_id" value="<?= $question['q_id'] ?>">
                            <div class="form-group">
                                <label><strong>Votre réponse :</strong></label>
                                <textarea name="contenu" placeholder="Partagez votre expertise..." required></textarea>
                            </div>
                            <button type="submit" class="success">📤 Poster la réponse</button>
                        </form>
                    <?php else: ?>
                        <div class="closed-message">
                            <p>🔒 <strong>Cette question est fermée.</strong> Vous ne pouvez plus y répondre.</p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="not-logged-in">
                        <p>Vous devez être <a href="connexion.php">connecté</a> pour répondre à cette question.</p>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <p>Forum de discussion © 2025 - <?= date('Y') ?></p>
    </footer>

    <script src="question.js"></script>
</body>
</html>