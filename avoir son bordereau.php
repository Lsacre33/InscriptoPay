<?php
// inscripto_bordereau.php
// =======================
// Page d’accueil InscriptoPay intégrant le bordereau RAWBANK vide
// =======================

?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Bordereau de versement des espèces</title>
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

    /* ===================== Bordereau RAWBANK ===================== */

    /* Conteneur bordereau simulant un A4 paysage */
    .bordereau-wrapper {
      width: 800px;               /* Largeur fixe pour image imprimée */
      margin: 0 auto 40px;
      border: 1px solid #000;     /* Cadre extérieur d’1px */
      box-sizing: border-box;
      position: relative;
      padding: 8px;
      background-color: #fff;
    }
    /* En-tête RAWBANK */
    .b-header-bank {
      font-size: 10px;
      line-height: 1.2;
      margin-bottom: 4px;
    }
    .b-header-bank b {
      font-size: 16px;
      letter-spacing: 1px;
    }
    /* Date en haut à droite */
    .b-date-line {
      position: absolute;
      top: 12px;
      right: 12px;
      font-size: 12px;
    }
    .b-date-line span {
      display: inline-block;
      width: 100px;
      border-bottom: 1px solid #000;
      text-align: center;
      margin-left: 4px;
      height: 14px;
      line-height: 14px;
    }
    /* Titre central et Numéro */
    .b-title-container {
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
      margin-bottom: 4px;
    }
    .b-bordero-title {
      border: 1px solid #000;
      padding: 2px 8px;
      font-weight: bold;
      font-size: 14px;
      letter-spacing: 1px;
    }
    .b-numero-text {
      position: absolute;
      right: 0;
      font-size: 12px;
    }
    .b-numero-text span {
      display: inline-block;
      width: 80px;
      border-bottom: 1px solid #000;
      text-align: center;
      margin-left: 4px;
      height: 14px;
      line-height: 14px;
    }
    /* Conteneur principal bordereau – deux colonnes */
    .b-main-sections {
      display: flex;
      width: 100%;
      margin-top: 4px;
    }
    /* Colonne de gauche */
    .b-left-column {
      width: 52%;
      border: 1px solid #000;
      border-collapse: collapse;
      display: flex;
      flex-direction: column;
    }
    .b-left-section {
      border-bottom: 1px solid #000;
      padding: 4px 6px;
      box-sizing: border-box;
    }
    .b-left-section:last-child {
      border-bottom: none;
    }
    .b-left-section-title {
      font-weight: bold;
      font-size: 13px;
      margin-bottom: 4px;
    }
    /* Libellés et soulignements */
    .b-field-label {
      display: inline-block;
      vertical-align: top;
      width: 80px;
      font-size: 12px;
    }
    .b-field-underline {
      display: inline-block;
      vertical-align: top;
      border-bottom: 1px solid #000;
      width: 220px;
      height: 14px;
      margin-left: 4px;
      box-sizing: border-box;
    }
    /* Compte à créditer – 14 cases + DEV */
    .b-boxed-label-numero {
      text-align: center;
      width: calc(14 * 20px);
      font-size: 11px;
      margin-bottom: 2px;
      box-sizing: border-box;
    }
    .b-boxed-row-14 {
      display: flex;
      gap: 2px;
    }
    .b-boxed-row-14 .b-box {
      width: 18px;
      height: 18px;
      border: 1px solid #000;
      box-sizing: border-box;
      background-color: #fff;
    }
    .b-boxed-label-dev {
      display: inline-block;
      width: 30px;
      text-align: center;
      font-size: 11px;
      margin-left: 8px;
      margin-bottom: 2px;
      box-sizing: border-box;
    }
    .b-boxed-dev {
      width: 30px;
      height: 18px;
      border-bottom: 1px solid #000;
      box-sizing: border-box;
    }
    /* Montant en lettres – souligné libre */
    .b-montant-letters-container {
      margin-top: 8px;
      display: flex;
      align-items: center;
    }
    .b-montant-letters-label {
      font-size: 11px;
      margin-right: 8px;
      white-space: nowrap;
    }
    .b-montant-letters-underline {
      border-bottom: 1px solid #000;
      flex: 1;
      height: 14px;
      box-sizing: border-box;
    }
    /* Montant en chiffres – 12 cases */
    .b-montant-chiffres-container {
      margin-top: 4px;
      display: flex;
      align-items: center;
    }
    .b-montant-chiffres-label {
      font-size: 11px;
      margin-right: 8px;
      white-space: nowrap;
      width: 48px;
      text-align: left;
    }
    .b-boxed-row-chiffres {
      display: flex;
      gap: 2px;
      flex: 1;
    }
    .b-boxed-row-chiffres .b-box-chiffre {
      width: 18px;
      height: 18px;
      border: 1px solid #000;
      box-sizing: border-box;
      background-color: #fff;
    }
    /* Colonne de droite */
    .b-right-column {
      width: 48%;
      border: 1px solid #000;
      border-left: none;
      display: flex;
      flex-direction: column;
      box-sizing: border-box;
    }
    .b-detail-header, .b-detail-body, .b-detail-footer {
      border-bottom: 1px solid #000;
      padding: 4px 6px;
      box-sizing: border-box;
    }
    .b-detail-header {
      display: flex;
      font-weight: bold;
      font-size: 13px;
    }
    .b-detail-header .b-header-cell {
      width: 32%;
      border: 1px solid #000;
      text-align: center;
      padding: 2px 0;
      font-size: 12px;
      box-sizing: border-box;
      background-color: #fff;
    }
    .b-detail-body {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 2px;
      padding: 2px 0;
      box-sizing: border-box;
    }
    .b-detail-row {
      display: flex;
      gap: 2px;
    }
    .b-detail-row .b-cell {
      width: 32%;
      height: 18px;
      border: 1px solid #000;
      box-sizing: border-box;
      background-color: #fff;
    }
    .b-detail-footer {
      display: flex;
      align-items: center;
      padding-top: 4px;
      box-sizing: border-box;
    }
    .b-total-label {
      font-weight: bold;
      font-size: 13px;
      flex: 1;
      text-align: right;
      margin-right: 4px;
      box-sizing: border-box;
    }
    .b-total-value {
      width: 32%;
      height: 18px;
      border: 1px solid #000;
      box-sizing: border-box;
      background-color: #fff;
    }
    /* Signatures */
    .b-signatures {
      display: flex;
      justify-content: space-between;
      margin-top: 8px;
      font-size: 11px;
    }
    .b-sign-box {
      width: 45%;
      text-align: center;
      border-top: 1px solid #000;
      padding-top: 2px;
      box-sizing: border-box;
    }

    /* Bouton d’action */
    .actions {
      text-align: right;
      margin-top: 12px;
    }
    .btn {
      background-color: #002147;
      color: #fff;
      padding: 6px 12px;
      font-size: 12px;
      font-weight: bold;
      border: none;
      cursor: pointer;
      margin-left: 4px;
    }
    .btn:hover {
      background-color: #001531;
    }

    /* Responsive bordereau (pour mobiles) */
    @media (max-width: 820px) {
      .bordereau-wrapper {
        width: 100%;
        margin: 20px 0;
        padding: 4px;
      }
      .b-field-underline {
        width: 150px;
      }
      .b-boxed-row-14 .b-box,
      .b-boxed-row-chiffres .b-box-chiffre {
        width: 14px;
        height: 14px;
      }
      .b-montant-letters-underline {
        height: 12px;
      }
      .b-montant-chiffres-label {
        font-size: 10px;
      }
      .b-montant-letters-label {
        font-size: 10px;
      }
    }
  </style>
