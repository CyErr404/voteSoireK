<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireAdmin();

$voteOuvert = voteEstOuvert();
$classement = [];

if (!$voteOuvert) {
    $sql = "
        SELECT
            p.id,
            p.nom,
            SUM(CASE WHEN v.choix1_id = p.id THEN 3
                     WHEN v.choix2_id = p.id THEN 2
                     WHEN v.choix3_id = p.id THEN 1
                     ELSE 0 END) AS points,
            SUM(CASE WHEN v.choix1_id = p.id THEN 1 ELSE 0 END) AS nb_1er,
            SUM(CASE WHEN v.choix2_id = p.id THEN 1 ELSE 0 END) AS nb_2e,
            SUM(CASE WHEN v.choix3_id = p.id THEN 1 ELSE 0 END) AS nb_3e
        FROM prestations p
        LEFT JOIN votes v ON p.id IN (v.choix1_id, v.choix2_id, v.choix3_id)
        GROUP BY p.id, p.nom
        ORDER BY points DESC, nb_1er DESC, nom ASC
    ";
    $classement = getPDO()->query($sql)->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Résultats</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head>
<body class="admin">
<?php include __DIR__ . '/../includes/nav_admin.php'; ?>
<main class="page-admin">
  <h1>Résultats</h1>

  <?php if ($voteOuvert): ?>
    <div class="carte">
      <p>Le vote est encore ouvert. Clôturez-le depuis le tableau de bord pour révéler le classement.</p>
    </div>
  <?php else: ?>
    <div class="carte">
      <table>
        <thead>
          <tr><th>Rang</th><th>Prestation</th><th>Points</th><th>1er</th><th>2e</th><th>3e</th></tr>
        </thead>
        <tbody>
          <?php foreach ($classement as $i => $p): ?>
            <tr class="<?= $i === 0 ? 'rang-1' : '' ?>">
              <td><?= $i + 1 ?></td>
              <td><?= htmlspecialchars($p['nom']) ?></td>
              <td><?= (int)$p['points'] ?></td>
              <td><?= (int)$p['nb_1er'] ?></td>
              <td><?= (int)$p['nb_2e'] ?></td>
              <td><?= (int)$p['nb_3e'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="ligne-actions">
        <a href="revelation.php" class="bouton"><i class="fa-solid fa-trophy"></i> Lancer la révélation</a>
        <a href="export_csv.php" class="bouton-secondaire" style="text-decoration:none;display:inline-block;">Exporter en CSV</a>
      </div>
    </div>
  <?php endif; ?>
</main>
</body>
</html>
