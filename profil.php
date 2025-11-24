<?php
require_once 'config.php';
require_once 'fonctions.php';

if (!est_connecte()) {
    header('Location: connexion.php');
    exit;
}

$erreur = '';
$succes = '';

// Récupérer les informations de l'utilisateur
$sql = "SELECT * FROM forum_utilisateur WHERE user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    die("Utilisateur non trouvé");
}

// Récupérer les statistiques de l'utilisateur
$sql = "SELECT 
        (SELECT COUNT(*) FROM forum_question WHERE user_id = ?) as nb_questions,
        (SELECT COUNT(*) FROM forum_reponse WHERE user_id = ?) as nb_reponses,
        (SELECT COUNT(*) FROM forum_commentaires WHERE user_id = ?) as nb_commentaires,
        (SELECT COUNT(*) FROM forum_likes l JOIN forum_reponse r ON l.r_id = r.r_id WHERE r.user_id = ?) as nb_likes_recus,
        (SELECT COUNT(*) FROM forum_question WHERE user_id = ? AND status = 'closed') as nb_questions_resolues";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$stats = $stmt->fetch();

// Récupérer les questions de l'utilisateur
$sql = "SELECT q_id, q_titre, q_date_ajout, status, views_count,
        (SELECT COUNT(*) FROM forum_reponse WHERE r_fk_question_id = q_id) as nb_reponses
        FROM forum_question 
        WHERE user_id = ? 
        ORDER BY q_date_ajout DESC 
        LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$mes_questions = $stmt->fetchAll();

// Récupérer les dernières activités
$sql = "(SELECT 'question' as type, q_id as id, q_titre as titre, q_date_ajout as date 
         FROM forum_question WHERE user_id = ?)
        UNION
        (SELECT 'reponse' as type, r_id as id, LEFT(r_contenu, 100) as titre, r_date_ajout as date 
         FROM forum_reponse WHERE user_id = ?)
        UNION
        (SELECT 'commentaire' as type, comment_id as id, LEFT(contenu, 100) as titre, created_at as date 
         FROM forum_commentaires WHERE user_id = ?)
        ORDER BY date DESC LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
$activites = $stmt->fetchAll();

