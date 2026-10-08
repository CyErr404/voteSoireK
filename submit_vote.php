<?php
require_once __DIR__ . '/includes/db.php';

header('Content-Type: application/json; charset=utf-8');

function repondre(bool $succes, string $message = ''): void
{
    echo json_encode(['succes' => $succes, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    repondre(false, 'Méthode non autorisée.');
}

if (!voteEstOuvert()) {
    repondre(false, 'Le vote est clos.');
}

if (isset($_COOKIE['a_vote'])) {
    repondre(false, 'Vous avez déjà voté depuis cet appareil.');
}

$data = json_decode(file_get_contents('php://input'), true);
$choix1 = isset($data['choix1']) ? (int)$data['choix1'] : 0;
$choix2 = isset($data['choix2']) ? (int)$data['choix2'] : 0;
$choix3 = isset($data['choix3']) ? (int)$data['choix3'] : 0;

if (!$choix1 || !$choix2 || !$choix3) {
    repondre(false, 'Veuillez sélectionner vos 3 choix.');
}

$idsUniques = array_unique([$choix1, $choix2, $choix3]);
if (count($idsUniques) !== 3) {
    repondre(false, 'Les 3 choix doivent être différents.');
}

// Vérifie que les 3 identifiants existent bien parmi les prestations
$placeholders = implode(',', array_fill(0, 3, '?'));
$stmt = getPDO()->prepare("SELECT COUNT(*) AS n FROM prestations WHERE id IN ($placeholders)");
$stmt->execute([$choix1, $choix2, $choix3]);
if ((int)$stmt->fetch()['n'] !== 3) {
    repondre(false, 'Sélection invalide.');
}

$jeton = bin2hex(random_bytes(16));

$stmt = getPDO()->prepare(
    'INSERT INTO votes (choix1_id, choix2_id, choix3_id, jeton_appareil, adresse_ip)
     VALUES (?, ?, ?, ?, ?)'
);
$stmt->execute([$choix1, $choix2, $choix3, $jeton, $_SERVER['REMOTE_ADDR'] ?? null]);

// Cookie valable 1 an : empêche un nouveau vote depuis le même navigateur
setcookie('a_vote', $jeton, [
    'expires'  => time() + 365 * 24 * 3600,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);

repondre(true, 'Vote enregistré.');
