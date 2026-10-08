-- Schema de la base de données pour l'application de vote
-- A importer via phpMyAdmin (cPanel O2Switch > Bases de données MySQL > phpMyAdmin)

CREATE TABLE IF NOT EXISTS prestations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    ordre INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    choix1_id INT NOT NULL,
    choix2_id INT NOT NULL,
    choix3_id INT NOT NULL,
    jeton_appareil VARCHAR(64) NOT NULL,
    adresse_ip VARCHAR(45) DEFAULT NULL,
    cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_choix1 FOREIGN KEY (choix1_id) REFERENCES prestations(id),
    CONSTRAINT fk_choix2 FOREIGN KEY (choix2_id) REFERENCES prestations(id),
    CONSTRAINT fk_choix3 FOREIGN KEY (choix3_id) REFERENCES prestations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS parametres (
    cle VARCHAR(50) PRIMARY KEY,
    valeur VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO parametres (cle, valeur) VALUES ('vote_ouvert', '1');
INSERT INTO parametres (cle, valeur) VALUES ('vote_cloture_le', '');

-- Exemple de prestations : remplacez ou ajoutez-en via l'espace admin après mise en ligne
INSERT INTO prestations (nom, ordre) VALUES
('Prestation 1', 1),
('Prestation 2', 2),
('Prestation 3', 3);
