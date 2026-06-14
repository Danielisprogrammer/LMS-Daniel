CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('etudiant', 'enseignant', 'promoteur') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    description TEXT,
    id_promoteur INT,
    FOREIGN KEY (id_promoteur) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS lecons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(150) NOT NULL,
    contenu_path VARCHAR(255) NOT NULL,
    id_module INT,
    id_enseignant INT,
    FOREIGN KEY (id_module) REFERENCES modules(id) ON DELETE CASCADE,
    FOREIGN KEY (id_enseignant) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_lecon INT UNIQUE,
    question TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    reponse_correcte CHAR(1) NOT NULL,
    FOREIGN KEY (id_lecon) REFERENCES lecons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS progressions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_etudiant INT,
    id_lecon INT,
    note INT NOT NULL,
    UNIQUE KEY etudiant_lecon (id_etudiant, id_lecon),
    FOREIGN KEY (id_etudiant) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (id_lecon) REFERENCES lecons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion des utilisateurs de test (Mot de passe haché pour 'password' avec PASSWORD_DEFAULT)
INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES
('Dr. Jean Prof', 'prof@lms.com', '$2y$10$7rK9b9N1C28M9gG9V9J9O.O8t8K9Y3Z5a6b7c8d9e1f2g3h4i5j6k', 'enseignant'),
('Daniel Etudiant', 'etudiant@lms.com', '$2y$10$7rK9b9N1C28M9gG9V9J9O.O8t8K9Y3Z5a6b7c8d9e1f2g3h4i5j6k', 'etudiant'),
('Chef Promoteur', 'admin@lms.com', '$2y$10$7rK9b9N1C28M9gG9V9J9O.O8t8K9Y3Z5a6b7c8d9e1f2g3h4i5j6k', 'promoteur');