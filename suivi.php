<?php
// suivi.php
// =======================
// Page de suivi des enregistrements pour la journée
// (AFFICHAGE DE TOUTES LES LIGNES, MÊME 'archived')
// =======================

require_once __DIR__ . '/db_config.php'; // Connexion PDO ($pdo)

$today = date('Y-m-d');
$userId = null;

// 1) Déterminer l’ID de l’enregistrement de l’utilisateur
if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $userId = (int)$_GET['id'];
} else {
    if (isset($_COOKIE['inscripto_id'], $_COOKIE['inscripto_date'])) {
        $cookieId   = $_COOKIE['inscripto_id'];
        $cookieDate = $_COOKIE['inscripto_date'];
        if (ctype_digit($cookieId) && $cookieDate === $today) {
            $userId = (int)$cookieId;
        }
    }
}

if ($userId === null) {
    echo "<p style='font-family:Segoe UI, Tahoma, Geneva, Verdana, sans-serif; color:#E53935; text-align:center; margin-top:40px;'>
            Aucun enregistrement trouvé pour aujourd'hui.<br>
            Veuillez vous inscrire d'abord ou réessayer.
          </p>";
    exit;
}

try {
    // 2) Récupérer l’enregistrement de l’utilisateur (incluant 'notified')
    $stmtUser = $pdo->prepare('
        SELECT id, nom, postnom, prenom, `date`, `number`, `timestamp`, `notified`
        FROM registrations
        WHERE id = :id
    ');
    $stmtUser->execute([':id' => $userId]);
    $userRow = $stmtUser->fetch();

    if (!$userRow) {
        echo "<p style='font-family:Segoe UI, Tahoma, Geneva, Verdana, sans-serif; color:#E53935; text-align:center; margin-top:40px;'>
                Enregistrement introuvable. Vérifiez votre lien ou inscrivez-vous à nouveau.
              </p>";
        exit;
    }

    // 3) Vérifier que l’enregistrement appartient bien à la date d’aujourd'hui
    if ($userRow['date'] !== $today) {
        echo "<p style='font-family:Segoe UI, Tahoma, Geneva, Verdana, sans-serif; color:#E53935; text-align:center; margin-top:40px;'>
                Votre ancien enregistrement n’est plus valide (date dépassée).<br>
                Veuillez vous inscrire à nouveau pour aujourd’hui.
              </p>";
        exit;
    }

    // 4) Récupérer tous les enregistrements du même jour (et même les 'archived' ; on garde l’historique),
    //    triés par 'number' (avec 'notified' pour savoir qui est coché)
    $stmtAll = $pdo->prepare('
        SELECT id, nom, postnom, prenom, `number`, `timestamp`, `notified`
        FROM registrations
        WHERE `date` = :date
        ORDER BY `number` ASC
    ');
    $stmtAll->execute([':date' => $today]);
    $allRows = $stmtAll->fetchAll();
} catch (\PDOException $e) {
    echo "<p style='font-family:Segoe UI, Tahoma, Geneva, Verdana, sans-serif; color:#E53935; text-align:center; margin-top:40px;'>
            Erreur de connexion à la base ou requête invalide. Veuillez réessayer plus tard.
          </p>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Suivi des enregistrements – InscriptoPay</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- ===================== Styles CSS intégrés ===================== -->
  <style>
    /* ============ Variables de couleurs ============ */
    :root {
      --primary-blue:       #005B9E;
      --dark-blue:          #002147;
      --light-gray-bg:      #F4F4F4;
      --white:              #FFFFFF;
      --text-dark:          #2E2E2E;
      --text-muted:         #666666;
      --card-shadow:        rgba(0, 0, 0, 0.08);
      --accent-red:         #E53935;
      --accent-green:       #4CAF50;
      --highlight-green-bg: rgba(76, 175, 80, 0.12);
    }

    /* ============ Reset minimal ============ */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    html, body {
      width: 100%;
      min-height: 100vh;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--light-gray-bg);
      color: var(--text-dark);
      overflow-x: hidden;
    }
    a {
      text-decoration: none;
      color: inherit;
    }
    ul {
      list-style: none;
    }
    button {
      cursor: pointer;
      border: none;
      background: none;
    }

    /* ============ Barre de navigation (header) ============ */
    header {
      position: fixed;
      top: 0; left: 0;
      width: 100%;
      height: 70px;
      background-color: var(--dark-blue);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 24px;
      box-shadow: 0 2px 12px var(--card-shadow);
      z-index: 1000;
    }
    .header-logo {
      height: 70px;
    }

    /* ============ Bouton tiroir & Sidebar ============ */
    .drawer-button {
      position: fixed;
      bottom: 24px;
      right: 24px;
      width: 64px;
      height: 64px;
      background-color: var(--primary-blue);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 16px var(--card-shadow);
      cursor: pointer;
      z-index: 1000;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      animation: pulse 2s infinite;
      font-size: 32px;
      color: var(--white);
      user-select: none;
    }
    .drawer-button:hover {
      transform: scale(1.08);
      box-shadow: 0 6px 20px var(--card-shadow);
    }
    @keyframes pulse {
      0%   { transform: scale(1); }
      50%  { transform: scale(1.05); }
      100% { transform: scale(1); }
    }
    .drawer {
      position: fixed;
      top: 0; left: -300px;
      width: 300px;
      height: 100%;
      background-color: var(--white);
      box-shadow: 2px 0 16px var(--card-shadow);
      transition: left 0.3s ease;
      z-index: 999;
      display: flex;
      flex-direction: column;
      padding-top: 70px;
    }
    .drawer.open { left: 0; }
    .drawer ul { margin-top: 16px; flex: 1; }
    .drawer li {
      padding: 16px 24px;
      font-size: 18px;
      color: var(--text-dark);
      cursor: pointer;
      display: flex;
      align-items: center;
      transition: background-color 0.2s ease;
    }
    .drawer li.active {
      background-color: var(--primary-blue);
      color: var(--white);
      border-radius: 4px;
    }
    .drawer li:not(.active):hover {
      background-color: rgba(0, 91, 158, 0.08);
    }
    .drawer li img.icon {
      width: 24px;
      height: 24px;
      margin-right: 12px;
      filter: invert(30%);
    }

    /* ============ Contenu principal ============ */
    main {
      margin-top: 90px;
      padding: 24px;
    }
    .page-title {
      font-size: 28px;
      color: var(--dark-blue);
      margin-bottom: 8px;
      text-align: center;
    }
    .user-greeting {
      font-size: 16px;
      color: var(--text-muted);
      text-align: center;
      margin-bottom: 24px;
      line-height: 1.4;
    }
    .tracking-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 16px;
    }
    .tracking-table th, .tracking-table td {
      padding: 12px 8px;
      border-bottom: 1px solid #EFEFEF;
      text-align: left;
      font-size: 14px;
    }
    .tracking-table th {
      background-color: var(--primary-blue);
      color: var(--white);
      font-weight: 600;
    }
    .tracking-table tr:hover {
      background-color: rgba(0, 0, 0, 0.04);
    }
    .tracking-table tr.own {
      background-color: var(--highlight-green-bg);
    }
    .notif-message {
      background-color: var(--accent-green);
      color: var(--white);
      font-weight: 600;
      padding: 8px;
      text-align: center;
    }

    @media (max-width: 768px) {
      .page-title {
        font-size: 24px;
      }
      .user-greeting {
        font-size: 14px;
      }
      .tracking-table th, .tracking-table td {
        font-size: 12px;
        padding: 8px 4px;
      }
    }
  </style>
</head>
<body>

  <!-- ===================== HEADER ===================== -->
  <header>
    <img src="img/logo.png" alt="Logo InscriptoPay" class="header-logo">
  </header>

  <!-- ===================== DRAWER MENU ===================== -->
  <div id="drawer" class="drawer">
    <ul>
      <li onclick="navigateTo('accueil.php')">
        🏠 Accueil
      </li>
      <li onclick="navigateTo('enregistrement.php')">
        📝 Enregistrement
      </li>
      <li class="active" onclick="navigateTo('suivi.php?id=<?= $userId ?>')">
        🔍 Suivi en temps réel
      </li>
      <li onclick="navigateTo('avoir son bordereau.php')">
        🧾 Avoir son bordereau
      </li>
    </ul>
  </div>

  <!-- Bouton tiroir en bas à droite -->
  <div id="drawerButton" class="drawer-button" aria-label="Ouvrir le menu" role="button">📂</div>

  <!-- ===================== CONTENU PRINCIPAL ===================== -->
  <main>
    <h1 class="page-title">Suivi des enregistrements du <?= htmlspecialchars($today) ?></h1>
    <p class="user-greeting">
      Bonjour <?= htmlspecialchars($userRow['prenom'] . ' ' . $userRow['postnom'] . ' ' . $userRow['nom']) ?>,<br>
      vous êtes le numéro <?= htmlspecialchars($userRow['number']) ?> aujourd’hui.
      <?php if ($userRow['notified'] == 1): ?>
        <br><span class="notif-message">Appel arrivé pour vous 💡</span>
      <?php endif; ?>
    </p>

    <table class="tracking-table">
      <thead>
        <tr>
          <th>Numéro</th>
          <th>Nom</th>
          <th>Post-nom</th>
          <th>Prénom</th>
          <th>Heure</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($allRows as $row):
            $isOwn = ($row['id'] == $userId);
            // Si cet étudiant a été notifié, on affiche d'abord une ligne de message
            if ($row['notified'] == 1): ?>
              <tr>
                <td colspan="5" class="notif-message">
                  Appel arrivé pour <?= htmlspecialchars($row['prenom'] . ' ' . $row['postnom'] . ' ' . $row['nom']) ?>
                </td>
              </tr>
        <?php endif; ?>
          <tr class="<?= $isOwn ? 'own' : '' ?>">
            <td><?= htmlspecialchars($row['number']) ?></td>
            <td><?= htmlspecialchars($row['nom']) ?></td>
            <td><?= htmlspecialchars($row['postnom']) ?></td>
            <td><?= htmlspecialchars($row['prenom']) ?></td>
            <td><?= htmlspecialchars(date('H:i:s', strtotime($row['timestamp']))) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>

  <!-- ===================== Scripts JavaScript ===================== -->
  <script>
    const drawer       = document.getElementById('drawer');
    const drawerButton = document.getElementById('drawerButton');

    drawerButton.addEventListener('click', function(event) {
      event.stopPropagation();
      drawer.classList.toggle('open');
    });
    document.addEventListener('click', function(event) {
      if (!drawer.contains(event.target) && !drawerButton.contains(event.target)) {
        drawer.classList.remove('open');
      }
    });

    function navigateTo(page) {
      window.location.href = page;
    }
  </script>
</body>
</html>
