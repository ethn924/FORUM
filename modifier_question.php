<?php
require_once 'config.php';
require_once 'fonctions.php'; // Ajout de cette ligne

if (!est_connecte()) {
    header('Location: connexion.php');
    exit;
}

$question_id = $_GET['id'] ?? 0;
$erreur = '';

// Récupérer la question
$sql = "SELECT * FROM forum_question WHERE q_id = ? AND user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$question_id, $_SESSION['user_id']]);
$question = $stmt->fetch();

if (!$question) {
    die("Question non trouvée ou vous n'avez pas les droits pour la modifier");
}

if ($_POST) {
    $titre = $_POST['titre'] ?? '';
    $contenu = $_POST['contenu'] ?? '';
    
    if ($titre && $contenu) {
        $sql = "UPDATE forum_question SET q_titre = ?, q_contenu = ? WHERE q_id = ? AND user_id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$titre, $contenu, $question_id, $_SESSION['user_id']])) {
            header("Location: question.php?id=$question_id");
            exit;
        } else {
            $erreur = "Erreur lors de la modification";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Modifier la question</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <?php afficher_header('Modifier la question'); ?>
    
    <main>
        <div class="card" style="max-width: 800px; margin: 40px auto;">
            <h2>✏️ Modifier la question</h2>
            
            <?php if ($erreur): ?>
                <?= afficher_erreur($erreur) ?>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label>Titre de la question :</label>
                    <input type="text" name="titre" value="<?= htmlspecialchars($question['q_titre']) ?>" required autofocus>
                </div>
                
                <div class="form-group">
                    <label>Contenu détaillé :</label>
                    <textarea name="contenu" required><?= htmlspecialchars($question['q_contenu']) ?></textarea>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" class="success">💾 Enregistrer les modifications</button>
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