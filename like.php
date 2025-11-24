<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!est_connecte()) {
    echo json_encode(['error' => 'Non connecté']);
    exit;
}

$action = $_POST['action'] ?? '';
$r_id = (int)($_POST['r_id'] ?? 0);
$user_id = $_SESSION['user_id'];

if (!$r_id) {
    echo json_encode(['error' => 'ID de réponse manquant']);
    exit;
}

try {
    if ($action === 'like') {
        // Vérifier si le like existe déjà
        $sql = "SELECT like_id FROM forum_likes WHERE user_id = ? AND r_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $r_id]);

        if (!$stmt->fetch()) {
            // Ajouter le like
            $sql = "INSERT INTO forum_likes (user_id, r_id) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $r_id]);
        }
    } elseif ($action === 'unlike') {
        // Supprimer le like
        $sql = "DELETE FROM forum_likes WHERE user_id = ? AND r_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $r_id]);
    }

    // Compter le nombre total de likes pour cette réponse
    $sql = "SELECT COUNT(*) as total_likes FROM forum_likes WHERE r_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$r_id]);
    $result = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'total_likes' => $result['total_likes']
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Erreur lors de l\'opération']);
}
?>