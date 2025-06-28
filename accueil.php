<?php
// accueil.php (page de notification + affichage de la cloche)
// =======================
// Page d'accueil modernisée pour InscriptoPay (affiche les notifications réelles depuis la BDD)
// =======================

require_once __DIR__ . '/db_config.php';

// 1) Récupérer la date du jour
$date = date('Y-m-d');

// 2) Requête pour obtenir les étudiants notifiés aujourd'hui (colonne 'notified' = 1)
//    On récupère nom, postnom, prenom, timestamp pour l'affichage
try {
    $stmtNotif = $pdo->prepare('
        SELECT nom, postnom, prenom, `timestamp`
        FROM registrations
        WHERE `date` = :date AND notified = 1 AND archived = 0
        ORDER BY `timestamp` DESC
    ');
    $stmtNotif->execute([':date' => $date]);
    $rowsNotif = $stmtNotif->fetchAll();
} catch (\PDOException $e) {
    $rowsNotif = [];
}

// Construire le tableau $notifications pour l'affichage dans la cloche
$notifications = [];
foreach ($rowsNotif as $row) {
    $fullName = htmlspecialchars($row['nom'] . ' ' . $row['postnom'] . ' ' . $row['prenom']);
    // On formate l'heure de notification en HH:MM
    $timeStr = date('H:i', strtotime($row['timestamp']));
    $notifications[] = [
        'text' => "$fullName – C'est votre tour",
        'time' => $timeStr
    ];
}
$notifCount = count($notifications);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>InscriptoPay – Accueil</title>
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

    /* Conteneur notification */
    .notif-wrapper {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      width: 48px;
      height: 48px;
      margin-left: 16px;
      font-size: 0; /* On gère la taille de l’emoji dans .notification-bell */
    }
    /* Icône de cloche sous forme d’emoji */
    .notification-bell {
      font-size: 28px;
      line-height: 1;
      display: inline-block;
      position: relative;
      cursor: pointer;
      user-select: none;
      color: var(--white);
    }
    /* Badge rouge avec compteur */
    .notification-badge {
      position: absolute;
      top: -4px;
      right: -4px;
      min-width: 18px;
      height: 18px;
      padding: 0 4px;
      background-color: var(--accent-red);
      color: var(--white);
      font-size: 12px;
      font-weight: bold;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 1px 4px var(--card-shadow);
    }
    /* Animation de rebond si notifications */
    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
      40% { transform: translateY(-6px); }
      60% { transform: translateY(-3px); }
    }
    .bounce {
      animation: bounce 1.5s infinite;
      animation-delay: 1s;
    }

    /* Dropdown notifications */
    .notif-dropdown {
      position: absolute;
      top: 60px;
      right: 0;
      width: 280px;
      background-color: var(--white);
      border-radius: 8px;
      box-shadow: 0 4px 16px var(--card-shadow);
      overflow: hidden;
      transform-origin: top right;
      transform: scaleY(0);
      transition: transform 0.2s ease-out;
      z-index: 1001;
    }
    .notif-dropdown.open {
      transform: scaleY(1);
    }
    .notif-header {
      background-color: var(--primary-blue);
      color: var(--white);
      padding: 12px 16px;
      font-size: 16px;
      font-weight: 600;
    }
    .notif-list {
      max-height: 300px;
      overflow-y: auto;
      background-color: var(--white);
    }
    .notif-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 16px;
      border-bottom: 1px solid #EFEFEF;
      font-size: 14px;
      color: var(--text-dark);
    }
    .notif-item:last-child {
      border-bottom: none;
    }
    .notif-text {
      flex: 1;
      margin-right: 8px;
    }
    .notif-time {
      font-size: 12px;
      color: var(--text-muted);
      white-space: nowrap;
    }
    .notif-empty {
      padding: 16px;
      text-align: center;
      font-size: 14px;
      color: var(--text-muted);
    }

    /* ============ Bouton tiroir (drawer) ============ */
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
      font-size: 32px;       /* Taille de l’emoji */
      color: var(--white);   /* Couleur de l’emoji */
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

    /* ============ Menu tiroir (sidebar) ============ */
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
      padding-top: 70px; /* pour ne pas recouvrir le header */
    }
    .drawer.open {
      left: 0;
    }
    .drawer ul {
      margin-top: 16px;
      flex: 1;
    }
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

    /* ---- Section Hero ---- */
    .hero {
      background-color: var(--white);
      border-radius: 8px;
      padding: 48px 24px;
      box-shadow: 0 4px 16px var(--card-shadow);
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      margin-bottom: 32px;
      position: relative;
      overflow: hidden;
    }
    .hero h1 {
      font-size: 36px;
      color: var(--dark-blue);
      margin-bottom: 16px;
    }
    .hero p.subtitle {
      font-size: 18px;
      color: var(--text-muted);
      margin-bottom: 24px;
      max-width: 600px;
      line-height: 1.5;
    }
    .hero .cta-button {
      background-color: var(--primary-blue);
      color: var(--white);
      padding: 14px 28px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 4px;
      transition: background-color 0.2s ease, transform 0.2s ease;
    }
    .hero .cta-button:hover {
      background-color: #004B7E;
      transform: translateY(-2px);
    }
    /* Décoration abstraite en arrière-plan de la Hero (ex. cercles légers) */
    .hero::before {
      content: "";
      position: absolute;
      top: -40px;
      right: -80px;
      width: 200px;
      height: 200px;
      background: var(--primary-blue);
      opacity: 0.04;
      border-radius: 50%;
      z-index: 0;
    }

    /* ============ Responsive ============ */
    @media (max-width: 768px) {
      .hero h1 {
        font-size: 28px;
      }
      .hero p.subtitle {
        font-size: 16px;
      }
    }

    /* ---- Section Fonctionnalités ---- */
    /* Ajout d'un margin-top pour espacer le bouton du début de la section */
    .features {
      margin-top: 24px;
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
    }
    .feature-card {
      background-color: var(--white);
      border-radius: 8px;
      box-shadow: 0 2px 12px var(--card-shadow);
      padding: 24px;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .feature-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 4px 20px var(--card-shadow);
    }
    .feature-card img.icon {
      width: 160px;
      height: 110px;
      margin-bottom: 16px;
    }
    .feature-card h3 {
      font-size: 20px;
      color: var(--dark-blue);
      margin-bottom: 12px;
    }
    .feature-card p {
      font-size: 14px;
      color: var(--text-muted);
      line-height: 1.5;
    }

    /* ---- Section Sécurité ---- */
    .security-section {
      margin-top: 40px;
      background-color: var(--white);
      border-radius: 8px;
      padding: 24px;
      box-shadow: 0 2px 12px var(--card-shadow);
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }
    .security-section h2 {
      font-size: 24px;
      color: var(--dark-blue);
      margin-bottom: 16px;
    }
    .security-section p {
      font-size: 16px;
      color: var(--text-muted);
      line-height: 1.6;
      margin-bottom: 16px;
    }
    .security-section img.guide {
      width: 100%;
      max-width: 500px;
      border-radius: 4px;
      margin-top: 16px;
      box-shadow: 0 1px 8px var(--card-shadow);
    }

    /* ============ Responsive ============ */
    @media (max-width: 768px) {
      .hero h1 {
        font-size: 28px;
      }
      .hero p.subtitle {
        font-size: 16px;
      }
      .feature-card {
        padding: 16px;
      }
      .feature-card img.icon {
        width: 48px;
        height: 48px;
      }
    }
  </style>
