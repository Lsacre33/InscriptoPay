<?php
// delete_student.php
header('Content-Type: application/json');
require_once __DIR__ . '/db_config.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id']) || !ctype_digit(strval($data['id']))) {
    echo json_encode(['success' => false, 'error' => 'invalid_id']);
    exit;
}
$id = (int)$data['id'];
$today = date('Y-m-d');

try {
    // On ne supprime PAS physiquement ; on marque 'archived = 1'
    $stmt = $pdo->prepare('
        UPDATE registrations
        SET archived = 1
        WHERE id = :id AND `date` = :date
    ');
    $stmt->execute([':id' => $id, ':date' => $today]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'not_found']);
    }
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'db_error']);
}
?>
