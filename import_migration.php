<?php
require_once 'config.php';

$sql_file = __DIR__ . '/migration.sql';

if (!file_exists($sql_file)) {
    die('❌ Erreur : Le fichier migration.sql n\'existe pas');
}

$sql = file_get_contents($sql_file);

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Exécuter le script SQL
    $pdo->exec($sql);
    
    echo "✅ Migration réussie !<br><br>";
    echo "Tables créées :<br>";
    echo "- forum_utilisateur<br>";
    echo "- forum_question<br>";
    echo "- forum_reponse<br>";
    echo "- forum_likes<br>";
    echo "- forum_commentaires<br>";
    echo "- forum_favoris<br>";
    echo "- forum_logs<br>";
    echo "- forum_notifications<br><br>";
    
    echo "✅ Données importées avec succès !<br>";
    echo "<a href='index.php'>Retour à l'accueil</a>";
    
} catch(PDOException $e) {
    echo "❌ Erreur lors de la migration : " . htmlspecialchars($e->getMessage());
}
?>
