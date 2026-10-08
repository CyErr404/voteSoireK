<?php
require_once __DIR__ . '/../includes/auth.php';

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $saisi = $_POST['mot_de_passe'] ?? '';
    if (hash_equals(ADMIN_PASSWORD, $saisi)) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    }
    $erreur = 'Mot de passe incorrect.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Connexion admin</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<main class="page">
  <h1>Espace organisateurs</h1>
  <div class="carte">
    <form method="post">
      <label for="mot_de_passe">Mot de passe</label>
      <input type="password" id="mot_de_passe" name="mot_de_passe" required autofocus>
      <?php if ($erreur): ?><p class="erreur"><?= htmlspecialchars($erreur) ?></p><?php endif; ?>
      <button type="submit">Se connecter</button>
    </form>
  </div>
</main>
</body>
</html>
