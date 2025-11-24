<?php
require_once 'config.php';
require_once 'fonctions.php';

if (!est_connecte()) {
    header('Location: connexion.php');
    exit;
}

$comment_id = $_GET['id'] ?? 0;
$erreur = '';
$succes = '';

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
    die("Vous n'êtes pas autorisé à modifier ce commentaire");
}

$question_id = $commentaire['r_fk_question_id'];

if ($_POST) {
    $contenu = valider_entree($_POST['contenu'] ?? '');
    
    if (!$contenu) {
        $erreur = "Le commentaire ne peut pas être vide";
    } else {
        try {
            $sql = "UPDATE forum_commentaires SET contenu = ? WHERE comment_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$contenu, $comment_id]);
            
            logger('modification_commentaire', "Commentaire $comment_id modifié");
            header('Location: forum_question.php?id=' . $question_id);
            exit;
        } catch (Exception $e) {
            $erreur = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Modifier le commentaire</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php afficher_header('Modifier'); ?>
    
    <main>
        <div class="card" style="max-width: 600px; margin: 30px auto;">
            <h2>Modifier le commentaire</h2>
            
            <?php if ($erreur): ?>
                <?= afficher_erreur($erreur) ?>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label>Contenu :</label>
                    <textarea name="contenu" required><?= htmlspecialchars($commentaire['contenu']) ?></textarea>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="success">💾 Enregistrer</button>
                    <a href="question.php?id=<?= $question_id ?>" class="btn">Annuler</a>
                </div>
            </form>
        </div>
    </main>
    
    <footer>
        <p>Forum de discussion © 2025 - <?= date('Y') ?></p>
    </footer>
</body>
</html>
