<?php
// home1.php
// (Ce fichier ne contient pas de logique PHP particulière pour l'instant.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Accueil – Splash Screen</title>

  <!--
    1. Couleur de fond identique à celle qui entoure le logo sur votre image (teinte crème).
       Ici on utilise #F8F4EE (à ajuster si nécessaire pour coller exactement).
    2. On centre l’image en plein écran, on supprime marges et paddings par défaut.
    3. Un keyframe CSS pour faire apparaître/disparaître progressivement le logo ("diaporama").
    4. Le script JavaScript redirige vers index.php après 5 secondes (5000 ms).
  -->
  <style>
    html, body {
      margin: 0;
      padding: 0;
      width: 100%;
      height: 100%;
      background-color:white;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    /* Image du logo centrée */
    .splash-logo {
      max-width: 60%;
      max-height: 60%;
      /* Animation de fade-in puis fade-out */
      animation: fadeInOut 5s ease-in-out forwards;
    }

    @keyframes fadeInOut {
      0%   { opacity: 0; }
      10%  { opacity: 1; }
      90%  { opacity: 1; }
      100% { opacity: 0; }
    }
  </style>

  <script>
    // Après 5 secondes, on redirige vers index.php (ou la page de votre choix)
    setTimeout(function() {
      window.location.href = "accueil.php";
    }, 3500);
  </script>
</head>
<body>
  <!-- 
    Affichez ici votre logo.
    Si votre fichier se nomme différemment ou se trouve dans un sous-dossier,
    pensez à ajuster le chemin (ex. "assets/img/logo.png" ou "images/logo-inscripto.png").
  -->
  <img src="img/robot.png" class="splash-logo" alt="Logo InscripttoPay">
</body>
</html>
