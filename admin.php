<?php
require_once 'config.php';
require_once 'fonctions.php'; // Ajout de cette ligne

if (!est_connecte() || !est_admin()) {
    header('Location: index.php');
    exit;
}

$message = '';

// Actions d'administration
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = $_GET['id'] ?? 0;
    
    switch ($action) {
        case 'supprimer_utilisateur':
            if ($id != $_SESSION['user_id']) { // Empêche de se supprimer soi-même
                $sql = "DELETE FROM utilisateur WHERE user_id = ?";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$id])) {
                    $message = "Utilisateur supprimé avec succès";
                }
            }
            break;
            
        case 'supprimer_question':
            $sql = "DELETE FROM question WHERE q_id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$id])) {
                $message = "Question supprimée avec succès";
            }
            break;
            
        case 'supprimer_reponse':
            $sql = "DELETE FROM reponse WHERE r_id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$id])) {
                $message = "Réponse supprimée avec succès";
            }
            break;
    }
}

// Récupérer les données
$sql_users = "SELECT * FROM utilisateur ORDER BY created_at DESC";
$users = $pdo->query($sql_users)->fetchAll();

$sql_questions = "SELECT q.*, u.login FROM question q JOIN utilisateur u ON q.user_id = u.user_id ORDER BY q.q_date_ajout DESC";
$questions = $pdo->query($sql_questions)->fetchAll();

$sql_reponses = "SELECT r.*, u.login, q.q_titre FROM reponse r JOIN utilisateur u ON r.user_id = u.user_id JOIN question q ON r.r_fk_question_id = q.q_id ORDER BY r.r_date_ajout DESC";
$reponses = $pdo->query($sql_reponses)->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Administration</title>
    <meta charset="utf-8">
    <style>
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .section { margin: 30px 0; }
        .danger { color: red; }
    </style>
</head>
<body>
    <h1>Administration du forum</h1>
    
    <?php if ($message): ?>
        <?= afficher_succes($message) ?>
    <?php endif; ?>
    
    <p><a href="index.php">Retour au forum</a></p>
    
    <div class="section">
        <h2>Gestion des utilisateurs</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Login</th>
                    <th>Admin</th>
                    <th>Date d'inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                    <tr>
                        <td><?= $user['user_id'] ?></td>
                        <td><?= htmlspecialchars($user['login']) ?></td>
                        <td><?= $user['is_admin'] ? 'Oui' : 'Non' ?></td>
                        <td><?= formater_date_fr($user['created_at']) ?></td>
                        <td>
                            <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                <a href="admin.php?action=supprimer_utilisateur&id=<?= $user['user_id'] ?>" 
                                   class="danger" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
                                    Supprimer
                                </a>
                            <?php else: ?>
                                <em>(Vous)</em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="section">
        <h2>Gestion des questions</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($questions as $question): ?>
                    <tr>
                        <td><?= $question['q_id'] ?></td>
                        <td><?= htmlspecialchars($question['q_titre']) ?></td>
                        <td><?= htmlspecialchars($question['login']) ?></td>
                        <td><?= formater_date_fr($question['q_date_ajout']) ?></td>
                        <td><?= $question['status'] == 'open' ? 'Ouverte' : 'Fermée' ?></td>
                        <td>
                            <a href="question.php?id=<?= $question['q_id'] ?>">Voir</a>
                            <a href="admin.php?action=supprimer_question&id=<?= $question['q_id'] ?>" 
                               class="danger" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette question ?')">
                                Supprimer
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="section">
        <h2>Gestion des réponses</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Contenu</th>
                    <th>Auteur</th>
                    <th>Question</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($reponses as $reponse): ?>
                    <tr>
                        <td><?= $reponse['r_id'] ?></td>
                        <td><?= htmlspecialchars(substr($reponse['r_contenu'], 0, 50)) ?>...</td>
                        <td><?= htmlspecialchars($reponse['login']) ?></td>
                        <td><?= htmlspecialchars($reponse['q_titre']) ?></td>
                        <td><?= formater_date_fr($reponse['r_date_ajout']) ?></td>
                        <td>
                            <a href="question.php?id=<?= $reponse['r_fk_question_id'] ?>">Voir</a>
                            <a href="admin.php?action=supprimer_reponse&id=<?= $reponse['r_id'] ?>" 
                               class="danger" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réponse ?')">
                                Supprimer
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>