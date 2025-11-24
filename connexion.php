<?php
require_once 'config.php';
require_once 'fonctions.php'; // Ajout de cette ligne

$erreur = '';
$succes = '';

if ($_POST) {
    $login = $_POST['login'] ?? '';
    $mdp = $_POST['mdp'] ?? '';
    
    if ($login && $mdp) {
        // Récupérer l'utilisateur par login
        $sql = "SELECT * FROM forum_utilisateur WHERE login = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$login]);
        $user = $stmt->fetch();
        
        // Vérifier le mot de passe
        if ($user && password_verify($mdp, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['login'] = $user['login'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            // Mettre à jour la dernière connexion
            $sql = "UPDATE forum_utilisateur SET derniere_connexion = NOW() WHERE user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user['user_id']]);
            
            $_SESSION['connexion_succes'] = "Bienvenue $login ! Vous êtes connecté.";
            
            header('Location: index.php');
            exit;
        } else {
            $erreur = "Identifiants incorrects";
        }
    } else {
        $erreur = "Veuillez remplir tous les champs";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Connexion</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="connexion.css">
</head>
<body>
    <?php afficher_header('Connexion'); ?>

    <div class="auth-container">
        <h1>🔐 Connexion au forum</h1>
        
        <?php if ($erreur): ?>
            <?= afficher_erreur($erreur) ?>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label>Login :</label>
                <input type="text" name="login" required autofocus>
            </div>
            <div class="form-group">
                <label>Mot de passe :</label>
                <input type="password" name="mdp" required>
            </div>
            <button type="submit" class="submit-button">Se connecter</button>
        </form>
        
        <div class="auth-links">
            <p>Pas encore de compte ? <a href="inscription.php">S'inscrire</a></p>
            <a href="index.php" class="back-link">← Retour à l'accueil</a>
        </div>
    </div>

    <footer>
        <p>Forum de discussion © 2025 - <?= date('Y') ?></p>
    </footer>
</body>
</html>