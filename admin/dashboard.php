<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] === 'ouvrir') {
    setParametre('vote_ouvert', '1');
    setParametre('vote_cloture_le', '');
  } elseif ($_POST['action'] === 'cloturer') {
    setParametre('vote_ouvert', '0');
    setParametre('vote_cloture_le', date('Y-m-d H:i:s'));
  }
  header('Location: dashboard.php');
  exit;
}

$voteOuvert = voteEstOuvert();
$nbVotes = (int) getPDO()->query('SELECT COUNT(*) AS n FROM votes')->fetch()['n'];
$nbPrestations = (int) getPDO()->query('SELECT COUNT(*) AS n FROM prestations')->fetch()['n'];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tableau de bord</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/style.css">
</head>

<body class="admin">
  <?php include __DIR__ . '/../includes/nav_admin.php'; ?>
  <main class="page-admin">
    <h1>Tableau de bord</h1>

    <div class="carte">
      <p>
        Statut du vote :
        <span class="pastille <?= $voteOuvert ? 'ouvert' : 'ferme' ?>">
          <?= $voteOuvert ? 'Ouvert' : 'Clos' ?>
        </span>
      </p>

      <div class="stat-grand"><?= $nbVotes ?></div>
      <div class="stat-label">vote(s) enregistré(s) — classement masqué jusqu'à la clôture</div>

      <p class="lien-discret" style="margin-top:16px;"><?= $nbPrestations ?> prestation(s) enregistrée(s).</p>

      <div class="ligne-actions">
        <?php if ($voteOuvert): ?>
          <form method="post" style="display:inline;" onsubmit="return confirm('Clôturer le vote maintenant ? Cette action révélera les résultats.');">
            <input type="hidden" name="action" value="cloturer">
            <button type="submit" class="bouton-secondaire">Clôturer le vote</button>
          </form>
        <?php else: ?>
          <form method="post" style="display:inline;">
            <input type="hidden" name="action" value="ouvrir">
            <button type="submit" class="bouton-secondaire">Rouvrir le vote</button>
          </form>
        <?php endif; ?>
        <?php if (!$voteOuvert && $nbVotes > 0): ?>
          <a href="revelation.php" class="bouton"><i class="fa-solid fa-trophy"></i> Lancer la révélation</a>
        <?php endif; ?>
      </div>
    </div>
  </main>
</body>

</html>