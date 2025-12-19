# 🧺 Lavanderia Galvani

![Status](https://img.shields.io/badge/Status-Operational-success?style=for-the-badge)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge)

> **Sistema di prenotazione centralizzato per la gestione delle lavatrici e asciugatrici dello studentato Galvani.**

Un'applicazione web **Mobile-First**, progettata per risolvere i conflitti tra inquilini, prevenire monopoli d'uso e garantire un accesso equo alle risorse comuni. Sviluppata con un focus su **Privacy (GDPR)**, **Performance** e **Semplicità**.

---

## ✨ Funzionalità Principali

### 🔒 Core & Sicurezza

- **Autenticazione Istituzionale**: Registrazione consentita solo tramite email `@studio.unibo.it` o `@unibo.it`.
- **Privacy by Design**: Gli utenti sono identificati pubblicamente tramite Username generati (es. `vincenzo12-89`) per proteggere i dati reali.
- **Password Hashing**: Utilizzo di `PASSWORD_BCRYPT` per la massima sicurezza.
- **GDPR Compliant**: Diritto all'oblio con procedura di anonimizzazione irreversibile.

### 📅 Prenotazioni & Logica

- **Pessimistic Locking**: Sistema anti-collisione in tempo reale. Se due utenti cliccano lo stesso slot simultaneamente, il database gestisce la concorrenza.
- **Limiti Settimanali**: Configurazione dinamica per limitare il numero di ore prenotabili a settimana per utente (Anti-Monopolio).
- **Slot Temporali**: Gestione intelligente degli slot passati e futuri (aperti solo per la settimana corrente).

### 📱 UI/UX

- **Dark Mode Nativa**: Interfaccia moderna scura, riposante per la vista.
- **Mobile First**: Ottimizzata per l'uso da smartphone ("Thumb Zone" navigation).
- **Feedback Immediato**: Modali e notifiche toast per ogni azione (conferma, errore, caricamento).
- **Multilingua**: Supporto nativo Italiano 🇮🇹 e Inglese 🇬🇧.

### 🛠 Pannello Amministrazione

- **Gestione Macchine**: Messa in manutenzione/riattivazione delle lavatrici con un click.
- **Gestione Utenti**: Reset password e anonimizzazione utenti problematici.
- **Configurazione Globale**: Modifica dei limiti di prenotazione in tempo reale senza toccare il codice.

---

## 📸 Screenshots

| Dispositivo |               Dashboard                |             Prenotazione Slot              |               Admin Panel                |
| :---------: | :------------------------------------: | :----------------------------------------: | :--------------------------------------: |
| **Desktop** |    ![PC Dash](./screen/pc-dash.png)    |    ![PC Booking](./screen/pc-modal.png)    |    ![PC Admin](./screen/pc-admin.png)    |
| **Mobile**  | ![Phone Dash](./screen/phone-dash.png) | ![Phone Booking](./screen/phone-modal.png) | ![Phone Admin](./screen/phone-admin.png) |

---

## 🏗 Tech Stack

Il progetto è costruito per essere leggero e facilmente deployabile su qualsiasi hosting condiviso o VPS standard.

- **Backend**: PHP 8.x (Vanilla, No Framework Bloat)
- **Database**: MySQL
- **Frontend**: HTML5, JavaScript (ES6+), Tailwind CSS (via CDN per rapid prototyping)
- **Architecture**: Custom MVC-like routing (`index.php` come entry point)

---

## 🚀 Installazione Locale

### Prerequisiti

- PHP >= 8.0
- MySQL o MariaDB
- Web Server (Apache o Nginx)

### Passaggi

1.  **Clona la repository**

    ```bash
    git clone [https://github.com/vcardea/Lavanderia-Galvani.git](https://github.com/vcardea/Lavanderia-Galvani.git)
    cd Lavanderia-Galvani
    ```

2.  **Configura il Database**

    - Crea un database vuoto (es. `lavanderia`).
    - Importa il file `database.sql` (che trovi nella root) per creare tabelle e dati iniziali.

    ```sql
    -- Esempio importazione CLI
    mysql -u root -p lavanderia < database.sql
    ```

3.  **Configura la connessione**

    - Apri `src/config/database.php`.
    - Modifica i parametri con le tue credenziali locali:

    ```php
    private $host = "localhost";
    private $db_name = "lavanderia";
    private $username = "root";
    private $password = "";
    ```

4.  **Avvia il Server**
    - Se usi PHP built-in server (per sviluppo rapido):
    ```bash
    php -S localhost:8000
    ```
    - Visita `http://localhost:8000` nel browser.

---

## 📂 Struttura del Progetto

```text
/
├── src/
│   ├── api/            # Endpoints AJAX (JSON responses)
│   ├── auth/           # Logica Login/Register/Logout
│   ├── config/         # Connessione DB
│   ├── lang/           # File di traduzione (it.php, en.php)
│   ├── pages/          # Viste (Dashboard, Admin, Privacy)
│   ├── templates/      # Header, Footer, parziali
│   ├── Lang.php        # Classe gestione lingue
│   └── utils.php       # Helper functions
├── public/
│   ├── css/            # Custom CSS (se necessario oltre Tailwind)
│   └── js/             # Main App Logic (app.js)
├── database.sql        # Schema DB e Dati Iniziali
├── index.php           # Router principale
└── README.md           # Documentazione
```
