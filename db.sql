-- Creazione Tabella Utenti
CREATE TABLE
    IF NOT EXISTS utenti (
        idutente INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(100) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        nome VARCHAR(50) NOT NULL, -- Parte estratta dall'email
        numero_appartamento VARCHAR(10) NOT NULL, -- Inserito manualmente
        username VARCHAR(60) NOT NULL UNIQUE, -- nome + numero_appartamento
        ruolo ENUM ('user', 'admin') DEFAULT 'user',
        data_registrazione DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Creazione Tabella Macchine
CREATE TABLE
    IF NOT EXISTS macchine (
        idmacchina INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(50) NOT NULL,
        tipo ENUM ('lavatrice', 'asciugatrice') NOT NULL,
        stato ENUM ('attiva', 'manutenzione') DEFAULT 'attiva'
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- Popolamento Macchine (2 Lavatrici, 1 Asciugatrice)
INSERT INTO
    macchine (nome, tipo)
VALUES
    ('Lavatrice 1', 'lavatrice'),
    ('Lavatrice 2', 'lavatrice'),
    ('Asciugatrice', 'asciugatrice');

-- Creazione Tabella Prenotazioni
CREATE TABLE
    IF NOT EXISTS prenotazioni (
        idprenotazione INT AUTO_INCREMENT PRIMARY KEY,
        idutente INT NOT NULL,
        idmacchina INT NOT NULL,
        data_prenotazione DATE NOT NULL,
        ora_inizio TIME NOT NULL,
        ora_fine TIME NOT NULL,
        stato ENUM ('in_attesa', 'confermata') DEFAULT 'in_attesa', -- 'in_attesa' è il Giallo
        scadenza_hold TIMESTAMP NULL, -- Se lo stato è 'in_attesa', qui salviamo quando scade
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (idutente) REFERENCES utenti (idutente) ON DELETE CASCADE,
        FOREIGN KEY (idmacchina) REFERENCES macchine (idmacchina),
        -- Indice per velocizzare le query di ricerca conflitti
        INDEX idx_conflitti (data_prenotazione, ora_inizio, idmacchina)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

