<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireAdmin();

header('Content-Type: text/html; charset=utf-8');

// Remet les numéros d'ordre à la suite : 1, 2, 3, ...
// $idPrio : prestation qui vient d'être ajoutée ou déplacée (elle passe en premier en cas d'égalité)
// $apres : true si elle doit passer après les autres de même numéro (déplacement vers le bas)
function renumeroter(PDO $pdo, int $idPrio = 0, bool $apres = false): void
{
    $sens = $apres ? 'ASC' : 'DESC';
    $stmt = $pdo->prepare("SELECT id FROM prestations ORDER BY ordre ASC, (id = ?) $sens, id ASC");
    $stmt->execute([$idPrio]);
    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $pdo->beginTransaction();
    $maj = $pdo->prepare('UPDATE prestations SET ordre = ? WHERE id = ?');
    foreach ($ids as $i => $id) {
        $maj->execute([$i + 1, $id]);
    }
    $pdo->commit();
}

$pdo = getPDO();

// Message mémorisé avant la redirection
$erreur = $_SESSION['flash_erreur'] ?? '';
unset($_SESSION['flash_erreur']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $ajax = !empty($_POST['ajax']);
    $message = '';

    if ($action === 'ajouter') {
        $nom = trim($_POST['nom'] ?? '');
        $ordre = (int) ($_POST['ordre'] ?? 0);
        if ($nom === '') {
            $message = 'Le nom ne peut pas être vide.';
        } else {
            $stmt = $pdo->prepare('SELECT COUNT(*) AS n FROM prestations WHERE nom = ?');
            $stmt->execute([$nom]);
            if ((int) $stmt->fetch()['n'] > 0) {
                $message = 'Cette prestation existe déjà.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO prestations (nom, ordre) VALUES (?, ?)');
                $stmt->execute([$nom, $ordre]);
                renumeroter($pdo, (int) $pdo->lastInsertId());
            }
        }

    } elseif ($action === 'modifier') {
        $id = (int) ($_POST['id'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');
        $ordre = (int) ($_POST['ordre'] ?? 0);
        if ($id && $nom !== '') {
            $stmt = $pdo->prepare('SELECT ordre FROM prestations WHERE id = ?');
            $stmt->execute([$id]);
            $ancien = $stmt->fetch();
            if ($ancien) {
                $stmt = $pdo->prepare('UPDATE prestations SET nom = ?, ordre = ? WHERE id = ?');
                $stmt->execute([$nom, $ordre, $id]);
                renumeroter($pdo, $id, $ordre > (int) $ancien['ordre']);
            }
        } else {
            $message = 'Le nom ne peut pas être vide.';
        }

    } elseif ($action === 'supprimer') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) AS n FROM votes WHERE choix1_id = ? OR choix2_id = ? OR choix3_id = ?'
        );
        $stmt->execute([$id, $id, $id]);
        if ((int) $stmt->fetch()['n'] > 0) {
            $message = 'Impossible de supprimer : des votes existent déjà pour cette prestation.';
        } else {
            $stmt = $pdo->prepare('DELETE FROM prestations WHERE id = ?');
            $stmt->execute([$id]);
            renumeroter($pdo);
        }
    }

    // Réponse aux appels du tableau (Enregistrer / Supprimer)
    if ($ajax) {
        if ($message !== '') {
            http_response_code(409);
            echo $message;
        } else {
            echo 'ok';
        }
        exit;
    }

    // Réponse au formulaire d'ajout : on redirige, donc F5 ne renvoie plus rien
    if ($message !== '') {
        $_SESSION['flash_erreur'] = $message;
    }
    header('Location: prestations.php');
    exit;
}

$prestations = $pdo->query('SELECT id, nom, ordre FROM prestations ORDER BY ordre ASC, nom ASC')->fetchAll();
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
    <p class="consigne">Ajoutez ici toutes les prestations qui seront proposées au vote. L'ordre détermine leur affichage sur la page de vote, et les numéros se réarrangent tout seuls.</p>
    <form method="post" onsubmit="this.querySelector('button[type=submit]').disabled = true;">
      <input type="hidden" name="action" value="ajouter">
      <div class="champ-inline">
        <input type="text" name="nom" placeholder="Nom de la prestation" required>
        <input type="number" name="ordre" placeholder="Ordre" value="<?= count($prestations) + 1 ?>">
      </div>
      <button type="submit"><i class="fa-solid fa-plus"></i> Ajouter</button>
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
            <td><input type="text" class="inp-nom" value="<?= htmlspecialchars($p['nom'], ENT_QUOTES) ?>"></td>
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
  }).then(function (r) {
    if (!r.ok) { return r.text().then(function (t) { alert(t); }); }
    window.location.href = 'prestations.php';
  });
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
    window.location.href = 'prestations.php';
  });
}
</script>
</body>
</html>