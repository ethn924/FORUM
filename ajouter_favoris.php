<?php
require_once 'config.php';
require_once 'fonctions.php';

if (!est_connecte()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

$question_id = $_POST['q_id'] ?? 0;
$action = $_POST['action'] ?? 'toggle';

if (!$question_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID invalide']);
    exit;
}

// Vérifier que la question existe
$sql = "SELECT q_id FROM forum_question WHERE q_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$question_id]);

if (!$stmt->fetch()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Question non trouvée']);
    exit;
}

// Vérifier si c'est déjà en favoris
$sql = "SELECT id FROM forum_favoris WHERE user_id = ? AND question_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id'], $question_id]);
$existe = $stmt->fetch();

$is_favoris = false;

if ($action === 'add' || ($action === 'toggle' && !$existe)) {
    // Ajouter aux favoris
    if (!$existe) {
        $sql = "INSERT INTO forum_favoris (user_id, question_id) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $question_id]);
        $is_favoris = true;
    }
} elseif ($action === 'remove' || ($action === 'toggle' && $existe)) {
    // Retirer des favoris
    if ($existe) {
        $sql = "DELETE FROM forum_favoris WHERE user_id = ? AND question_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$_SESSION['user_id'], $question_id]);
        $is_favoris = false;
    }
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'is_favoris' => $is_favoris]);
exit;
?>
