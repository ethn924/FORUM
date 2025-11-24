<?php
require_once 'config.php';
require_once 'fonctions.php'; // Ajout de cette ligne

$erreur = '';
$succes = '';

if ($_POST) {
    $login = valider_entree($_POST['login'] ?? '');
    $mdp = $_POST['mdp'] ?? '';
    $confirmation = $_POST['confirmation'] ?? '';
    
    if ($login && $mdp && $confirmation) {
        if (strlen($mdp) < 6) {
            $erreur = "Le mot de passe doit contenir au moins 6 caractères";
        } elseif ($mdp !== $confirmation) {
            $erreur = "Les mots de passe ne correspondent pas";
        } elseif (strlen($login) < 3) {
            $erreur = "Le login doit contenir au moins 3 caractères";
        } else {
            // Vérifier si le login existe déjà
            $sql = "SELECT user_id FROM forum_utilisateur WHERE login = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$login]);
            
            if ($stmt->fetch()) {
                $erreur = "Ce login est déjà utilisé";
            } else {
                // Créer le nouvel utilisateur avec password hashé
                $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);
                $sql = "INSERT INTO forum_utilisateur (login, password) VALUES (?, ?)";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$login, $mdp_hash])) {
                    $succes = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                } else {
                    $erreur = "Erreur lors de l'inscription";
                }
            }
        }
    } else {
        $erreur = "Veuillez remplir tous les champs";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Inscription</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="inscription.css">
</head>
<body>
    <?php afficher_header('Inscription'); ?>

    <div class="auth-container">
        <h1>✍️ Inscription au forum</h1>
        
        <?php if ($erreur): ?>
            <?= afficher_erreur($erreur) ?>
        <?php endif; ?>
        
        <?php if ($succes): ?>
            <?= afficher_succes($succes) ?>
            <div class="auth-links">
                <a href="connexion.php">Se connecter</a>
            </div>
        <?php else: ?>
            <form method="post">
                <div class="form-group">
                    <label>Login :</label>
                    <input type="text" name="login" required autofocus>
                </div>
                <div class="form-group">
                    <label>Mot de passe :</label>
                    <input type="password" name="mdp" required>
                </div>
                <div class="form-group">
                    <label>Confirmation du mot de passe :</label>
                    <input type="password" name="confirmation" required>
                </div>
                <button type="submit" class="submit-button">S'inscrire</button>
            </form>
            
            <div class="auth-links">
                <p>Déjà un compte ? <a href="connexion.php">Se connecter</a></p>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" class="back-link">← Retour à l'accueil</a>
        </div>
    </div>

    <footer>
        <p>Forum de discussion © 2025 - <?= date('Y') ?></p>
    </footer>
</body>
</html>