</head>
<body>

  <!-- ===================== HEADER ===================== -->
  <header>
    <!-- Logo principal en haut à gauche -->
    <img src="img/logo.png" alt="Logo InscriptoPay" class="header-logo">
    <div></div>
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
      <li onclick="navigateTo('suivi.php')">
        🔍 Suivi en temps réel
      </li>
      <li class="active" onclick="navigateTo('avoir son bordereau.php')">
        🧾 Avoir son bordereau
      </li>
    </ul>
  </div>

  <!-- Bouton tiroir en bas à droite (emoji) -->
  <div id="drawerButton" class="drawer-button" aria-label="Ouvrir le menu" role="button">📂</div>

  <!-- ===================== CONTENU PRINCIPAL ===================== -->
  <main>
    <!-- Bordereau RAWBANK statique intégré -->
    <div class="bordereau-wrapper">
      <!-- En-tête RAWBANK -->
      <div class="b-header-bank">
        <b>RAWBANK</b><br>
        RAWBANK S.A. – Siège : 6 avenue M’Zée Laurent Désiré Kabila, Kinshasa/Gombe<br>
        NRC CD KIN O B3359 – BCC : 5120 – Tél : (243) 81 554 73 99 – Fax : (243) 81 552 35 00<br>
        B.P. 2499 KIN – E-mail : contact@rawbank.cd
      </div>

      <!-- Date en haut à droite -->
      <div class="b-date-line">
        Kinshasa, le <span>__/__/____</span>
      </div>

      <!-- Titre central et Numéro -->
      <div class="b-title-container">
        <div class="b-bordero-title">BORDEREAU DE VERSEMENT DES ESPÈCES</div>
        <div class="b-numero-text">N° <span>_______</span></div>
      </div>

      <!-- Conteneur principal bordereau – deux colonnes -->
      <div class="b-main-sections">
        <!-- Colonne Gauche -->
        <div class="b-left-column">
          <!-- Section COMPTE À CRÉDITER -->
          <div class="b-left-section">
            <div class="b-left-section-title">COMPTE À CRÉDITER</div>

            <!-- Ligne Agence de : ________________________ -->
            <div>
              <span class="b-field-label">Agence de :</span>
              <span class="b-field-underline"></span>
            </div>

            <!-- Ligne "NUMÉRO" (étiquette) et 14 cases + DEV -->
            <div style="margin-top:8px;">
              <div class="b-boxed-label-numero">NUMÉRO</div>
              <div class="b-boxed-row-14">
                <?php for ($i = 0; $i < 14; $i++): ?>
                  <div class="b-box"></div>
                <?php endfor; ?>
                <!-- Case DEV à droite -->
                <div class="b-boxed-label-dev">DEV</div>
                <div class="b-boxed-dev"></div>
              </div>
            </div>

            <!-- Ligne Intitulé : ________________________ -->
            <div style="margin-top:8px;">
              <span class="b-field-label">Intitulé :</span>
              <span class="b-field-underline"></span>
            </div>

            <!-- Ligne Motif : __________________________ -->
            <div style="margin-top:8px;">
              <span class="b-field-label">Motif :</span>
              <span class="b-field-underline"></span>
            </div>

            <!-- Montant en lettres – souligné libre -->
            <div class="b-montant-letters-container">
              <div class="b-montant-letters-label">MONTANT EN LETTRES</div>
              <div class="b-montant-letters-underline"></div>
            </div>

            <!-- Montant en chiffres – 12 cases -->
            <div class="b-montant-chiffres-container">
              <div class="b-montant-chiffres-label">MONTANT EN CHIFFRES</div>
              <div class="b-boxed-row-chiffres">
                <?php for ($j = 0; $j < 12; $j++): ?>
                  <div class="b-box-chiffre"></div>
                <?php endfor; ?>
              </div>
            </div>
          </div>

          <!-- Section IDENTITÉ DE LA PARTIE VERSANTE -->
          <div class="b-left-section">
            <div class="b-left-section-title">IDENTITÉ DE LA PARTIE VERSANTE</div>

            <!-- Nom : _________________________________ -->
            <div>
              <span class="b-field-label">Nom :</span>
              <span class="b-field-underline" style="width:180px;"></span>
            </div>

            <!-- Adresse : ______________________________ -->
            <div style="margin-top:8px;">
              <span class="b-field-label">Adresse :</span>
              <span class="b-field-underline" style="width:180px;"></span>
            </div>

            <!-- Téléphone : ____________________________ -->
            <div style="margin-top:8px;">
              <span class="b-field-label">Téléphone :</span>
              <span class="b-field-underline" style="width:180px;"></span>
            </div>
          </div>
        </div>

        <!-- Colonne Droite -->
        <div class="b-right-column">
          <!-- En-tête du DÉTAIL DU VERSEMENT -->
          <div class="b-detail-header">
            <div class="b-header-cell">COUPURE</div>
            <div class="b-header-cell">NOMBRE</div>
            <div class="b-header-cell">TOTAL PAR COUPURE</div>
          </div>
          <!-- Corps (10 lignes vides) -->
          <div class="b-detail-body">
            <?php for ($r = 0; $r < 10; $r++): ?>
              <div class="b-detail-row">
                <div class="b-cell"></div>
                <div class="b-cell"></div>
                <div class="b-cell"></div>
              </div>
            <?php endfor; ?>
          </div>
          <!-- Pied de tableau : TOTAL VERSÉ -->
          <div class="b-detail-footer">
            <div class="b-total-label">TOTAL VERSÉ :</div>
            <div class="b-total-value"></div>
          </div>
          <!-- Signatures -->
          <div class="b-signatures">
            <div class="b-sign-box">
              Signature du caissier<br>
              (Cachet de la banque)
            </div>
            <div class="b-sign-box">
              Signature de la partie versante
            </div>
          </div>
        </div>
      </div>

      <!-- Bouton pour imprimer/télécharger – fonctionne sur PC et mobile -->
      <div class="actions">
        <button type="button" class="btn" onclick="window.print()">TÉLÉCHARGER PDF</button>
      </div>
    </div>
  </main>

  <!-- ===================== Scripts JavaScript ===================== -->
  <script>
    const drawer        = document.getElementById('drawer');
    const drawerButton  = document.getElementById('drawerButton');

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
    });

    // Redirection vers une autre page
    function navigateTo(page) {
      window.location.href = page;
    }
  </script>
</body>
</html>
