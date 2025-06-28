<?php
// get_next_registration.php
header('Content-Type: application/json');

require_once __DIR__ . '/db_config.php'; // $pdo est disponible

// 1) On récupère la date du jour
$date = date('Y-m-d');

// 2) On compte le nombre d'enregistrements pour la date du jour
try {
    $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM registrations WHERE `date` = :date');
    $stmt->execute([':date' => $date]);
    $row = $stmt->fetch();
    $count = (int)$row['cnt'];
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'db_query']);
    exit;
}

// 3) Si la limite de 500 est atteinte, on renvoie une erreur
if ($count >= 500) {
    echo json_encode(['error' => 'limit_reached']);
    exit;
}

// 4) Sinon, on renvoie le prochain numéro (count + 1) et le nombre de places restantes
$next = $count + 1;
$remaining = 500 - $count;
echo json_encode([
    'next'      => $next,
    'remaining' => $remaining
]);
?>
