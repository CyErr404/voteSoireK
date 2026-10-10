<?php
require_once __DIR__ . '/db.php';

// Vrai seulement si le cookie correspond à un vote présent en base
function aDejaVote(): bool
{
    if (empty($_COOKIE['a_vote'])) {
        return false;
    }
    $stmt = getPDO()->prepare('SELECT COUNT(*) AS n FROM votes WHERE jeton_appareil = ?');
    $stmt->execute([$_COOKIE['a_vote']]);
    return (int) $stmt->fetch()['n'] > 0;
}