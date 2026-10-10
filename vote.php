<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/includes/db.php';

$voteOuvert = voteEstOuvert();
$dejaVote = isset($_COOKIE['a_vote']);

$prestations = [];
if ($voteOuvert && !$dejaVote) {
    $stmt = getPDO()->query('SELECT id, nom FROM prestations ORDER BY ordre ASC, nom ASC');
    $prestations = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Votez pour vos prestations préférées</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<main class="page">
  <h1>Votez pour vos 3 prestations préférées</h1>

  <?php if (!$voteOuvert): ?>
    <div class="carte message">
      <p>Le vote est actuellement <strong>clos</strong>.</p>
      <p>Merci à toutes et à tous pour votre participation !</p>
    </div>

  <?php elseif ($dejaVote): ?>
    <div class="carte message succes">
      <p>Votre vote a bien été enregistré.</p>
      <p>Merci d'avoir participé !</p>
    </div>

  <?php elseif (count($prestations) < 3): ?>
    <div class="carte message">
      <p>Le vote n'est pas encore prêt (il faut au moins 3 prestations enregistrées).</p>
    </div>

  <?php else: ?>
    <div class="carte">
      <p class="consigne">Choisissez votre 1er, 2e et 3e choix parmi les prestations ci-dessous. Chaque choix doit être différent.</p>

      <form id="form-vote">
        <label for="choix1">1er choix</label>
        <select id="choix1" required>
          <option value="">Sélectionnez...</option>
          <?php foreach ($prestations as $p): ?>
            <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
          <?php endforeach; ?>
        </select>

        <label for="choix2">2e choix</label>
        <select id="choix2" required>
          <option value="">Sélectionnez...</option>
          <?php foreach ($prestations as $p): ?>
            <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
          <?php endforeach; ?>
        </select>

        <label for="choix3">3e choix</label>
        <select id="choix3" required>
          <option value="">Sélectionnez...</option>
          <?php foreach ($prestations as $p): ?>
            <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['nom']) ?></option>
          <?php endforeach; ?>
        </select>

        <p id="erreur" class="erreur" hidden></p>

        <button type="submit">Je valider mon vote</button>
      </form>
    </div>
  <?php endif; ?>
</main>

<script src="assets/script.js"></script>
</body>
</html>
