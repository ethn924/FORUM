<?php
require_once 'config.php';
require_once 'fonctions.php'; // Ajout de cette ligne

if (!est_connecte()) {
    header('Location: connexion.php');
    exit;
}

$reponse_id = $_GET['id'] ?? 0;
$erreur = '';

// Récupérer la réponse
$sql = "SELECT r.*, q.q_id 
        FROM forum_reponse r 
        JOIN forum_question q ON r.r_fk_question_id = q.q_id 
        WHERE r.r_id = ? AND r.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$reponse_id, $_SESSION['user_id']]);
$reponse = $stmt->fetch();

if (!$reponse) {
    die("Réponse non trouvée ou vous n'avez pas les droits pour la modifier");
}

if ($_POST) {
    $contenu = $_POST['contenu'] ?? '';
    
    if ($contenu) {
        $sql = "UPDATE forum_reponse SET r_contenu = ? WHERE r_id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$contenu, $reponse_id, $_SESSION['user_id']])) {
            header("Location: question.php?id=" . $reponse['q_id']);
            exit;
        } else {
            $erreur = "Erreur lors de la modification";
        }
    } else {
        $erreur = "Veuillez remplir le contenu";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Modifier la réponse</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="question.css">
</head>
<body>
    <?php afficher_header('Modifier la réponse'); ?>
    
    <main>
        <div class="card" style="max-width: 800px; margin: 40px auto;">
            <h2>✏️ Modifier la réponse</h2>
            
            <?php if ($erreur): ?>
                <?= afficher_erreur($erreur) ?>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label>Contenu de la réponse :</label>
                    <textarea name="contenu" required><?= htmlspecialchars($reponse['r_contenu']) ?></textarea>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" class="success">💾 Enregistrer la modification</button>
                    <a href="question.php?id=<?= $reponse['q_id'] ?>" class="btn">Annuler</a>
                </div>
            </form>
        </div>
    </main>
    
    <footer>
        <p>Forum de discussion © 2025 - <?= date('Y') ?></p>
    </footer>
</body>
</html>