<?php
require_once __DIR__ . '/../includes/auth.php';

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $saisi = $_POST['mot_de_passe'] ?? '';
    if (hash_equals(ADMIN_PASSWORD, $saisi)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    }
    usleep(700000);
    $erreur = 'Mot de passe incorrect.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connexion admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head>
<body class="admin page-login">
<main class="page">
  <img src="../assets/img/logo-soiree-k.png" alt="Soirée K" class="login-logo">
  <h1>Espace organisateurs</h1>
  <div class="carte">
    <form method="post">
      <label for="mot_de_passe">Mot de passe</label>
      <input type="password" id="mot_de_passe" name="mot_de_passe" required autofocus>
      <?php if ($erreur): ?><p class="erreur"><?= htmlspecialchars($erreur) ?></p><?php endif; ?>
      <button type="submit"><i class="fa-solid fa-lock-open"></i> Se connecter</button>
    </form>
  </div>
</main>
</body>
</html>