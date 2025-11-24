<?php
require_once 'config.php';
require_once 'fonctions.php';

if (!est_connecte()) {
    header('Location: connexion.php');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$questions_par_page = 10;

// Compter le total de favoris
$sql_count = "SELECT COUNT(*) as total FROM forum_favoris WHERE user_id = ?";
$stmt = $pdo->prepare($sql_count);
$stmt->execute([$_SESSION['user_id']]);
$total = $stmt->fetch()['total'];
$pagination = get_pagination($page, $total, $questions_par_page);

// Récupérer les favoris
$sql = "SELECT q.*, u.login, u.avatar, u.reputation,
        COUNT(r.r_id) as nb_reponses,
        (SELECT u2.login FROM forum_reponse r2 
         JOIN forum_utilisateur u2 ON r2.user_id = u2.user_id 
         WHERE r2.r_fk_question_id = q.q_id 
         ORDER BY r2.r_date_ajout DESC LIMIT 1) as dernier_repondeur
        FROM forum_favoris f
        JOIN forum_question q ON f.question_id = q.q_id
        LEFT JOIN forum_utilisateur u ON q.user_id = u.user_id 
        LEFT JOIN forum_reponse r ON q.q_id = r.r_fk_question_id 
        WHERE f.user_id = ?
        GROUP BY q.q_id 
        ORDER BY f.added_at DESC
        LIMIT " . (int)$pagination['elements_par_page'] . " OFFSET " . (int)$pagination['offset'];
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$questions = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mes favoris</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <?php afficher_header('Mes favoris'); ?>

    <main>
        <h2>⭐ Mes questions favorites</h2>
        
        <?php if (empty($questions)): ?>
            <div class="empty-state">
                <p>Vous n'avez aucune question en favoris.</p>
                <p><a href="index.php">Retour à l'accueil</a></p>
            </div>
        <?php else: ?>
            <p style="color: #666; margin-bottom: 20px;">Vous avez <strong><?= $total ?></strong> question(s) en favoris</p>
            
            <?php foreach($questions as $question): ?>
                <div class="question-card">
                    <div style="flex: 1;">
                        <h3 style="margin-top: 0;">
                            <a href="question.php?id=<?= $question['q_id'] ?>">
                                <?= htmlspecialchars($question['q_titre']) ?>
                            </a>
                            <?php if ($question['status'] == 'closed'): ?>
                                <span class="solution-badge">✅ Résolu</span>
                            <?php endif; ?>
                        </h3>
                        <div class="question-meta">
                            Par 
                            <?php if ($question['avatar']): ?>
                                <img src="<?= htmlspecialchars($question['avatar']) ?>" alt="Avatar" class="user-avatar">
                            <?php endif; ?>
                            <strong><?= htmlspecialchars($question['login']) ?></strong> 
                            • <?= formater_date_relative($question['q_date_ajout']) ?>
                            • 👁️ <?= $question['views_count'] ?> vues
                        </div>
                        <p style="color: #888; margin: 0;">
                            <?= $question['nb_reponses'] ?> réponses 
                            <?php if ($question['dernier_repondeur']): ?>
                                • Dernière réponse par <?= htmlspecialchars($question['dernier_repondeur']) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="question-stats">
                        <strong><?= $question['nb_reponses'] ?></strong>
                        <small>réponses</small>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <div class="pagination">
                    <?php if ($pagination['page_actuelle'] > 1): ?>
                        <a href="?page=<?= $pagination['page_actuelle'] - 1 ?>">‹ Précédent</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <?php if ($i == $pagination['page_actuelle']): ?>
                            <strong><?= $i ?></strong>
                        <?php else: ?>
                            <a href="?page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($pagination['page_actuelle'] < $pagination['total_pages']): ?>
                        <a href="?page=<?= $pagination['page_actuelle'] + 1 ?>">Suivant ›</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <footer>
        <p>Forum de discussion © 2025 - <?= date('Y') ?></p>
    </footer>
</body>
</html>