</head>
<body>

  <!-- ===================== HEADER ===================== -->
  <header>
    <!-- Logo principal en haut à gauche -->
    <img src="img/logo.png" alt="Logo InscriptoPay" class="header-logo">
    <div style="display: flex; align-items: center;">
      <!-- Cloche de notification -->
      <div id="notifWrapper" class="notif-wrapper" onclick="toggleNotifDropdown(event)">
        <div
          id="notifBell"
          class="notification-bell <?php if ($notifCount > 0) echo 'bounce'; ?>"
          aria-label="Notifications"
          role="button"
        >🔔</div>

        <?php if ($notifCount > 0): ?>
          <div id="notifBadge" class="notification-badge">
            <?php echo $notifCount; ?>
          </div>
        <?php endif; ?>

        <!-- Dropdown des notifications -->
        <div id="notifDropdown" class="notif-dropdown">
          <div class="notif-header">Notifications</div>
          <div class="notif-list">
            <?php if ($notifCount > 0): ?>
              <?php foreach ($notifications as $notif): ?>
                <div class="notif-item">
                  <div class="notif-text"><?php echo htmlspecialchars($notif['text']); ?></div>
                  <div class="notif-time"><?php echo htmlspecialchars($notif['time']); ?></div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="notif-empty">Aucune notification</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- ===================== DRAWER MENU ===================== -->
  <div id="drawer" class="drawer">
    <ul>
      <li class="active" onclick="navigateTo('accueil.php')">
        🏠 Accueil
      </li>
      <li onclick="navigateTo('enregistrement.php')">
        📝 Enregistrement
      </li>
      <li onclick="navigateTo('suivi.php')">
        🔍 Suivi en temps réel
      </li>
      <li onclick="navigateTo('avoir son bordereau.php')">
        🧾 Avoir son bordereau
      </li>
      <li onclick="navigateTo('dashboard_logout.php')">
         🔓 Déconnexion
      </li>
    </ul>
  </div>

  <!-- Bouton tiroir en bas à droite (emoji) -->
  <div id="drawerButton" class="drawer-button" aria-label="Ouvrir le menu" role="button">📂</div>

  <!-- ===================== CONTENU PRINCIPAL ===================== -->
  <main>
    <!-- ---- Section Hero ---- -->
    <section class="hero">
      <h1>Bienvenue sur InscriptoPay</h1>
      <p class="subtitle">
        Simplifiez vos enregistrements pour vos paiements et suivez votre appel en temps réel.
      </p>
      <button class="cta-button" onclick="navigateTo('enregistrement.php')">
        Enregistrez-vous dès maintenant
      </button>

      <!-- ---- Section Fonctionnalités ---- -->
      <section class="features">
        <div class="feature-card">
          <img src="img/enreg.png" alt="Enregistrement" class="icon">
          <h3>Enregistrement Rapide</h3>
          <p>
            Enregistrez-vous en quelques clics. Votre nom complet est entièrement enregistré
            et stocké en toute sécurité.
          </p>
        </div>
        <div class="feature-card">
          <img src="img/suivi.png" alt="Suivi de paiement" class="icon">
          <h3>Suivi en Temps Réel</h3>
          <p>
            Visualisez l’état de la liste  et recevez des notifications
            automatiques dès que le service d'appel est sur votre nom.
          </p>
        </div>
        <div class="feature-card">
          <img src="img/fiabilité.png" alt="Sécurité" class="icon">
          <h3>Fiabilité</h3>
          <p>
            La fiabilité est au cœur de notre service pour garantir votre tranquillité d’esprit.
          </p>
        </div>
      </section>

      <!-- ---- Section Sécurité Détail ---- -->
      <section class="security-section">
        <h2>Pourquoi choisir InscriptoPay ?</h2>
        <p>
          <h3>1. Enregistrement simplifié</h3>
          • InscriptoPay vous permet de vous inscrire rapidement en ligne pour figurer sur la liste de paiement des frais académiques, sans paperasse inutile..<br>
          <h3>2. Suivi en temps réel</h3>
          • Vous suivez l’évolution de votre situation en direct : validation de votre enregistrement et appel administratif.<br>
          <h3>3. Gain de temps et d'énergie</h3>
          • Finies les longues files d’attente. Vous gérez tout depuis votre téléphone ou ordinateur, à tout moment.<br>
          <h3>4. Fiabilité</h3>
          • Notifications automatiques pour ne jamais manquer une échéance.<br>
          <h3>5. Accessible à tous</h3>
          • Conçu pour les étudiants, même sans grandes compétences numériques. Interface simple, claire, en français et en anglais.<br>
        </p>
        <img src="img/pour.png" alt="Guide Sécurité" class="guide">
      </section>
    </section>
  </main>

  <!-- ===================== Scripts JavaScript ===================== -->
  <script>
    // Références aux éléments du DOM
    const drawer        = document.getElementById('drawer');
    const drawerButton  = document.getElementById('drawerButton');
    const notifWrapper  = document.getElementById('notifWrapper');
    const notifDropdown = document.getElementById('notifDropdown');

    // Ouvre/Ferme le drawer au clic sur le bouton
    drawerButton.addEventListener('click', function(event) {
      event.stopPropagation();
      drawer.classList.toggle('open');
    });

    // Ferme le drawer si on clique à l'extérieur
    document.addEventListener('click', function(event) {
      if (!drawer.contains(event.target) && !drawerButton.contains(event.target)) {
        drawer.classList.remove('open');
      }
      // Si on clique en dehors du dropdown de notifications, on le ferme
      if (!notifWrapper.contains(event.target)) {
        notifDropdown.classList.remove('open');
      }
    });

    // Redirection vers une autre page
    function navigateTo(page) {
      window.location.href = page;
    }

    // Affiche/masque le dropdown notifications
    function toggleNotifDropdown(event) {
      event.stopPropagation();
      notifDropdown.classList.toggle('open');
      // Si on ouvre le dropdown, on enlève l'animation bounce et le badge
      if (notifDropdown.classList.contains('open')) {
        const bell  = document.getElementById('notifBell');
        const badge = document.getElementById('notifBadge');
        if (bell)  bell.classList.remove('bounce');
        if (badge) badge.style.display = 'none';
        // Ici, on pourrait ajouter un appel AJAX pour marquer en base que l'utilisateur a consulté
      }
    }
  </script>
</body>
</html>
