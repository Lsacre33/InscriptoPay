<?php
// dashboard_login.php and home
session_start();

if (isset($_SESSION['dashboard_logged']) && $_SESSION['dashboard_logged'] === true) {
    header('Location: dashboard.php');
    exit;
}
if (isset($_SEESION['home_logged']) && $_SESSION['home_logged'] === true) {
  header('location: home.php');
}

$errorMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codeChars = '';
    for ($i = 1; $i <= 6; $i++) {
        $field = 'c' . $i;
        if (isset($_POST[$field])) {
            $codeChars .= strtoupper(substr($_POST[$field], 0, 1));
        }
    }
    if ($codeChars === 'USER') {
        $_SESSION['home_logged'] = true;
        header('Location: home1.php');
        exit;
    } else {
        $errorMsg = 'Code invalide, veuillez réessayer.';
    }

    if ($codeChars === 'ADMI') {
        $_SESSION['dashboard_logged'] = true;
        header('Location: home2.php');
        exit;
    } else {
        $errorMsg = 'Code invalide, veuillez réessayer.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion Dashboard – InscriptoPay</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #F4F4F4;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }
    .container {
      background: #FFFFFF;
      padding: 24px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
      text-align: center;
      width: 320px;
    }
    .robot-img {
      width: 120px;
      height: auto;
      margin-bottom: 16px;
    }
    .code-inputs {
      display: flex;
      justify-content: space-between;
      margin-bottom: 16px;
    }
    .code-inputs input {
      width: 40px;
      height: 40px;
      font-size: 24px;
      text-align: center;
      border: 2px solid #005B9E;
      border-radius: 4px;
    }
    .code-inputs input:focus {
      outline: none;
      border-color: #002147;
    }
    .btn-connexion {
      background-color: #005B9E;
      color: #FFFFFF;
      padding: 10px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
    }
    .error-msg {
      color: #E53935;
      margin-bottom: 12px;
      font-size: 14px;
    }
  </style>
</head>
<body>

  <div class="container">
    <img src="img/tete.png" alt="Robot" class="robot-img">

    <h2>Connexion-vous</h2>
    <p>Utilisez le code passe étudiant "USER"</p>
    <?php if ($errorMsg): ?>
      <div class="error-msg"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <form method="POST" id="loginForm">
      <div class="code-inputs">
        <input type="text" id="c1" name="c1" maxlength="1" autocomplete="off" autofocus>
        <input type="text" id="c2" name="c2" maxlength="1" autocomplete="off">
        <input type="text" id="c3" name="c3" maxlength="1" autocomplete="off">
        <input type="text" id="c4" name="c4" maxlength="1" autocomplete="off">
      </div>
      <button type="submit" class="btn-connexion">🔑 Connexion</button>
    </form>
  </div>

  <script>
    const inputs = Array.from(document.querySelectorAll('.code-inputs input'));
    inputs.forEach((input, idx) => {
      input.addEventListener('input', function(e) {
        const value = e.target.value;
        if (value.length === 1 && idx < inputs.length - 1) {
          inputs[idx + 1].focus();
        }
      });
      input.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft' && idx > 0) {
          inputs[idx - 1].focus();
        } else if (e.key === 'ArrowRight' && idx < inputs.length - 1) {
          inputs[idx + 1].focus();
        }
      });
    });
  </script>

</body>
</html>
