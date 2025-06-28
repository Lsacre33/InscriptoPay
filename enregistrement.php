<?php
// accueil.php (page d'enregistrement quotidien)
// =======================
// Page d'accueil modernisée pour InscriptoPay (affiche uniquement le jour actuel, avec ajout du champ email)
// =======================
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>InscriptoPay – Accueil / Enregistrement</title>
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
      text-align:center;
    }

    /* ---- Section Enregistrement ---- */
    .registration-section {
      background-color: var(--white);
      border-radius: 8px;
      padding: 24px;
      box-shadow: 0 2px 12px var(--card-shadow);
      max-width: 500px;
      margin: 0 auto;
    }
    .registration-section h2 {
      font-size: 24px;
      color: var(--dark-blue);
      margin-bottom: 16px;
      text-align: center;
    }
    .day-buttons {
      display: flex;
      justify-content: center;
      margin-top: 16px;
    }
    .day-button {
      background: var(--white);
      border-radius: 8px;
      box-shadow: 0 2px 12px var(--card-shadow);
      padding: 16px 24px;
      text-align: center;
      font-size: 18px;
      color: var(--dark-blue);
      cursor: pointer;
      transition: transform 0.2s ease, background-color 0.2s ease, color 0.2s ease;
    }
    .day-button:hover {
      transform: translateY(-2px);
    }
    /* Style spécial pour le jour courant afin de l’attirer */
    .day-button.current {
      background: var(--accent-green);
      color: var(--white);
      animation: pulse 1.5s infinite;
    }

    /* ---- Message et compte à rebours ---- */
    .time-info {
      text-align: center;
      margin-top: 12px;
      font-size: 16px;
      color: var(--accent-red);
    }
    .countdown {
      text-align: center;
      font-size: 18px;
      font-weight: bold;
      color: var(--primary-blue);
      margin-top: 8px;
    }

    /* ---- Modal d'enregistrement ---- */
    .registration-modal {
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1002;
    }
    .registration-modal.open {
      display: flex;
    }
    .modal-content {
      background: var(--white);
      padding: 24px;
      border-radius: 8px;
      position: relative;
      width: 90%;
      max-width: 400px;
      text-align: center;
      box-shadow: 0 4px 16px var(--card-shadow);
    }
    .modal-content .close-button {
      position: absolute;
      top: 12px; right: 12px;
      font-size: 24px;
      background: none;
      border: none;
      cursor: pointer;
      color: var(--text-muted);
    }
    .modal-content .robot-image {
      width: 80px;
      margin-bottom: 16px;
    }
    .modal-content h3 {
      font-size: 20px;
      color: var(--dark-blue);
      margin-bottom: 16px;
    }

    /* --- Affichage du numéro d'enregistrement et limite --- */
    .registration-number {
      font-size: 18px;
      font-weight: bold;
      color: var(--primary-blue);
      margin-bottom: 12px;
    }
    .limit-message {
      font-size: 14px;
      color: var(--accent-red);
      margin-bottom: 12px;
      display: none;
    }

    .modal-content form {
      display: flex;
      flex-direction: column;
      align-items: stretch;
    }
    .modal-content form label {
      font-size: 14px;
      margin-top: 8px;
      margin-bottom: 4px;
      text-align: left;
      color: var(--text-dark);
    }
    .modal-content form input {
      padding: 8px;
      margin-bottom: 6px;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 14px;
    }
    .modal-content form input[type="email"] {
      /* Aucun style particulier, justifié par le type */
    }
    .modal-content form .error-message {
      font-size: 12px;
      color: var(--accent-red);
      margin-bottom: 10px;
      display: none; /* Masqué par défaut */
      text-align: left;
    }
    .modal-content form button.submit-button {
      background-color: var(--primary-blue);
      color: var(--white);
      padding: 10px 20px;
      border-radius: 4px;
      font-size: 16px;
      margin-top: 8px;
      transition: background-color 0.2s ease, transform 0.2s ease;
    }
    .modal-content form button.submit-button:hover {
      background-color: #004B7E;
      transform: translateY(-1px);
    }
    .success-message {
      display: none;
      font-size: 16px;
      color: var(--accent-green);
      margin-top: 16px;
    }

    /* ============ Responsive ============ */
    @media (max-width: 768px) {
      .day-button {
        font-size: 16px;
        padding: 14px 20px;
      }
      .registration-section h2 {
        font-size: 20px;
      }
      .modal-content {
        padding: 16px;
      }
      .modal-content h3 {
        font-size: 18px;
      }
      .modal-content form input, .modal-content form button.submit-button {
        font-size: 14px;
      }
      .time-info {
        font-size: 14px;
      }
      .countdown {
        font-size: 16px;
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
      <li class="active" onclick="navigateTo('enregistrement.php')">
        📝 Enregistrement
      </li>
      <li onclick="navigateTo('suivi.php')">
        🔍 Suivi en temps réel
      </li>
      <li onclick="navigateTo('avoir son bordereau.php')">
        🧾 Avoir son bordereau
      </li>
    </ul>
  </div>

  <!-- Bouton tiroir en bas à droite (emoji) -->
  <div id="drawerButton" class="drawer-button" aria-label="Ouvrir le menu" role="button">📂</div>

  <!-- ===================== CONTENU PRINCIPAL ===================== -->
  <main>
    <!-- Section Enregistrement pour la semaine (affiche uniquement le jour actuel) -->
    <section class="registration-section">
      <h2>Enregistrement quotidien</h2>
      <p>Les enregistrements sont ouverts de 00H à 13H 30.</p>
      <div id="day-buttons" class="day-buttons">
        <!-- Le bouton du jour actuel sera généré dynamiquement -->
      </div>
      <div id="time-info" class="time-info"></div>
      <div id="countdown" class="countdown"></div>
    </section>

    <!-- Modal d'enregistrement -->
    <div id="registration-modal" class="registration-modal">
      <div class="modal-content">
        <button type="button" class="close-button" aria-label="Fermer">&times;</button>
        <img src="img/tete.png" alt="Robot" class="robot-image">
        <h3>Enregistrement – <span id="selected-day-name"></span></h3>

        <!-- Affichage du numéro et message de limite -->
        <div id="registration-number" class="registration-number"></div>
        <div id="limit-message" class="limit-message">La limite de 500 enregistrements pour aujourd’hui est atteinte, revenez demain.</div>

        <form id="registration-form">
          <label for="nom">Nom</label>
          <input type="text" id="nom" name="nom" required placeholder="Ex : Ngoma">
          <div id="error-nom" class="error-message">Le nom ne doit contenir que des lettres.</div>

          <label for="postnom">Post-nom</label>
          <input type="text" id="postnom" name="postnom" required placeholder="Ex : Mukendi">
          <div id="error-postnom" class="error-message">Le post-nom ne doit contenir que des lettres.</div>

          <label for="prenom">Prénom</label>
          <input type="text" id="prenom" name="prenom" required placeholder="Ex : Alain">
          <div id="error-prenom" class="error-message">Le prénom ne doit contenir que des lettres.</div>

          <!-- Nouveau champ Email -->
          <label for="email">Adresse Email</label>
          <input type="email" id="email" name="email" required placeholder="exemple@domaine.com">
          <div id="error-email" class="error-message">Veuillez entrer une adresse email valide.</div>

          <button type="submit" class="submit-button">S'enregistrer</button>
        </form>

        <div id="success-message" class="success-message">
          Enregistrement effectué ! Vous êtes n° <span id="success-number"></span>.<br>
          Vous allez être redirigé vers la page de suivi… 
        </div>
      </div>
    </div>
  </main>

  <!-- ===================== Scripts JavaScript ===================== -->
  <script>
    // Références DOM
    const drawer       = document.getElementById('drawer');
    const drawerButton = document.getElementById('drawerButton');

    // Ouvre/Ferme le drawer
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

    // ------- Variables pour horaires et compte à rebours -------
    const HEURE_OUVERTURE = 0;        // 00:00
    const HEURE_FERMETURE = 16.5;     // 13:30 exprimé en heures décimales (13 + 30/60 = 13.5)

    // Éléments DOM pour jour, message et compte à rebours
    const dayButtonsContainer = document.getElementById('day-buttons');
    const timeInfoDiv         = document.getElementById('time-info');
    const countdownDiv        = document.getElementById('countdown');

    const joursFrancais = ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"];
    const maintenant     = new Date();
    const heureActuelle  = maintenant.getHours() + (maintenant.getMinutes() / 60);

    // Fonction pour formater le temps restant en HH:MM:SS
    function formatTemps(ms) {
      const totalSecondes = Math.floor(ms / 1000);
      const heures   = Math.floor(totalSecondes / 3600);
      const minutes  = Math.floor((totalSecondes % 3600) / 60);
      const secondes = totalSecondes % 60;
      const pad = n => n.toString().padStart(2, '0');
      return `${pad(heures)}:${pad(minutes)}:${pad(secondes)}`;
    }
    const MAX_ENREGISTREMENTS = 500; // Valeur maximale autorisée

    // Vérifie si nous sommes dans la plage horaire autorisée
    if (heureActuelle >= HEURE_OUVERTURE && heureActuelle <= HEURE_FERMETURE) {
      // Affichage du bouton pour le jour actuel
      const jourIndex = maintenant.getDay(); // 0=Dimanche .. 6=Samedi
      const jourAffiche = joursFrancais[jourIndex];
      const btn = document.createElement('div');
      btn.classList.add('day-button', 'current');
      btn.textContent = jourAffiche;
      btn.dataset.dayIndex = jourIndex;
      btn.addEventListener('click', openRegistrationModal);
      dayButtonsContainer.appendChild(btn);

      // Initialisation du compte à rebours jusqu’à 13:30 aujourd’hui
      const finPeriode = new Date(
        maintenant.getFullYear(),
        maintenant.getMonth(),
        maintenant.getDate(),
        16, 30, 0
      ).getTime();

      function mettreAJourCompteARebours() {
        const maintenantMs = new Date().getTime();
        const diffMs = finPeriode - maintenantMs;
        if (diffMs <= 0) {
          // Plus de temps, désactiver bouton et afficher message
          btn.style.display = 'none';
          countdownDiv.textContent = "";
          timeInfoDiv.textContent = "Les heures d'enregistrement sont écoulées, revenez demain";
          clearInterval(intervalId);
        } else {
          // Afficher temps restant
          countdownDiv.textContent = "Temps restant pour s'enregistrer : " + formatTemps(diffMs);
        }
      }
      mettreAJourCompteARebours();
      const intervalId = setInterval(mettreAJourCompteARebours, 1000);
    } else {
      // Hors plage : afficher message d’erreur
      timeInfoDiv.textContent = "Les heures d'enregistrement sont écoulées, revenez demain";
      // Le bouton n’est pas créé
    }

    // ------- Références modal et formulaire -------
    const modal               = document.getElementById('registration-modal');
    const closeButton         = modal.querySelector('.close-button');
    const selectedDayNameSpan = document.getElementById('selected-day-name');
    const registrationForm    = document.getElementById('registration-form');
    const successMessage      = document.getElementById('success-message');
    const successNumberSpan   = document.getElementById('success-number');

    const registrationNumberDiv = document.getElementById('registration-number');
    const limitMessageDiv       = document.getElementById('limit-message');

    // Messages d'erreur sous chaque champ
    const errorNom     = document.getElementById('error-nom');
    const errorPostnom = document.getElementById('error-postnom');
    const errorPrenom  = document.getElementById('error-prenom');
    const errorEmail   = document.getElementById('error-email');

    // Ouvre le modal et récupère le numéro d’enregistrement
    function openRegistrationModal(event) {
      const idx = parseInt(event.currentTarget.dataset.dayIndex, 10);
      const nomJour = joursFrancais[idx];
      selectedDayNameSpan.textContent = nomJour;

      // Réinitialise l’affichage
      registrationForm.style.display = 'flex';
      successMessage.style.display = 'none';
      hideAllErrorMessages();
      hideLimitMessage();
      registrationNumberDiv.textContent = 'Chargement…';
      registrationNumberDiv.style.display = 'block';

      // Affiche le modal
      modal.classList.add('open');

      // 1) Appel AJAX à get_next_registration.php
      fetch('get_next_registration.php')
        .then(response => response.json())
        .then(data => {
          if (data.error === 'limit_reacheda' || data.error === 'limit_reached') {
            // Si limite atteinte, on masque le numéro et le formulaire, on affiche le message
            registrationNumberDiv.style.display = 'none';
            limitMessageDiv.style.display = 'block';
            registrationForm.querySelector('button.submit-button').disabled = true;
          } else {
            // Sinon, on affiche le numéro et on active le formulaire
            registrationNumberDiv.style.display = 'block';
            registrationNumberDiv.textContent = `Votre numéro d’enregistrement aujourd’hui : ${data.next}`;
            limitMessageDiv.style.display = 'none';
            registrationForm.querySelector('button.submit-button').disabled = false;
          }
        })
        .catch(err => {
          console.error('Erreur réseau', err);
          registrationNumberDiv.textContent = 'Erreur de récupération du numéro';
        });
    }

    // Ferme le modal
    closeButton.addEventListener('click', () => {
      modal.classList.remove('open');
    });
    modal.addEventListener('click', event => {
      if (event.target === modal) {
        modal.classList.remove('open');
      }
    });

    function hideAllErrorMessages() {
      errorNom.style.display     = 'none';
      errorPostnom.style.display = 'none';
      errorPrenom.style.display  = 'none';
      errorEmail.style.display   = 'none';
    }
    function hideLimitMessage() {
      limitMessageDiv.style.display = 'none';
    }

    // ------- Validation et soumission AJAX -------
    registrationForm.addEventListener('submit', function(event) {
      event.preventDefault();
      hideAllErrorMessages();

      const nomInput     = document.getElementById('nom').value.trim();
      const postnomInput = document.getElementById('postnom').value.trim();
      const prenomInput  = document.getElementById('prenom').value.trim();
      const emailInput   = document.getElementById('email').value.trim();
      const nameRegex    = /^[A-Za-zÀ-ÖØ-öø-ÿ']+$/;
      const emailRegex   = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      let valid = true;

      if (!nameRegex.test(nomInput)) {
        errorNom.style.display = 'block';
        valid = false;
      }
      if (!nameRegex.test(postnomInput)) {
        errorPostnom.style.display = 'block';
        valid = false;
      }
      if (!nameRegex.test(prenomInput)) {
        errorPrenom.style.display = 'block';
        valid = false;
      }
      if (!emailRegex.test(emailInput)) {
        errorEmail.style.display = 'block';
        valid = false;
      }
      if (!valid) return;

      // Préparation des données pour l’envoi
      const formData = new FormData();
      formData.append('nom', nomInput);
      formData.append('postnom', postnomInput);
      formData.append('prenom', prenomInput);
      formData.append('email', emailInput);

      // 2) Envoi AJAX en POST à submit_registration.php
      fetch('submit_registration.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.error === 'limit_reached') {
          registrationForm.style.display = 'none';
          limitMessageDiv.textContent = 'La limite de 500 enregistrements pour aujourd’hui est atteinte.';
          limitMessageDiv.style.display = 'block';
        }
        else if (data.error === 'invalid_nom' || data.error === 'invalid_postnom' || data.error === 'invalid_prenom' || data.error === 'invalid_email') {
          alert('Erreur dans les données saisies côté serveur. Vérifiez vos saisies.');
        }
        else if (data.success) {
          registrationForm.style.display = 'none';
          limitMessageDiv.style.display = 'none';
          successNumberSpan.textContent = data.number;
          successMessage.style.display = 'block';

          // On ajoute un délai de 2 s pour laisser le message s'afficher, puis on redirige en passant l'id en GET
          setTimeout(() => {
            window.location.href = `suivi.php?id=${data.id}`;
          }, 2000);
        }
      })
      .catch(err => {
        console.error('Erreur réseau', err);
        alert('Une erreur est survenue lors de l’enregistrement. Veuillez réessayer.');
      });
    });
  </script>
</body>
</html>
