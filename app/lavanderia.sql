-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Dic 20, 2025 alle 22:12
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lavanderia`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `configurazioni`
--

CREATE TABLE `configurazioni` (
  `chiave` varchar(50) NOT NULL,
  `valore` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `configurazioni`
--

INSERT INTO `configurazioni` (`chiave`, `valore`) VALUES
('max_hours_weekly', '8');

-- --------------------------------------------------------

--
-- Struttura della tabella `macchine`
--

CREATE TABLE `macchine` (
  `idmacchina` int(11) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `tipo` enum('lavatrice','asciugatrice') NOT NULL,
  `stato` enum('attiva','manutenzione') DEFAULT 'attiva'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `macchine`
--

INSERT INTO `macchine` (`idmacchina`, `nome`, `tipo`, `stato`) VALUES
(1, 'Lavatrice 1', 'lavatrice', 'attiva'),
(2, 'Lavatrice 2', 'lavatrice', 'attiva'),
(3, 'Asciugatrice', 'asciugatrice', 'attiva');

-- --------------------------------------------------------

--
-- Struttura della tabella `prenotazioni`
--

CREATE TABLE `prenotazioni` (
  `idprenotazione` int(11) NOT NULL,
  `idutente` int(11) NOT NULL,
  `idmacchina` int(11) NOT NULL,
  `data_prenotazione` date NOT NULL,
  `ora_inizio` time NOT NULL,
  `ora_fine` time NOT NULL,
  `stato` enum('in_attesa','confermata') DEFAULT 'in_attesa',
  `scadenza_hold` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `utenti`
--

CREATE TABLE `utenti` (
  `idutente` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `nome` varchar(50) NOT NULL,
  `numero_appartamento` varchar(10) NOT NULL,
  `username` varchar(60) NOT NULL,
  `ruolo` enum('user','admin') DEFAULT 'user',
  `data_registrazione` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `utenti`
--

INSERT INTO `utenti` (`idutente`, `email`, `password_hash`, `nome`, `numero_appartamento`, `username`, `ruolo`, `data_registrazione`) VALUES
(6, 'vincenzo.cardea@studio.unibo.it', '$2y$10$vpkLhAaDqB.3hOXcv18zZOZ5w.JzlHlfZ2GxclXhYkwPJkjemB66q', 'Vincenzo', '23', 'vincenzo23-84', 'user', '2025-12-20 22:09:54');

UPDATE utenti
SET ruolo = 'admin'
WHERE email LIKE 'vincenzo.cardea@studio.unibo.it';

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `configurazioni`
--
ALTER TABLE `configurazioni`
  ADD PRIMARY KEY (`chiave`);

--
-- Indici per le tabelle `macchine`
--
ALTER TABLE `macchine`
  ADD PRIMARY KEY (`idmacchina`);

--
-- Indici per le tabelle `prenotazioni`
--
ALTER TABLE `prenotazioni`
  ADD PRIMARY KEY (`idprenotazione`),
  ADD KEY `idutente` (`idutente`),
  ADD KEY `idmacchina` (`idmacchina`),
  ADD KEY `idx_conflitti` (`data_prenotazione`,`ora_inizio`,`idmacchina`);

--
-- Indici per le tabelle `utenti`
--
ALTER TABLE `utenti`
  ADD PRIMARY KEY (`idutente`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `macchine`
--
ALTER TABLE `macchine`
  MODIFY `idmacchina` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `prenotazioni`
--
ALTER TABLE `prenotazioni`
  MODIFY `idprenotazione` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT per la tabella `utenti`
--
ALTER TABLE `utenti`
  MODIFY `idutente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `prenotazioni`
--
ALTER TABLE `prenotazioni`
  ADD CONSTRAINT `prenotazioni_ibfk_1` FOREIGN KEY (`idutente`) REFERENCES `utenti` (`idutente`) ON DELETE CASCADE,
  ADD CONSTRAINT `prenotazioni_ibfk_2` FOREIGN KEY (`idmacchina`) REFERENCES `macchine` (`idmacchina`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
