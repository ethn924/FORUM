<?php
// Fonctions utilitaires

function afficher_erreur($message) {
    return "<div class='erreur'>⚠️ $message</div>";
}

function afficher_succes($message) {
    return "<div class='succes'>✅ $message</div>";
}

function afficher_info($message) {
    return "<div class='info'>ℹ️ $message</div>";
}

// Statistiques
function get_question_plus_reponses($pdo) {
    $sql = "SELECT q.q_id, q.q_titre, COUNT(r.r_id) as nb_reponses 
            FROM forum_question q 
            LEFT JOIN forum_reponse r ON q.q_id = r.r_fk_question_id 
            GROUP BY q.q_id 
            ORDER BY nb_reponses DESC 
            LIMIT 1";
    return $pdo->query($sql)->fetch();
}

function get_questions_populaires($pdo) {
    $sql = "SELECT q.q_titre, COUNT(r.r_id) as nb_reponses 
            FROM forum_question q 
            LEFT JOIN forum_reponse r ON q.q_id = r.r_fk_question_id 
            GROUP BY q.q_id 
            ORDER BY nb_reponses DESC 
            LIMIT 5";
    return $pdo->query($sql)->fetchAll();
}

function get_nombre_total_questions($pdo) {
    $sql = "SELECT COUNT(*) as total FROM forum_question";
    return $pdo->query($sql)->fetch()['total'];
}

function get_nombre_total_reponses($pdo) {
    $sql = "SELECT COUNT(*) as total FROM forum_reponse";
    return $pdo->query($sql)->fetch()['total'];
}

// Formatage des dates en français
function formater_date_fr($date_sql) {
    if (!$date_sql) return 'Non spécifiée';

    $timestamp = strtotime($date_sql);
    $jours = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    $mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

    $jour_semaine = $jours[date('w', $timestamp)];
    $jour = date('j', $timestamp);
    $mois_nom = $mois[date('n', $timestamp) - 1];
    $annee = date('Y', $timestamp);
    $heure = date('H:i', $timestamp);

    return "$jour $mois_nom $annee à $heure";
}

function formater_date_relative($date_sql) {
    $maintenant = new DateTime();
    $date = new DateTime($date_sql);
    $interval = $maintenant->diff($date);
    
    if ($interval->y > 0) return "il y a {$interval->y} an" . ($interval->y > 1 ? 's' : '');
    if ($interval->m > 0) return "il y a {$interval->m} mois";
    if ($interval->d > 0) return "il y a {$interval->d} jour" . ($interval->d > 1 ? 's' : '');
    if ($interval->h > 0) return "il y a {$interval->h} heure" . ($interval->h > 1 ? 's' : '');
    if ($interval->i > 0) return "il y a {$interval->i} minute" . ($interval->i > 1 ? 's' : '');
    return "à l'instant";
}

// Compter les likes d'une réponse
function get_likes_count($pdo, $r_id) {
    $sql = "SELECT COUNT(*) as count FROM forum_likes WHERE r_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$r_id]);
    return $stmt->fetch()['count'];
}

// Vérifier si l'utilisateur a liké une réponse
function user_has_liked($pdo, $user_id, $r_id) {
    if (!$user_id) return false;
    
    $sql = "SELECT like_id FROM forum_likes WHERE user_id = ? AND r_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $r_id]);
    return $stmt->fetch() ? true : false;
}

// Récupérer les commentaires d'une réponse
function get_commentaires($pdo, $r_id) {
    $sql = "SELECT c.*, u.login, u.avatar, u.reputation 
            FROM forum_commentaires c 
            JOIN forum_utilisateur u ON c.user_id = u.user_id 
            WHERE c.r_id = ? 
            ORDER BY c.created_at ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$r_id]);
    return $stmt->fetchAll();
}

// Recherche dans le forum
function rechercher($pdo, $terme, $page = 1, $par_page = 10) {
    $terme = "%$terme%";
    
    // Compter le total
    $sql_count = "SELECT COUNT(DISTINCT q.q_id) as total 
                  FROM forum_question q 
                  LEFT JOIN forum_reponse r ON q.q_id = r.r_fk_question_id 
                  WHERE q.q_titre LIKE ? OR q.q_contenu LIKE ? OR r.r_contenu LIKE ?";
    $stmt = $pdo->prepare($sql_count);
    $stmt->execute([$terme, $terme, $terme]);
    $total = $stmt->fetch()['total'];
    
    $pagination = get_pagination($page, $total, $par_page);
    
    // Récupérer les résultats
    $sql = "SELECT q.*, u.login, 
            (SELECT COUNT(*) FROM forum_reponse r2 WHERE r2.r_fk_question_id = q.q_id) as nb_reponses
            FROM forum_question q 
            LEFT JOIN forum_utilisateur u ON q.user_id = u.user_id 
            WHERE q.q_titre LIKE ? OR q.q_contenu LIKE ? OR q.q_id IN (
                SELECT DISTINCT r.r_fk_question_id FROM forum_reponse r WHERE r.r_contenu LIKE ?
            )
            GROUP BY q.q_id 
            ORDER BY q.last_activity DESC
            LIMIT " . (int)$par_page . " OFFSET " . (int)$pagination['offset'];
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$terme, $terme, $terme]);
    $resultats = $stmt->fetchAll();
    
    return [
        'resultats' => $resultats,
        'pagination' => $pagination,
        'total' => $total
    ];
}

