<?php
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
<title>Soirée K - Je vote</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="page-vote">

<?php if (!$voteOuvert): ?>
  <main class="vote-etat">
    <i class="fa-solid fa-lock"></i>
    <h1>Le vote est clos</h1>
    <p>Merci à toutes et à tous pour votre participation !</p>
    <a href="index.php" class="btn-vote">Retour à l'accueil</a>
  </main>

<?php elseif ($dejaVote): ?>
  <main class="vote-etat">
    <i class="fa-solid fa-circle-check"></i>
    <h1>Vote déjà enregistré</h1>
    <p>Merci d'avoir participé à la Soirée K !</p>
    <a href="index.php" class="btn-vote">Retour à l'accueil</a>
  </main>

<?php elseif (count($prestations) < 3): ?>
  <main class="vote-etat">
    <i class="fa-solid fa-hourglass-half"></i>
    <h1>Bientôt prêt</h1>
    <p>Le vote n'est pas encore prêt. Reviens dans un instant !</p>
    <a href="index.php" class="btn-vote">Retour à l'accueil</a>
  </main>

<?php else: ?>
  <header class="vote-entete">
    <a href="index.php" class="vote-retour" aria-label="Accueil"><i class="fa-solid fa-chevron-left"></i></a>
    <h1>Choisis tes 3 préférées</h1>
    <p>Touche une prestation : la première est ton 1er choix, puis le 2e, puis le 3e. Touche-la encore pour la retirer.</p>
    <div class="vote-recherche">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="search" id="recherche" placeholder="Rechercher une prestation..." autocomplete="off">
    </div>
  </header>

  <main class="vote-liste" id="liste">
    <?php foreach ($prestations as $p): ?>
      <button type="button" class="prestation" data-id="<?= (int)$p['id'] ?>" data-nom="<?= htmlspecialchars($p['nom'], ENT_QUOTES) ?>">
        <span class="prestation-nom"><?= htmlspecialchars($p['nom']) ?></span>
        <span class="prestation-badge" aria-hidden="true"></span>
      </button>
    <?php endforeach; ?>
    <p id="aucun" class="vote-aucun" hidden>Aucune prestation trouvée.</p>
  </main>

  <footer class="vote-barre">
    <div class="vote-barre-int">
      <p id="message" class="vote-message" hidden></p>
      <ol class="vote-resume">
        <li><span class="rang">1</span><span class="resume-nom">—</span></li>
        <li><span class="rang">2</span><span class="resume-nom">—</span></li>
        <li><span class="rang">3</span><span class="resume-nom">—</span></li>
      </ol>
      <button type="button" id="btn-valider" class="btn-vote" disabled>
        <i class="fa-solid fa-check"></i> Valider mon vote
      </button>
    </div>
  </footer>

  <div id="modal" class="modal" hidden>
    <div class="modal-boite" role="dialog" aria-modal="true">
      <h2>Confirmer ton vote ?</h2>
      <ol class="modal-liste" id="modal-liste"></ol>
      <p class="modal-info">Tu ne pourras plus le modifier après.</p>
      <p id="modal-erreur" class="erreur" hidden></p>
      <div class="modal-actions">
        <button type="button" id="btn-modifier" class="btn-secondaire">Modifier</button>
        <button type="button" id="btn-confirmer" class="btn-vote">Confirmer</button>
      </div>
    </div>
  </div>

  <div id="merci" class="merci" hidden>
    <div class="merci-check"><i class="fa-solid fa-check"></i></div>
    <h2>Merci !</h2>
    <p>Ton vote est bien enregistré. À toi de profiter de la Soirée K <i class="fa-solid fa-heart merci-coeur"></i></p>
    <a href="index.php" class="btn-vote">Retour à l'accueil</a>
  </div>

  <div id="confettis"></div>
<?php endif; ?>

<?php if ($voteOuvert && !$dejaVote && count($prestations) >= 3): ?>
<script src="assets/script.js"></script>
<?php endif; ?>
</body>
</html>
