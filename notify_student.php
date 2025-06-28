<?php
// notify_student.php
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
    // 1) Remise à zéro de 'notified' pour TOUS les enregistrements du jour
    $stmtReset = $pdo->prepare('UPDATE registrations SET notified = 0 WHERE `date` = :date');
    $stmtReset->execute([':date' => $today]);

    // 2) Passage à 1 de 'notified' pour l’ID sélectionné
    $stmtSet = $pdo->prepare('UPDATE registrations SET notified = 1 WHERE id = :id AND `date` = :date');
    $stmtSet->execute([':id' => $id, ':date' => $today]);

    if ($stmtSet->rowCount() > 0) {
        // 3) Récupérer les infos de l'étudiant pour envoyer l'email
        $stmtInfo = $pdo->prepare('
            SELECT nom, postnom, prenom, email
            FROM registrations
            WHERE id = :id AND `date` = :date
        ');
        $stmtInfo->execute([':id' => $id, ':date' => $today]);
        $etudiant = $stmtInfo->fetch();

        if ($etudiant) {
            $fullName = $etudiant['nom'] . ' ' . $etudiant['postnom'] . ' ' . $etudiant['prenom'];
            $to       = $etudiant['email'];
            $subject  = "InscriptoPay – C'est votre tour";
            $message  = "Bonjour $fullName,\r\n\r\nVotre appel est arrivé à votre nom. C'est votre tour.\r\n\r\nCordialement,\r\nL'équipe InscriptoPay";
            $headers  = "From: no-reply@inscriptopay.com\r\n" .
                        "Reply-To: no-reply@inscriptopay.com\r\n" .
                        "Content-Type: text/plain; charset=UTF-8\r\n";
            // Envoi de l'email
            @mail($to, $subject, $message, $headers);
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'not_found_or_already']);
    }
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'db_error']);
}
?>
