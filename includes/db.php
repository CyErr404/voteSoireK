<?php
require_once __DIR__ . '/../config.php';

function getPDO(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function getParametre(string $cle): ?string
{
    $stmt = getPDO()->prepare('SELECT valeur FROM parametres WHERE cle = ?');
    $stmt->execute([$cle]);
    $row = $stmt->fetch();
    return $row ? $row['valeur'] : null;
}

function setParametre(string $cle, string $valeur): void
{
    $stmt = getPDO()->prepare(
        'INSERT INTO parametres (cle, valeur) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE valeur = VALUES(valeur)'
    );
    $stmt->execute([$cle, $valeur]);
}

function voteEstOuvert(): bool
{
    return getParametre('vote_ouvert') === '1';
}
