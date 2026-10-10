<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireAdmin();

header('Content-Type: text/html; charset=utf-8');
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'ajouter') {
        $nom = trim($_POST['nom'] ?? '');
        $ordre = (int) ($_POST['ordre'] ?? 0);
        if ($nom === '') {
            $erreur = 'Le nom ne peut pas être vide.';
        } else {
            $stmt = getPDO()->prepare('INSERT INTO prestations (nom, ordre) VALUES (?, ?)');
            $stmt->execute([$nom, $ordre]);
        }
    } elseif ($action === 'modifier') {
        $id = (int) ($_POST['id'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');
        $ordre = (int) ($_POST['ordre'] ?? 0);
        if ($id && $nom !== '') {
            $stmt = getPDO()->prepare('UPDATE prestations SET nom = ?, ordre = ? WHERE id = ?');
            $stmt->execute([$nom, $ordre, $id]);
        }
        if (!empty($_POST['ajax'])) { echo 'ok'; exit; }
    } elseif ($action === 'supprimer') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = getPDO()->prepare(
            'SELECT COUNT(*) AS n FROM votes WHERE choix1_id = ? OR choix2_id = ? OR choix3_id = ?'
        );
        $stmt->execute([$id, $id, $id]);
        if ((int) $stmt->fetch()['n'] > 0) {
            $erreur = 'Impossible de supprimer : des votes existent déjà pour cette prestation.';
            if (!empty($_POST['ajax'])) { http_response_code(409); echo $erreur; exit; }
        } else {
            $stmt = getPDO()->prepare('DELETE FROM prestations WHERE id = ?');
            $stmt->execute([$id]);
            if (!empty($_POST['ajax'])) { echo 'ok'; exit; }
        }
    }
}

$prestations = getPDO()->query('SELECT id, nom, ordre FROM prestations ORDER BY ordre ASC, nom ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gestion des prestations</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/style.css">
</head>
<body class="admin">
<?php include __DIR__ . '/../includes/nav_admin.php'; ?>
<main class="page-admin">
  <h1>Prestations</h1>

  <?php if ($erreur): ?><p class="erreur"><?= htmlspecialchars($erreur) ?></p><?php endif; ?>

  <div class="carte">
    <p class="consigne">Ajoutez ici toutes les prestations qui seront proposées au vote. L'ordre détermine leur affichage sur la page de vote.</p>
    <form method="post">
      <input type="hidden" name="action" value="ajouter">
      <div class="champ-inline">
        <input type="text" name="nom" placeholder="Nom de la prestation" required>
        <input type="number" name="ordre" placeholder="Ordre" value="<?= count($prestations) + 1 ?>">
      </div>
      <button type="submit">Ajouter</button>
    </form>
  </div>

  <div class="carte" style="margin-top:20px;">
    <table id="tableau-prestations">
      <thead>
        <tr><th>Ordre</th><th>Nom</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($prestations as $p): ?>
          <tr data-id="<?= (int)$p['id'] ?>">
            <td><input type="number" class="inp-ordre" value="<?= (int)$p['ordre'] ?>" style="width:60px;"></td>
            <td><input type="text" class="inp-nom" value="<?= htmlspecialchars($p['nom']) ?>"></td>
            <td style="white-space:nowrap;">
              <button type="button" class="bouton-secondaire" onclick="enregistrerLigne(this)">Enregistrer</button>
              <button type="button" class="bouton-secondaire" onclick="supprimerLigne(this)">Supprimer</button>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$prestations): ?>
          <tr><td colspan="3">Aucune prestation enregistrée pour l'instant.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

<script>
function ligneDeBouton(bouton) {
  return bouton.closest('tr');
}

function enregistrerLigne(bouton) {
  var ligne = ligneDeBouton(bouton);
  var id = ligne.getAttribute('data-id');
  var nom = ligne.querySelector('.inp-nom').value;
  var ordre = ligne.querySelector('.inp-ordre').value;

  fetch('prestations.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=modifier&ajax=1&id=' + encodeURIComponent(id) +
          '&nom=' + encodeURIComponent(nom) + '&ordre=' + encodeURIComponent(ordre)
  }).then(function () { location.reload(); });
}

function supprimerLigne(bouton) {
  if (!confirm('Supprimer cette prestation ?')) return;
  var ligne = ligneDeBouton(bouton);
  var id = ligne.getAttribute('data-id');

  fetch('prestations.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=supprimer&ajax=1&id=' + encodeURIComponent(id)
  }).then(function (r) {
    if (!r.ok) { return r.text().then(function (t) { alert(t); }); }
    location.reload();
  });
}
</script>
</body>
</html>
