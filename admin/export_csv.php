<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireAdmin();

if (voteEstOuvert()) {
    http_response_code(403);
    echo 'Clôturez le vote avant d\'exporter les résultats.';
    exit;
}

$sql = "
    SELECT
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
    ORDER BY points DESC, nb_1er DESC, p.nom ASC
";
$lignes = getPDO()->query($sql)->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="resultats_vote.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Rang', 'Prestation', 'Points', '1er choix', '2e choix', '3e choix'], ';');
foreach ($lignes as $i => $l) {
    fputcsv($out, [$i + 1, $l['nom'], $l['points'], $l['nb_1er'], $l['nb_2e'], $l['nb_3e']], ';');
}
fclose($out);
exit;
