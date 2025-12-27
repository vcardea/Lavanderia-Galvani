-- --------------------------------------------------------
-- SQL Setup per Lavanderia Galvani
-- Pulito e Ottimizzato
-- --------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 1. Creazione Database (Se non esiste)
--
CREATE DATABASE IF NOT EXISTS `lavanderia` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `lavanderia`;

-- --------------------------------------------------------

--
-- 2. Struttura della tabella `configurazioni`
--
CREATE TABLE IF NOT EXISTS `configurazioni` (
  `chiave` varchar(50) NOT NULL,
  `valore` varchar(255) NOT NULL,
  PRIMARY KEY (`chiave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `configurazioni`
--
INSERT INTO `configurazioni` (`chiave`, `valore`) VALUES
('max_hours_weekly', '5'),
('registration_code', '{REDACTED}'); -- Cambia questo valore in produzione!

-- --------------------------------------------------------

--
-- 3. Struttura della tabella `macchine`
--
CREATE TABLE IF NOT EXISTS `macchine` (
  `idmacchina` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `tipo` enum('lavatrice','asciugatrice') NOT NULL,
  `stato` enum('attiva','manutenzione') DEFAULT 'attiva',
  `ritardo` int(11) DEFAULT 0,
  PRIMARY KEY (`idmacchina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `macchine`
--
INSERT INTO `macchine` (`idmacchina`, `nome`, `tipo`, `stato`, `ritardo`) VALUES
(1, 'Lavatrice 1', 'lavatrice', 'attiva', 0),
(2, 'Lavatrice 2', 'lavatrice', 'attiva', 0),
(3, 'Asciugatrice', 'asciugatrice', 'attiva', 0);

-- --------------------------------------------------------

--
-- 4. Struttura della tabella `utenti`
--
CREATE TABLE IF NOT EXISTS `utenti` (
  `idutente` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `numero_appartamento` varchar(10) NOT NULL,
  `username` varchar(60) NOT NULL,
  `ruolo` enum('user','admin') DEFAULT 'user',
  `data_registrazione` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`idutente`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- 5. Struttura della tabella `prenotazioni`
-- Nota: Creata per ultima perché dipende da `utenti` e `macchine` (Foreign Keys)
--
CREATE TABLE IF NOT EXISTS `prenotazioni` (
  `idprenotazione` int(11) NOT NULL AUTO_INCREMENT,
  `idutente` int(11) NOT NULL,
  `idmacchina` int(11) NOT NULL,
  `data_prenotazione` date NOT NULL,
  `ora_inizio` time NOT NULL,
  `ora_fine` time NOT NULL,
  `stato` enum('in_attesa','confermata') DEFAULT 'in_attesa',
  `scadenza_hold` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`idprenotazione`),
  KEY `idutente` (`idutente`),
  KEY `idmacchina` (`idmacchina`),
  KEY `idx_conflitti` (`data_prenotazione`,`ora_inizio`,`idmacchina`),
  CONSTRAINT `prenotazioni_ibfk_1` FOREIGN KEY (`idutente`) REFERENCES `utenti` (`idutente`) ON DELETE CASCADE,
  CONSTRAINT `prenotazioni_ibfk_2` FOREIGN KEY (`idmacchina`) REFERENCES `macchine` (`idmacchina`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;