<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireAdmin();

$voteOuvert = voteEstOuvert();
$classement = [];
$nbVotants = 0;

if (!$voteOuvert) {
    $sql = "
        SELECT
            p.id,
            p.nom,
            SUM(CASE WHEN v.choix1_id = p.id THEN 3
                     WHEN v.choix2_id = p.id THEN 2
                     WHEN v.choix3_id = p.id THEN 1
                     ELSE 0 END) AS points,
            SUM(CASE WHEN v.choix1_id = p.id THEN 1 ELSE 0 END) AS nb_1er
        FROM prestations p
        LEFT JOIN votes v ON p.id IN (v.choix1_id, v.choix2_id, v.choix3_id)
        GROUP BY p.id, p.nom
        ORDER BY points DESC, nb_1er DESC, nom ASC
    ";
    $classement = getPDO()->query($sql)->fetchAll();
    $nbVotants = (int) getPDO()->query('SELECT COUNT(*) AS n FROM votes')->fetch()['n'];
}

$top = array_slice($classement, 0, 3);
$donnees = [];
foreach ($top as $p) {
    $donnees[] = ['nom' => $p['nom'], 'points' => (int) $p['points']];
}

// Égalités parfaites (mêmes points et même nombre de 1ers choix) autour du podium
$egalites = [];
for ($i = 0; $i < min(3, count($classement) - 1); $i++) {
    if ((int)$classement[$i]['points'] === (int)$classement[$i + 1]['points']
        && (int)$classement[$i]['nb_1er'] === (int)$classement[$i + 1]['nb_1er']) {
        $egalites[] = $classement[$i]['nom'] . ' / ' . $classement[$i + 1]['nom'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Révélation - Soirée K</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head>
<body class="revelation">

<?php if ($voteOuvert): ?>
  <main class="rev-centre">
    <i class="fa-solid fa-lock"></i>
    <h1>Le vote est encore ouvert</h1>
    <p>Clôturez-le depuis le tableau de bord pour révéler les résultats.</p>
    <a href="dashboard.php" class="rev-btn">Tableau de bord</a>
  </main>

<?php elseif ($nbVotants === 0 || count($donnees) === 0): ?>
  <main class="rev-centre">
    <i class="fa-solid fa-inbox"></i>
    <h1>Aucun vote enregistré</h1>
    <a href="dashboard.php" class="rev-btn">Tableau de bord</a>
  </main>

<?php else: ?>
  <main class="rev-centre" id="regie">
    <i class="fa-solid fa-trophy"></i>
    <h1>Révélation des résultats</h1>
    <p><?= $nbVotants ?> vote<?= $nbVotants > 1 ? 's' : '' ?> comptabilisé<?= $nbVotants > 1 ? 's' : '' ?>.</p>

    <?php if ($egalites): ?>
      <p class="rev-alerte">
        <i class="fa-solid fa-triangle-exclamation"></i>
        Égalité parfaite (mêmes points et même nombre de 1ers choix) entre :
        <strong><?= htmlspecialchars(implode(' — ', $egalites)) ?></strong>.
        À l'écran, elles sont classées par ordre alphabétique : décidez avant de révéler.
      </p>
    <?php endif; ?>

    <button type="button" id="btn-lancer" class="rev-btn"><i class="fa-solid fa-play"></i> Lancer la révélation</button>
    <button type="button" id="btn-plein-ecran" class="rev-btn discret"><i class="fa-solid fa-expand"></i> Plein écran</button>
    <a href="resultats.php" class="rev-lien">Retour aux résultats</a>
  </main>

  <section id="scene" class="scene" hidden>
    <h2 class="scene-titre">Le podium de la Soirée K</h2>

    <div class="slots">
      <?php foreach ($donnees as $i => $d): ?>
        <div class="slot">
          <div class="slot-rang"><?= $i + 1 ?></div>
          <div class="slot-corps">
            <span class="slot-nom">?</span>
            <span class="slot-pts"></span>
          </div>
          <i class="slot-icone fa-solid <?= $i === 0 ? 'fa-trophy' : 'fa-medal' ?>"></i>
        </div>
      <?php endforeach; ?>
    </div>

    <p id="indice" class="indice"></p>

    <div id="fin" class="scene-fin" hidden>
      <button type="button" id="btn-rejouer" class="rev-btn discret"><i class="fa-solid fa-rotate-left"></i> Rejouer</button>
      <a href="resultats.php" class="rev-lien">Retour aux résultats</a>
    </div>
  </section>

  <div id="confettis"></div>

  <script>
  (function () {
    var donnees = <?= json_encode($donnees, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
    var regie = document.getElementById('regie');
    var scene = document.getElementById('scene');
    var slots = Array.prototype.slice.call(document.querySelectorAll('.slot'));
    var indice = document.getElementById('indice');
    var fin = document.getElementById('fin');
    var confettis = document.getElementById('confettis');

    var ordre = [];
    for (var i = donnees.length - 1; i >= 0; i--) ordre.push(i);
    var etape = 0;
    var occupe = false;

    function majIndice() {
      indice.classList.remove('suspense');
      if (etape < ordre.length) {
        indice.textContent = 'Espace, → ou toucher l\u2019écran : place n°' + (ordre[etape] + 1);
      }
    }

    function lancerConfettis() {
      var couleurs = ['#ff2d95', '#19a9ff', '#ffffff', '#ffd36b', '#ffb3d9'];
      for (var k = 0; k < 140; k++) {
        var p = document.createElement('i');
        p.className = 'confetti';
        p.style.left = (Math.random() * 100) + 'vw';
        p.style.background = couleurs[Math.floor(Math.random() * couleurs.length)];
        p.style.animationDuration = (3 + Math.random() * 3) + 's';
        p.style.animationDelay = (Math.random() * 1.5) + 's';
        confettis.appendChild(p);
      }
      setTimeout(function () { confettis.innerHTML = ''; }, 9000);
    }

    function reveler(i) {
      var s = slots[i];
      s.querySelector('.slot-nom').textContent = donnees[i].nom;
      s.querySelector('.slot-pts').textContent =
        donnees[i].points + ' point' + (donnees[i].points > 1 ? 's' : '');
      s.classList.add('revele');
      if (i === 0) {
        s.classList.add('premier');
        lancerConfettis();
      }
    }

    function terminer() {
      indice.classList.remove('suspense');
      indice.textContent = 'Bravo à toutes les prestations !';
      fin.hidden = false;
    }

    function suivant() {
      if (occupe || etape >= ordre.length) return;
      var i = ordre[etape];
      etape++;

      if (i === 0) {
        occupe = true;
        indice.textContent = 'Et la prestation n°1 est\u2026';
        indice.classList.add('suspense');
        setTimeout(function () {
          reveler(0);
          occupe = false;
          terminer();
        }, 2600);
      } else {
        reveler(i);
        if (etape < ordre.length) { majIndice(); } else { terminer(); }
      }
    }

    document.getElementById('btn-lancer').addEventListener('click', function () {
      regie.hidden = true;
      scene.hidden = false;
      majIndice();
    });

    document.getElementById('btn-plein-ecran').addEventListener('click', function () {
      var el = document.documentElement;
      if (el.requestFullscreen) el.requestFullscreen();
    });

    document.getElementById('btn-rejouer').addEventListener('click', function () {
      location.reload();
    });

    scene.addEventListener('click', function (e) {
      if (e.target.closest('a, button')) return;
      suivant();
    });

    document.addEventListener('keydown', function (e) {
      if (scene.hidden) return;
      if (e.key === ' ' || e.key === 'Enter' || e.key === 'ArrowRight') {
        e.preventDefault();
        suivant();
      }
    });
  })();
  </script>
<?php endif; ?>

</body>
</html>