if ($_POST) {
    $nouveau_login = valider_entree($_POST['login'] ?? '');
    $email = valider_entree($_POST['email'] ?? '', 'email');
    $signature = valider_entree($_POST['signature'] ?? '');
    $nouveau_mdp = $_POST['mdp'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';
    
    try {
        if ($nouveau_login) {
            // Validation du login
            if (strlen($nouveau_login) < 3) {
                throw new Exception("Le login doit contenir au moins 3 caractères");
            }
            
            if (strlen($nouveau_login) > 50) {
                throw new Exception("Le login ne peut pas dépasser 50 caractères");
            }
            
            // Vérifier si le nouveau login est disponible
            $sql = "SELECT user_id FROM forum_utilisateur WHERE login = ? AND user_id != ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nouveau_login, $_SESSION['user_id']]);
            
            if ($stmt->fetch()) {
                throw new Exception("Ce login est déjà utilisé");
            }
        }
        
        if ($email) {
            // Vérifier si l'email est disponible
            $sql = "SELECT user_id FROM forum_utilisateur WHERE email = ? AND user_id != ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email, $_SESSION['user_id']]);
            
            if ($stmt->fetch()) {
                throw new Exception("Cet email est déjà utilisé");
            }
        }
        
        // Mettre à jour les informations de base
        $sql = "UPDATE forum_utilisateur SET login = ?, email = ?, signature = ? WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nouveau_login ?: $user['login'], $email ?: $user['email'], $signature, $_SESSION['user_id']]);
        
        // Mettre à jour la session si le login a changé
        if ($nouveau_login) {
            $_SESSION['login'] = $nouveau_login;
        }
        
        // Gestion du mot de passe
        if ($nouveau_mdp) {
            if ($nouveau_mdp !== $confirmation) {
                throw new Exception("Les mots de passe ne correspondent pas");
            }
            
            if (strlen($nouveau_mdp) < 6) {
                throw new Exception("Le mot de passe doit contenir au moins 6 caractères");
            }
            
            // Hachage du mot de passe
            $mdp_hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
            
            // Mettre à jour le mot de passe
            $sql = "UPDATE forum_utilisateur SET password = ? WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$mdp_hash, $_SESSION['user_id']]);
            
            logger('changement_mdp', "Mot de passe modifié");
        }
        
        // Gestion de l'avatar (upload simple)
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $dossier_upload = 'uploads/avatars/';
            if (!is_dir($dossier_upload)) {
                mkdir($dossier_upload, 0755, true);
            }
            
            $extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $extensions_autorisees = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array(strtolower($extension), $extensions_autorisees)) {
                throw new Exception("Format de fichier non autorisé. Utilisez JPG, PNG ou GIF.");
            }
            
            $nom_fichier = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $extension;
            $chemin_fichier = $dossier_upload . $nom_fichier;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $chemin_fichier)) {
                // Mettre à jour l'avatar dans la base
                $sql = "UPDATE forum_utilisateur SET avatar = ? WHERE user_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$chemin_fichier, $_SESSION['user_id']]);
                
                logger('changement_avatar', "Avatar mis à jour");
            } else {
                throw new Exception("Erreur lors de l'upload de l'avatar");
            }
        }
        
        logger('modification_profil', "Profil mis à jour");
        $succes = "Profil mis à jour avec succès";
        
        // Recharger les informations utilisateur
        $sql = "SELECT * FROM forum_utilisateur WHERE user_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        
    } catch (Exception $e) {
        $erreur = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mon profil</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="profil.css">
</head>
<body>
    <?php afficher_header('Mon profil'); ?>

    <main>
        <div class="profil-container">
            <!-- Sidebar avec informations -->
            <div class="sidebar">
                <?php if ($user['avatar']): ?>
                    <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="avatar">
                <?php else: ?>
                    <div class="avatar-placeholder">
                        <?= strtoupper(substr($user['login'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                
                <h2><?= htmlspecialchars($user['login']) ?></h2>
                <div>
                    🏆 <?= $user['reputation'] ?> points de réputation
                    <?php if ($user['is_admin']): ?>
                        <br><span style="color: #e74c3c;">👑 Administrateur</span>
                    <?php endif; ?>
                </div>
                
                <?php if ($user['signature']): ?>
                    <div class="signature-preview">
                        "<?= htmlspecialchars($user['signature']) ?>"
                    </div>
                <?php endif; ?>
                
                <div class="stats-grid">
                    <div class="stat">
                        <div class="nombre"><?= $stats['nb_questions'] ?></div>
                        <div class="label">Questions</div>
                    </div>
                    <div class="stat">
                        <div class="nombre"><?= $stats['nb_reponses'] ?></div>
                        <div class="label">Réponses</div>
                    </div>
                    <div class="stat">
                        <div class="nombre"><?= $stats['nb_commentaires'] ?></div>
                        <div class="label">Commentaires</div>
                    </div>
                    <div class="stat">
                        <div class="nombre"><?= $stats['nb_likes_recus'] ?></div>
                        <div class="label">Likes reçus</div>
                    </div>
                </div>
                
                <div>
                    <h4>Dernières activités</h4>
                    <?php foreach($activites as $activite): ?>
                        <div class="activite-item">
                            <strong>
                                <?= $activite['type'] == 'question' ? '❓ Question' : ($activite['type'] == 'reponse' ? '💬 Réponse' : '💭 Commentaire') ?>
                            </strong><br>
                            <small><?= htmlspecialchars($activite['titre']) ?>...</small><br>
                            <small><?= formater_date_relative($activite['date']) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Contenu principal -->
            <div class="main-content">
                <!-- Formulaire de modification -->
                <div class="edit-profile-section card">
                    <h2>Modifier mon profil</h2>
                    
                    <?php if ($erreur): ?>
                        <?= afficher_erreur($erreur) ?>
                    <?php endif; ?>
                    
                    <?php if ($succes): ?>
                        <?= afficher_succes($succes) ?>
                    <?php endif; ?>
                    
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-section">
                            <label>Avatar :</label>
                            <input type="file" name="avatar" accept="image/*">
                            <small>Formats acceptés: JPG, PNG, GIF (max 2MB)</small>
                        </div>
                        
                        <div class="form-section">
                            <label>Login :</label>
                            <input type="text" name="login" value="<?= htmlspecialchars($user['login']) ?>" required>
                        </div>
                        
                        <div class="form-section">
                            <label>Email :</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                        </div>
                        
                        <div class="form-section">
                            <label>Signature :</label>
                            <textarea name="signature" placeholder="Votre signature apparaîtra sous vos messages..."><?= htmlspecialchars($user['signature'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="form-section">
                            <label>Nouveau mot de passe (laisser vide pour ne pas changer) :</label>
                            <input type="password" name="mdp" placeholder="••••••">
                        </div>
                        
                        <div class="form-section">
                            <label>Confirmation du nouveau mot de passe :</label>
                            <input type="password" name="confirmation" placeholder="••••••">
                        </div>
                        
                        <button type="submit" class="success">💾 Enregistrer les modifications</button>
                    </form>
                </div>

                <!-- Mes questions -->
                <div class="mes-questions-section card">
                    <h2>❓ Mes questions</h2>
                    
                    <?php if (empty($mes_questions)): ?>
                        <div class="empty-state">
                            <p>Vous n'avez posé aucune question pour le moment.</p>
                            <p><a href="ajouter_question.php" class="btn success">Poser ma première question</a></p>
                        </div>
                    <?php else: ?>
                        <div class="questions-list">
                            <?php foreach($mes_questions as $question): ?>
                                <div class="question-item">
                                    <h3 style="margin: 0 0 8px 0;">
                                        <a href="question.php?id=<?= $question['q_id'] ?>" class="question-link">
                                            <?= htmlspecialchars($question['q_titre']) ?>
                                        </a>
                                        <?php if ($question['status'] == 'closed'): ?>
                                            <span class="solution-badge">✅ Résolu</span>
                                        <?php endif; ?>
                                    </h3>
                                    
                                    <div class="question-meta">
                                        <span class="timestamp">
                                            <?= formater_date_relative($question['q_date_ajout']) ?>
                                        </span>
                                        • 
                                        <span class="views">
                                            👁️ <?= $question['views_count'] ?> vues
                                        </span>
                                        • 
                                        <span class="reponses">
                                            💬 <?= $question['nb_reponses'] ?> réponses
                                        </span>
                                        • 
                                        <span class="status <?= $question['status'] ?>">
                                            <?= $question['status'] == 'open' ? '🟢 Ouverte' : '🔴 Fermée' ?>
                                        </span>
                                    </div>
                                    
                                    <div class="question-actions">
                                        <a href="question.php?id=<?= $question['q_id'] ?>" class="btn small">
                                            👀 Voir la question
                                        </a>
                                        <?php if ($question['status'] == 'open'): ?>
                                            <a href="modifier_question.php?id=<?= $question['q_id'] ?>" class="btn small">
                                                ✏️ Modifier
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($stats['nb_questions'] > 10): ?>
                            <div style="text-align: center; margin-top: 20px;">
                                <a href="mes_questions.php" class="btn">Voir toutes mes questions</a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Statistiques détaillées -->
                <div class="detailed-stats card">
                    <h3>📊 Statistiques détaillées</h3>
                    <div class="detailed-stats-grid">
                        <div class="detailed-stat-item">
                            <div class="value"><?= $stats['nb_questions_resolues'] ?></div>
                            <div class="label">Questions résolues</div>
                        </div>
                        <div class="detailed-stat-item">
                            <div class="value"><?= $user['reputation'] ?></div>
                            <div class="label">Réputation totale</div>
                        </div>
                        <div class="detailed-stat-item">
                            <div class="value">
                                <?= $stats['nb_questions'] + $stats['nb_reponses'] + $stats['nb_commentaires'] ?>
                            </div>
                            <div class="label">Contributions totales</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <p>Forum de discussion © 2025 - <?= date('Y') ?></p>
    </footer>
</body>
</html>