// Générer un slug pour les URLs
function generer_slug($texte) {
    $texte = preg_replace('~[^\pL\d]+~u', '-', $texte);
    $texte = iconv('utf-8', 'us-ascii//TRANSLIT', $texte);
    $texte = preg_replace('~[^-\w]+~', '', $texte);
    $texte = trim($texte, '-');
    $texte = preg_replace('~-+~', '-', $texte);
    $texte = strtolower($texte);
    
    return $texte ?: 'question';
}

// Marquer une réponse comme solution
function marquer_comme_solution($pdo, $r_id, $user_id) {
    try {
        $pdo->beginTransaction();
        
        // Récupérer la question associée
        $sql = "SELECT r_fk_question_id, user_id FROM forum_reponse WHERE r_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$r_id]);
        $reponse = $stmt->fetch();
        
        if (!$reponse) {
            throw new Exception("Réponse non trouvée");
        }
        
        // Vérifier que l'utilisateur est l'auteur de la question
        $sql = "SELECT user_id FROM forum_question WHERE q_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$reponse['r_fk_question_id']]);
        $question = $stmt->fetch();
        
        if ($question['user_id'] != $user_id && !est_admin()) {
            throw new Exception("Vous n'êtes pas autorisé à marquer cette réponse comme solution");
        }
        
        // Réinitialiser toutes les solutions pour cette question
        $sql = "UPDATE forum_reponse SET is_solution = 0 WHERE r_fk_question_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$reponse['r_fk_question_id']]);
        
        // Marquer la réponse comme solution
        $sql = "UPDATE forum_reponse SET is_solution = 1 WHERE r_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$r_id]);
        
        // Donner de la réputation à l'auteur de la réponse
        $sql = "UPDATE forum_utilisateur SET reputation = reputation + 15 WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$reponse['user_id']]);
        
        $pdo->commit();
        
        // Notifier l'auteur de la réponse
        creer_notification(
            $reponse['user_id'],
            'Votre réponse a été marquée comme solution',
            'Félicitations ! Votre réponse a été sélectionnée comme solution et vous gagnez 15 points de réputation.',
            "question.php?id={$reponse['r_fk_question_id']}"
        );
        
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

// Vérifier si une question est en favoris
function est_en_favoris($pdo, $user_id, $question_id) {
    if (!$user_id) return false;
    
    $sql = "SELECT id FROM forum_favoris WHERE user_id = ? AND question_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $question_id]);
    return $stmt->fetch() ? true : false;
}

// Générer le header de navigation
function afficher_header($titre = 'Forum de discussion', $pdo = null) {
    global $pdo;
    $notifications = [];
    if (est_connecte()) {
        $notifications = get_notifications($_SESSION['user_id'], $pdo, true);
    }
    ?>
    <header>
        <div class="header-container">
            <div class="header-brand">
                <h1>
                    <a href="index.php">💬 <?= htmlspecialchars($titre) ?></a>
                </h1>
            </div>
            
            <nav class="header-nav">
                <a href="index.php" class="nav-link">🏠 Accueil</a>
                <a href="recherche.php" class="nav-link">🔍 Rechercher</a>
                
                <?php if (est_connecte()): ?>
                    <a href="ajouter_question.php" class="nav-link">❓ Poser une question</a>
                    <a href="mes_favoris.php" class="nav-link">⭐ Favoris</a>
                    
                    <div class="nav-user">
                        <a href="profil.php" class="nav-link user-link">
                            👤 <span><?= htmlspecialchars($_SESSION['login']) ?></span>
                        </a>
                        <?php if (est_admin()): ?>
                            <a href="admin.php" class="nav-link admin-link" title="Admin">⚙️</a>
                        <?php endif; ?>
                        <a href="deconnexion.php" class="nav-link logout-link">🚪</a>
                    </div>
                <?php else: ?>
                    <div class="nav-auth">
                        <a href="connexion.php" class="nav-link">🔐 Connexion</a>
                        <a href="inscription.php" class="nav-link btn-signup">✍️ S'inscrire</a>
                    </div>
                <?php endif; ?>
            </nav>
            
            <?php if (!empty($notifications)): ?>
                <div class="header-notifications">
                    <button class="notif-btn" title="Notifications">
                        🔔 <span class="notif-count"><?= count($notifications) ?></span>
                    </button>
                    <div class="notif-dropdown">
                        <?php foreach($notifications as $notif): ?>
                            <a href="<?= htmlspecialchars($notif['lien'] ?? '#') ?>" class="notif-item">
                                <strong><?= htmlspecialchars($notif['titre']) ?></strong>
                                <p><?= htmlspecialchars($notif['contenu']) ?></p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <?php
}
?>