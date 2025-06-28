<?php
// db_config.php
// --------------------
// Fichier de configuration PDO pour XAMPP (localhost, user=root, pas de mot de passe)

$host = 'localhost';
$db   = 'inscriptopay';
$user = 'root';
$pass = '';          // Par défaut, XAMPP ne donne pas de mot de passe à l'utilisateur root
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Activer les exceptions PDO
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Résultat sous forme associative
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Désactiver l'émulation des requêtes préparées
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // En cas d'erreur de connexion, on renvoie un JSON d'erreur (utilisé par AJAX)
    http_response_code(500);
    echo json_encode(['error' => 'db_connection']);
    exit;
}
?>
