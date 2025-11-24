<?php
require_once 'config.php';
require_once 'fonctions.php'; // Ajout de cette ligne

if (!est_connecte()) {
    $_SESSION['erreur'] = "Vous devez être connecté pour poser une question";
    header('Location: connexion.php');
    exit;
}

$erreur = '';

if ($_POST) {
    $titre = $_POST['titre'] ?? '';
    $contenu = $_POST['contenu'] ?? '';
    
    if ($titre && $contenu) {
        $sql = "INSERT INTO forum_question (q_titre, q_contenu, q_date_ajout, user_id) VALUES (?, ?, NOW(), ?)";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$titre, $contenu, $_SESSION['user_id']])) {
            header('Location: index.php');
            exit;
        } else {
            $erreur = "Erreur lors de l'ajout de la question";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Poser une question</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="index.css">
</head>
<body>
    <?php afficher_header('Poser une question'); ?>
    
    <main>
        <div class="card" style="max-width: 800px; margin: 40px auto;">
            <h2>❓ Poser une nouvelle question</h2>
            
            <?php if ($erreur): ?>
                <?= afficher_erreur($erreur) ?>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label>Titre de la question :</label>
                    <input type="text" name="titre" placeholder="Décrivez votre question en quelques mots..." required autofocus>
                </div>
                
                <div class="form-group">
                    <label>Contenu détaillé :</label>
                    <textarea name="contenu" placeholder="Fournissez plus de détails sur votre question..." required></textarea>
                </div>
                
                <div style="display: flex; gap: 12px; margin-top: 24px;">
                    <button type="submit" class="success">✅ Poser la question</button>
                    <a href="index.php" class="btn">Annuler</a>
                </div>
            </form>
        </div>
    </main>
    
    <footer>
        <p>Forum de discussion © 2025 - <?= date('Y') ?></p>
    </footer>
</body>
</html>