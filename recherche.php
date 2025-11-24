<?php
require_once 'config.php';
require_once 'fonctions.php';

$terme = valider_entree($_GET['q'] ?? '');
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$resultats = [];
$pagination = [];

if ($terme) {
    try {
        $recherche = rechercher($pdo, $terme, $page, 10);
        $resultats = $recherche['resultats'];
        $pagination = $recherche['pagination'];
        $total = $recherche['total'];
        
        // Logger la recherche
        logger('recherche', "Recherche: '$terme' - $total résultats", $_SESSION['user_id'] ?? null);
    } catch (Exception $e) {
        $erreur = gerer_exception($e);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Recherche<?= $terme ? " - $terme" : '' ?></title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="recherche.css">
</head>
<body>
    <?php afficher_header('Recherche'); ?>

    <main>
        <h2>🔍 Recherche dans le forum</h2>
        
        <form method="get" action="recherche.php" class="search-form">
            <input type="text" name="q" value="<?= htmlspecialchars($terme) ?>" 
                   placeholder="Entrez vos mots-clés...">
            <button type="submit">🔍 Rechercher</button>
        </form>

        <?php if (isset($erreur)): ?>
            <?= afficher_erreur($erreur) ?>
        <?php endif; ?>

        <?php if ($terme): ?>
            <div class="search-stats">
                <?php if (!empty($resultats)): ?>
                    <strong><?= $total ?> résultat(s) trouvé(s) pour "<?= htmlspecialchars($terme) ?>"</strong>
                <?php else: ?>
                    <strong>Aucun résultat trouvé pour "<?= htmlspecialchars($terme) ?>"</strong>
                    <p>Suggestions :</p>
                    <ul>
                        <li>Vérifiez l'orthographe des termes</li>
                        <li>Utilisez des mots-clés plus généraux</li>
                        <li>Essayez d'autres termes de recherche</li>
                    </ul>
                <?php endif; ?>
            </div>

            <?php foreach($resultats as $question): ?>
                <div class="resultat">
                    <h3 style="margin-top: 0;">
                        <a href="question.php?id=<?= $question['q_id'] ?>">
                            <?= highlight_mots(htmlspecialchars($question['q_titre']), $terme) ?>
                        </a>
                    </h3>
                    
                    <div style="color: #666; margin-bottom: 10px;">
                        Par <strong><?= htmlspecialchars($question['login']) ?></strong> 
                        • <?= formater_date_relative($question['q_date_ajout']) ?>
                        • <?= $question['nb_reponses'] ?> réponses
                        • 👁️ <?= $question['views_count'] ?> vues
                    </div>
                    
                    <div style="line-height: 1.5;">
                        <?= highlight_mots(nl2br(htmlspecialchars(substr($question['q_contenu'], 0, 300) . (strlen($question['q_contenu']) > 300 ? '...' : ''))), $terme) ?>
                    </div>
                    
                    <?php if ($question['status'] == 'closed'): ?>
                        <div style="margin-top: 10px;">
                            <span style="background: #27ae60; color: white; padding: 3px 8px; border-radius: 12px; font-size: 12px;">
                                ✅ Résolu
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <?php if ($pagination && $pagination['total_pages'] > 1): ?>
                <div class="pagination">
                    <?php if ($pagination['page_actuelle'] > 1): ?>
                        <a href="?q=<?= urlencode($terme) ?>&page=<?= $pagination['page_actuelle'] - 1 ?>">‹ Précédent</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                        <?php if ($i == $pagination['page_actuelle']): ?>
                            <strong><?= $i ?></strong>
                        <?php else: ?>
                            <a href="?q=<?= urlencode($terme) ?>&page=<?= $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($pagination['page_actuelle'] < $pagination['total_pages']): ?>
                        <a href="?q=<?= urlencode($terme) ?>&page=<?= $pagination['page_actuelle'] + 1 ?>">Suivant ›</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php elseif ($_GET): ?>
            <div class="empty-search">
                <p>🔍 Entrez des termes de recherche pour trouver des questions et réponses.</p>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <p>Forum de discussion © 2025 - <?= date('Y') ?></p>
    </footer>

    <?php
    function highlight_mots($texte, $terme) {
        $mots = explode(' ', $terme);
        foreach ($mots as $mot) {
            if (strlen(trim($mot)) > 2) {
                $texte = preg_replace("/\b(" . preg_quote(trim($mot), '/') . ")\b/i", '<span class="highlight">$1</span>', $texte);
            }
        }
        return $texte;
    }
    ?>
</body>
</html>