<?php
// submit_registration.php
header('Content-Type: application/json');

require_once __DIR__ . '/db_config.php'; // Connexion PDO ($pdo)

// 1) Récupération des champs POST
$nom      = isset($_POST['nom'])      ? trim($_POST['nom'])      : '';
$postnom  = isset($_POST['postnom'])  ? trim($_POST['postnom'])  : '';
$prenom   = isset($_POST['prenom'])   ? trim($_POST['prenom'])   : '';
$email    = isset($_POST['email'])    ? trim($_POST['email'])    : '';

// 2) Validation côté serveur (lettres accentuées + apostrophe + email valide)
$namePattern  = "/^[A-Za-zÀ-ÖØ-öø-ÿ']+$/";
$emailPattern = "/^[^\s@]+@[^\s@]+\.[^\s@]+$/";

if (!preg_match($namePattern, $nom)) {
    echo json_encode(['error' => 'invalid_nom']);
    exit;
}
if (!preg_match($namePattern, $postnom)) {
    echo json_encode(['error' => 'invalid_postnom']);
    exit;
}
if (!preg_match($namePattern, $prenom)) {
    echo json_encode(['error' => 'invalid_prenom']);
    exit;
}
if (!preg_match($emailPattern, $email)) {
    echo json_encode(['error' => 'invalid_email']);
    exit;
}

// 3) Date du jour
$date = date('Y-m-d');

try {
    // 4) On compte combien d'enregistrements existent déjà pour aujourd'hui
    $stmtCount = $pdo->prepare('SELECT COUNT(*) AS cnt FROM registrations WHERE `date` = :date');
    $stmtCount->execute([':date' => $date]);
    $row = $stmtCount->fetch();
    $count = (int)$row['cnt'];

    if ($count >= 500) {
        // Si la limite de 500 enregistrements est atteinte
        echo json_encode(['error' => 'limit_reached']);
        exit;
    }

    // 5) On calcule le numéro journalier
    $nextNumber = $count + 1;

    // 6) On insère la nouvelle ligne (en veillant à avoir ajouté au préalable
    //    la colonne 'email' dans la table 'registrations' : ALTER TABLE registrations ADD email VARCHAR(255) NOT NULL;
    $stmtInsert = $pdo->prepare('
        INSERT INTO registrations (nom, postnom, prenom, email, `date`, `number`, `timestamp`)
        VALUES (:nom, :postnom, :prenom, :email, :date, :number, NOW())
    ');
    $stmtInsert->execute([
        ':nom'    => $nom,
        ':postnom'=> $postnom,
        ':prenom' => $prenom,
        ':email'  => $email,
        ':date'   => $date,
        ':number'=> $nextNumber
    ]);
    $newId = $pdo->lastInsertId();

    // 7) Calcul de la date d’expiration du cookie (minuit prochain)
    $tomorrowMidnight = strtotime('tomorrow');

    // 8) Définir les cookies pour l’utilisateur : ID, date et email
    setcookie('inscripto_id', $newId, $tomorrowMidnight, '/');
    setcookie('inscripto_date', $date, $tomorrowMidnight, '/');
    setcookie('inscripto_email', $email, $tomorrowMidnight, '/');

    // 9) On renvoie la réponse de succès en JSON (avec l’ID généré)
    echo json_encode([
        'success' => true,
        'number'  => $nextNumber,
        'id'      => $newId
    ]);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'db_query']);
    exit;
}
?>
