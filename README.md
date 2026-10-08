# Application de vote — mode d'emploi

Application testée de bout en bout (vote, anti-doublon, clôture, calcul des points, export).
Stack : PHP + MySQL, compatible avec un hébergement mutualisé classique comme O2Switch — aucune dépendance externe.

## Fonctionnement

- Le public scanne **un seul QR code**, qui pointe vers `index.php` : la page de vote.
- Chaque votant choisit son 1er, 2e et 3e choix parmi la liste des prestations.
- Barème : 1er = 3 points, 2e = 2 points, 3e = 1 point.
- **Anti-doublon** : un cookie est posé après le vote pour bloquer un second vote depuis le même navigateur. Ce n'est pas infaillible (navigation privée, autre appareil), c'est un système basé sur la confiance, comme convenu.
- Les résultats restent **cachés** (y compris pour vous) tant que vous n'avez pas cliqué sur "Clôturer le vote" dans le tableau de bord admin.

## Déploiement sur O2Switch (étape par étape)

### 1. Créer la base de données
1. Connectez-vous à **cPanel** O2Switch.
2. Allez dans **Bases de données MySQL**.
3. Créez une base (ex: `vote`) → le nom final sera `VOTRECPANEL_vote`.
4. Créez un utilisateur MySQL avec un mot de passe fort → le nom final sera `VOTRECPANEL_voteuser`.
5. Associez l'utilisateur à la base avec **tous les privilèges**.

### 2. Importer le schéma
1. Toujours dans cPanel, ouvrez **phpMyAdmin**.
2. Sélectionnez votre base `VOTRECPANEL_vote` dans la colonne de gauche.
3. Onglet **Importer** → choisissez le fichier `database.sql` fourni → cliquez sur **Exécuter**.

### 3. Mettre les fichiers en ligne
1. Via le **Gestionnaire de fichiers** cPanel (ou FileZilla en FTP), uploadez **tout le contenu** du dossier `vote_app` dans `public_html` (ou un sous-dossier, ex: `public_html/vote`).
2. Ouvrez `config.php` et remplacez :
   - `DB_NAME` par le nom complet de votre base
   - `DB_USER` par le nom complet de votre utilisateur
   - `DB_PASS` par son mot de passe
   - `ADMIN_PASSWORD` par un mot de passe que **vous seul** connaissez

### 4. Activer le HTTPS (important)
Dans cPanel, section **SSL/TLS Status**, activez **Let's Encrypt** (gratuit) sur votre domaine. Le vote et l'espace admin doivent toujours être utilisés en `https://`.

### 5. Ajouter vos prestations
Rendez-vous sur `https://votredomaine.com/vote/admin/` (ou `/admin/login.php`), connectez-vous avec votre mot de passe admin, puis allez dans **Prestations** pour ajouter vos 15 à 30 prestations (nom + ordre d'affichage). Les 3 prestations d'exemple peuvent être supprimées ou modifiées.

### 6. Générer le QR code
Le QR doit simplement pointer vers :
```
https://votredomaine.com/vote/index.php
```
Vous pouvez le générer sur qr-code-generator.com ou qrcode-monkey.com, et l'afficher en grand sur un écran pendant l'événement.

## Le jour de l'événement

1. Vérifiez dans **Tableau de bord** que le statut affiche bien **Ouvert**.
2. Après chaque série de prestations (ou à la fin, selon votre choix), laissez le public scanner le QR et voter.
3. Le tableau de bord affiche le nombre de votes reçus en direct (sans révéler le classement).
4. Une fois toutes les prestations passées et le temps de vote écoulé, cliquez sur **Clôturer le vote**.
5. Allez dans **Résultats** pour voir le classement final, et **Exporter en CSV** si besoin d'archiver.

## Fichiers du projet

```
vote_app/
├── index.php              page de vote publique
├── submit_vote.php         traitement d'un vote
├── config.php              identifiants base de données + mot de passe admin
├── database.sql            schéma à importer dans phpMyAdmin
├── assets/
│   ├── style.css
│   └── script.js
├── includes/
│   ├── db.php
│   ├── auth.php
│   └── nav_admin.php
└── admin/
    ├── login.php
    ├── logout.php
    ├── dashboard.php        statut du vote, ouverture/clôture
    ├── prestations.php       gestion des prestations (ajout/édition/suppression)
    ├── resultats.php         classement final (visible après clôture)
    └── export_csv.php        export du classement
```

## Limites connues (à accepter en connaissance de cause)

- L'anti-doublon repose sur un cookie navigateur : une personne motivée peut voter plusieurs fois (navigation privée, autre téléphone). Ce choix a été fait pour éviter toute friction (pas de SMS, pas de compte).
- Pensez à annoncer oralement "un seul vote par personne" pour un effet dissuasif.
