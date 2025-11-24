<?php
session_start();

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'u482683110_26LALIE_BDD');
define('DB_USER', 'u482683110_26LALIE');
define('DB_PASS', 'I6lal30?');

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Erreur de connexion : " . $e->getMessage());
    die("Erreur de connexion à la base de données. Veuillez réessayer plus tard.");
}

// Vérifier si l'utilisateur est connecté
function est_connecte() {
    return isset($_SESSION['user_id']);
}

// Vérifier si l'utilisateur est admin
function est_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Journalisation
function logger($action, $details = null, $user_id = null) {
    global $pdo;
    
    if ($user_id === null && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'inconnue';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'inconnu';
    
    $sql = "INSERT INTO forum_logs (user_id, action, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $action, $details, $ip_address, $user_agent]);
}

// Créer une notification
function creer_notification($user_id, $titre, $contenu, $lien = null) {
    global $pdo;
    
    $sql = "INSERT INTO forum_notifications (user_id, titre, contenu, lien) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$user_id, $titre, $contenu, $lien]);
}

// Récupérer les notifications de l'utilisateur
function get_notifications($user_id, $pdo, $non_lues_seulement = true) {
    $sql = "SELECT * FROM forum_notifications WHERE user_id = ?";
    
    if ($non_lues_seulement) {
        $sql .= " AND is_read = 0";
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT 10";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

// Valider et sécuriser les entrées
function valider_entree($data, $type = 'texte') {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    
    switch ($type) {
        case 'email':
            if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
            break;
        case 'int':
            $data = filter_var($data, FILTER_VALIDATE_INT);
            break;
        case 'url':
            $data = filter_var($data, FILTER_VALIDATE_URL);
            break;
    }
    
    return $data;
}

// Gestion des erreurs
function gerer_exception($e) {
    error_log("Erreur: " . $e->getMessage() . " dans " . $e->getFile() . " ligne " . $e->getLine());
    
    if (est_connecte() && est_admin()) {
        // Afficher les détails aux admins
        return "Erreur: " . $e->getMessage();
    } else {
        // Message générique pour les utilisateurs normaux
        return "Une erreur s'est produite. Notre équipe a été notifiée.";
    }
}

// Pagination
function get_pagination($page_actuelle, $total_elements, $elements_par_page = 10) {
    $total_pages = ceil($total_elements / $elements_par_page);
    $page_actuelle = max(1, min($page_actuelle, $total_pages));
    
    return [
        'page_actuelle' => $page_actuelle,
        'total_pages' => $total_pages,
        'offset' => ($page_actuelle - 1) * $elements_par_page,
        'elements_par_page' => $elements_par_page
    ];
}
?>