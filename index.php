<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/db.php';

$voteOuvert = voteEstOuvert();
require_once __DIR__ . '/includes/vote_utils.php';
$dejaVote = aDejaVote();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Soirée K - Vote</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body class="page-accueil">

<main class="accueil">
  <img src="assets/img/logo-soiree-k.png" alt="Soirée K" class="logo-neon">
  <p class="theme">Spécial Années 2016-2026</p>
  <p class="intro">À toi de choisir tes meilleures prestations...</p>
  <img src="assets/img/telephone.png" alt="" class="telephone">

  <?php if (!$voteOuvert): ?>
    <p class="etat"><i class="fa-solid fa-lock"></i> Le vote n'est pas ouvert pour le moment.</p>
  <?php elseif ($dejaVote): ?>
    <p class="etat"><i class="fa-solid fa-circle-check"></i> Ton vote est déjà enregistré. Merci !</p>
  <?php else: ?>
    <a href="vote.php" class="btn-vote"><i class="fa-solid fa-heart"></i> Je vote maintenant</a>
  <?php endif; ?>
</main>

</body>
</html>