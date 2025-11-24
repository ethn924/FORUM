<?php
require_once 'config.php';
require_once 'fonctions.php';

// Mettre à jour la dernière connexion
if (est_connecte()) {
    $sql = "UPDATE forum_utilisateur SET derniere_connexion = NOW() WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    
    // Logger la connexion
    logger('connexion', "Utilisateur {$_SESSION['login']} connecté");
}

// Tri
$tri = $_GET['tri'] ?? 'recent';
$filtre = $_GET['filtre'] ?? 'tous';

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$questions_par_page = 10;

// Déterminer l'ordre
$order_by = "q.last_activity DESC";
if ($tri === 'reponses') {
    $order_by = "nb_reponses DESC";
} elseif ($tri === 'ancien') {
    $order_by = "q.q_date_ajout ASC";
}

// Déterminer le filtre
$where = "1=1";
if ($filtre === 'resolu') {
    $where = "q.status = 'closed'";
} elseif ($filtre === 'non_resolu') {
    $where = "q.status = 'open'";
}

// Compter le total de questions
$sql_count = "SELECT COUNT(*) as total FROM forum_question q WHERE $where";
$total_questions = $pdo->query($sql_count)->fetch()['total'];
$pagination = get_pagination($page, $total_questions, $questions_par_page);

// Récupérer les questions avec pagination
$sql = "SELECT q.*, u.login, u.avatar, u.reputation,
        COUNT(r.r_id) as nb_reponses,
        MAX(r.r_date_ajout) as derniere_reponse_date,
        (SELECT u2.login FROM forum_reponse r2 
         JOIN forum_utilisateur u2 ON r2.user_id = u2.user_id 
         WHERE r2.r_fk_question_id = q.q_id 
         ORDER BY r2.r_date_ajout DESC LIMIT 1) as dernier_repondeur
        FROM forum_question q 
        LEFT JOIN forum_utilisateur u ON q.user_id = u.user_id 
        LEFT JOIN forum_reponse r ON q.q_id = r.r_fk_question_id 
        WHERE $where
        GROUP BY q.q_id 
        ORDER BY $order_by
        LIMIT " . (int)$pagination['elements_par_page'] . " OFFSET " . (int)$pagination['offset'];
$stmt = $pdo->prepare($sql);
$stmt->execute();
$questions = $stmt->fetchAll();

// Récupérer les statistiques
$question_plus_reponses = get_question_plus_reponses($pdo);
$questions_populaires = get_questions_populaires($pdo);
$total_questions = get_nombre_total_questions($pdo);
$total_reponses = get_nombre_total_reponses($pdo);

// Récupérer les notifications
$notifications = [];
if (est_connecte()) {
    $notifications = get_notifications($_SESSION['user_id'], $pdo, true);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forum de discussion</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <?php afficher_header(); ?>

    <main>
        <?php if (isset($_SESSION['connexion_succes'])): ?>
            <?= afficher_succes($_SESSION['connexion_succes']) ?>
            <?php unset($_SESSION['connexion_succes']); ?>
        <?php endif; ?>
        <!-- Barre de recherche rapide -->
        <section class="search-section">
            <form action="recherche.php" method="get" class="search-form">
                <input type="text" name="q" placeholder="Rechercher dans le forum...">
                <button type="submit">🔍 Rechercher</button>
            </form>
        </section>

        <!-- Tri et filtrage -->
        <section style="display: flex; gap: 15px; margin-bottom: 20px; flex-wrap: wrap;">
            <div>
                <label style="font-weight: bold; margin-right: 10px;">Trier par :</label>
                <a href="?tri=recent&filtre=<?= htmlspecialchars($filtre) ?>" class="btn <?= $tri === 'recent' ? 'active' : '' ?>" style="padding: 8px 12px; font-size: 14px;">Plus récent</a>
                <a href="?tri=reponses&filtre=<?= htmlspecialchars($filtre) ?>" class="btn <?= $tri === 'reponses' ? 'active' : '' ?>" style="padding: 8px 12px; font-size: 14px;">Plus de réponses</a>
                <a href="?tri=ancien&filtre=<?= htmlspecialchars($filtre) ?>" class="btn <?= $tri === 'ancien' ? 'active' : '' ?>" style="padding: 8px 12px; font-size: 14px;">Plus ancien</a>
            </div>
            <div>
                <label style="font-weight: bold; margin-right: 10px;">Filtre :</label>
                <a href="?tri=<?= htmlspecialchars($tri) ?>&filtre=tous" class="btn <?= $filtre === 'tous' ? 'active' : '' ?>" style="padding: 8px 12px; font-size: 14px;">Toutes</a>
                <a href="?tri=<?= htmlspecialchars($tri) ?>&filtre=resolu" class="btn <?= $filtre === 'resolu' ? 'active' : '' ?>" style="padding: 8px 12px; font-size: 14px;">✅ Résolues</a>
                <a href="?tri=<?= htmlspecialchars($tri) ?>&filtre=non_resolu" class="btn <?= $filtre === 'non_resolu' ? 'active' : '' ?>" style="padding: 8px 12px; font-size: 14px;">❓ Non résolues</a>
            </div>
        </section>

        <section class="stats-grid">
            <div class="stat-card">
                <h3><?= $total_questions ?></h3>
                <p>Questions</p>
            </div>
            <div class="stat-card">
                <h3><?= $total_reponses ?></h3>
                <p>Réponses</p>
            </div>
            <div class="stat-card">
                <h3><?= count($questions_populaires) ?></h3>
                <p>Questions populaires</p>
            </div>
        </section>

        <?php if ($question_plus_reponses): ?>
            <section class="most-active">
                <h3>📊 Question la plus active</h3>
                <p>
                    "<a href="question.php?id=<?= $question_plus_reponses['q_id'] ?>">
                        <?= htmlspecialchars($question_plus_reponses['q_titre']) ?>
                    </a>" 
                    (<?= $question_plus_reponses['nb_reponses'] ?> réponses)
                </p>
            </section>
        <?php endif; ?>

        <section class="questions-section">
            <h2>Questions récentes</h2>
            
            <?php if (empty($questions)): ?>
                <div class="empty-state">
                    <p>Aucune question pour le moment. <a href="ajouter_question.php">Soyez le premier à poser une question !</a></p>
                </div>
            <?php else: ?>
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
        </section>
    </main>

    <footer>
        <p>Forum de discussion © 2025 - <?= date('Y') ?></p>
    </footer>
</body>
</html>