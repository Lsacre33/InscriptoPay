<?php
// dashboard.php
session_start();

// 1) Vérifier l'accès au Dashboard
if (!isset($_SESSION['dashboard_logged']) || $_SESSION['dashboard_logged'] !== true) {
    header('Location: home.php');
    exit;
}

require_once __DIR__ . '/db_config.php';

$today = date('Y-m-d');

try {
    // 2) Récupérer TOUS les enregistrements du jour avec archived = 0
    $stmtAll = $pdo->prepare('
        SELECT id, nom, postnom, prenom, email, `number`, `timestamp`, `notified`
        FROM registrations
        WHERE `date` = :date AND archived = 0
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
  <title>Dashboard – InscriptoPay</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- ===================== Bootstrap (CSS + JS) ===================== -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-ENjdO4Dr2bkBIFxQpeoAMGGMbQkmLks1qVX1rFQoi0GGM/KzF0RJ7O2Kc6eSm0g"
    crossorigin="anonymous"
  >
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+emF3zB1jzI6jIQxR2T6HIZ8sW+Fs"
    crossorigin="anonymous"
  ></script>

  <!-- ===================== Styles CSS personnalisés ===================== -->
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
    .drawer li:not(.active):hover {
      background-color: rgba(0, 91, 158, 0.08);
    }
    .drawer li.active {
      background-color: var(--primary-blue);
      color: var(--white);
      border-radius: 4px;
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
    .info-msg {
      font-size: 16px;
      color: var(--text-muted);
      text-align: center;
      margin-bottom: 24px;
      line-height: 1.4;
    }
    .dashboard-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 16px;
    }
    .dashboard-table th, .dashboard-table td {
      padding: 12px 8px;
      border-bottom: 1px solid #EFEFEF;
      text-align: left;
      font-size: 14px;
    }
    .dashboard-table th {
      background-color: var(--primary-blue);
      color: var(--white);
      font-weight: 600;
    }
    .dashboard-table tr:hover {
      background-color: rgba(0, 0, 0, 0.04);
    }
    .dashboard-table tr.notified td {
      background-color: var(--highlight-green-bg);
    }
    .delete-btn {
      color: var(--accent-red);
      font-weight: bold;
      cursor: pointer;
    }

    @media (max-width: 768px) {
      .page-title {
        font-size: 24px;
      }
      .info-msg {
        font-size: 14px;
      }
      .dashboard-table th, .dashboard-table td {
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
      <li class="active" onclick="navigateTo('dashboard.php')">
        📋 Dashboard
      </li>
      <li onclick="logoutDashboard()">
        🔓 Déconnexion
      </li>
    </ul>
  </div>

  <!-- Bouton tiroir en bas à droite -->
  <div id="drawerButton" class="drawer-button" aria-label="Ouvrir le menu" role="button">📂</div>

  <!-- ===================== CONTENU PRINCIPAL ===================== -->
  <main>
    <h1 class="page-title">Dashboard – Enregistrements du <?= htmlspecialchars($today) ?></h1>
    <p class="info-msg">Cochez une case pour notifier un étudiant (une seule case cochée possible),<br>
       ou cliquez sur ✖ pour supprimer (suppression logique).</p>

    <table class="dashboard-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Notifier</th>
          <th>Nom</th>
          <th>Post-nom</th>
          <th>Prénom</th>
          <th>Email</th>
          <th>Heure</th>
          <th>Supprimer</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($allRows as $row): 
            $rowClass = $row['notified'] == 1 ? 'notified' : '';
        ?>
          <tr id="row-<?= $row['id'] ?>" class="<?= $rowClass ?>">
            <td><?= htmlspecialchars($row['number']) ?></td>
            <td class="text-center">
              <input 
                type="checkbox" 
                class="notify-checkbox" 
                data-id="<?= $row['id'] ?>"
                <?= $row['notified'] == 1 ? 'checked disabled' : '' ?>
              >
            </td>
            <td><?= htmlspecialchars($row['nom']) ?></td>
            <td><?= htmlspecialchars($row['postnom']) ?></td>
            <td><?= htmlspecialchars($row['prenom']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars(date('H:i:s', strtotime($row['timestamp']))) ?></td>
            <td class="text-center">
              <span class="delete-btn" data-id="<?= $row['id'] ?>">✖</span>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>

  <!-- ===================== MODALES BOOTSTRAP ===================== -->

  <!-- 1) Modale de confirmation "Notifier" -->
  <div class="modal fade" id="modalNotify" tabindex="-1" aria-labelledby="modalNotifyLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalNotifyLabel">Confirmation de notification</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          Êtes-vous sûr de notifier <strong><span id="notifyStudentName"></span></strong> ?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btnNotifyCancel">Non</button>
          <button type="button" class="btn btn-primary" id="btnNotifyConfirm">Oui, notifier</button>
        </div>
      </div>
    </div>
  </div>

  <!-- 2) Modale de confirmation "Supprimer" -->
  <div class="modal fade" id="modalDelete" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalDeleteLabel">Confirmation de suppression</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body">
          Voulez-vous vraiment supprimer <strong><span id="deleteStudentName"></span></strong> ?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btnDeleteCancel">Non</button>
          <button type="button" class="btn btn-danger" id="btnDeleteConfirm">Oui, supprimer</button>
        </div>
      </div>
    </div>
  </div>


  <!-- ===================== SCRIPTS ===================== -->
  <script>
    // Gérer l'ouverture/fermeture du drawer
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
    function logoutDashboard() {
      window.location.href = 'dashboard_logout.php';
    }

    // ================ COMPORTEMENT "CASE UNIQUE COCHÉE" ================
    // On récupère toutes les checkboxes
    const checkboxes = document.querySelectorAll('.notify-checkbox');

    // À chaque changement, on décoche les autres et on ouvre la modale
    checkboxes.forEach(cb => {
      cb.addEventListener('change', function(e) {
        const clickedId = parseInt(this.getAttribute('data-id'));
        const clickedRow = document.getElementById('row-' + clickedId);
        const clickedName = clickedRow.querySelectorAll('td')[2].textContent + ' '
                            + clickedRow.querySelectorAll('td')[3].textContent + ' '
                            + clickedRow.querySelectorAll('td')[4].textContent;

        // Si on vient de cocher
        if (this.checked) {
          // 1) Décoche immédiatement toutes les autres
          checkboxes.forEach(otherCb => {
            if (parseInt(otherCb.getAttribute('data-id')) !== clickedId) {
              otherCb.checked = false;
              otherCb.disabled = false;
              document.getElementById('row-' + otherCb.getAttribute('data-id')).classList.remove('notified');
            }
          });

          // 2) Afficher la modale de notification en y injectant le nom
          document.getElementById('notifyStudentName').textContent = clickedName;
          // On stocke l'ID pour le confirmer ensuite
          window._idToNotify = clickedId;
          // On affiche la modale
          const notifyModal = new bootstrap.Modal(document.getElementById('modalNotify'));
          notifyModal.show();
        } else {
          // Si on décoche manuellement, on ne fait rien (juste empêcher deux cochés)
        }
      });
    });

    // Quand on confirme la notification DANS la modale
    document.getElementById('btnNotifyConfirm').addEventListener('click', function() {
      const id = window._idToNotify;
      // Envoi AJAX pour notifier (qui remet à zéro les autres)
      fetch('notify_student.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // On désactive toutes les cases, puis on coche celle-ci et on grise la ligne
          checkboxes.forEach(cb => {
            cb.checked = false;
            cb.disabled = false;
            document.getElementById('row-' + cb.getAttribute('data-id')).classList.remove('notified');
          });
          const cbThis = document.querySelector('.notify-checkbox[data-id="' + id + '"]');
          cbThis.checked = true;
          cbThis.disabled = true;
          document.getElementById('row-' + id).classList.add('notified');
        } else {
          alert('Erreur lors de la notification.');
        }
      })
      .catch(() => {
        alert('Erreur réseau lors de la notification.');
      })
      .finally(() => {
        // On ferme la modale
        const notifyModalElement = document.getElementById('modalNotify');
        const notifyModal = bootstrap.Modal.getInstance(notifyModalElement);
        notifyModal.hide();
      });
    });

    // Quand on annule dans la modale de notification
    document.getElementById('btnNotifyCancel').addEventListener('click', function() {
      // On décoche la case qui était cochée
      const id = window._idToNotify;
      const cbThis = document.querySelector('.notify-checkbox[data-id="' + id + '"]');
      cbThis.checked = false;
    });


    // ================ SUPPRESSION LOGIQUE ================
    // Au clic sur la croix, on affiche la modale de suppression
    const deleteButtons = document.querySelectorAll('.delete-btn');
    deleteButtons.forEach(btn => {
      btn.addEventListener('click', function() {
        const id = parseInt(this.getAttribute('data-id'));
        const row = document.getElementById('row-' + id);
        const studentName = row.querySelectorAll('td')[2].textContent + ' '
                            + row.querySelectorAll('td')[3].textContent + ' '
                            + row.querySelectorAll('td')[4].textContent;
        document.getElementById('deleteStudentName').textContent = studentName;
        window._idToDelete = id;
        const deleteModal = new bootstrap.Modal(document.getElementById('modalDelete'));
        deleteModal.show();
      });
    });

    // Quand on confirme la suppression DANS la modale
    document.getElementById('btnDeleteConfirm').addEventListener('click', function() {
      const id = window._idToDelete;
      fetch('delete_student.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // On supprime la ligne du Dashboard (mais Suivi gardera l'historique)
          const row = document.getElementById('row-' + id);
          row.parentNode.removeChild(row);
        } else {
          alert('Impossible de supprimer cet étudiant.');
        }
      })
      .catch(() => {
        alert('Erreur réseau lors de la suppression.');
      })
      .finally(() => {
        const deleteModalElement = document.getElementById('modalDelete');
        const deleteModal = bootstrap.Modal.getInstance(deleteModalElement);
        deleteModal.hide();
      });
    });

    // Quand on annule dans la modale de suppression
    document.getElementById('btnDeleteCancel').addEventListener('click', function() {
      // Rien à faire, la modale se ferme automatiquement
    });
  </script>

</body>
</html>
