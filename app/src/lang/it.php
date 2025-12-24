<?php
return [
    // --- NAVIGAZIONE ---
    'nav_dashboard' => 'Bacheca',
    'nav_admin' => 'Amministrazione',
    'nav_logout' => 'Esci',
    'nav_login' => 'Accedi',
    'nav_register' => 'Registrati',
    'nav_brand' => 'Lavanderia',
    'logged_in_as' => 'Loggato come',

    // --- GIORNI DELLA SETTIMANA ---
    'day_0' => 'Dom',
    'day_1' => 'Lun',
    'day_2' => 'Mar',
    'day_3' => 'Mer',
    'day_4' => 'Gio',
    'day_5' => 'Ven',
    'day_6' => 'Sab',

    // --- DASHBOARD ---
    'dash_title' => 'Prenotazioni del',
    'status_maintenance' => 'MANUTENZIONE',
    'status_free' => 'Libero',
    'status_pending' => 'In attesa...',
    'status_taken' => 'Occupato',
    'status_mine' => 'Mio',

    // --- ADMIN PANEL ---
    'admin_title' => 'Pannello Amministrazione',
    'admin_subtitle' => 'Admin Area',
    'admin_sect_settings' => 'Impostazioni Generali',
    'lbl_max_hours' => 'Max Ore Settimanali per Utente',
    'note_max_hours' => 'Ore max prenotabili a settimana per utente.',
    'lbl_registration_code' => 'Codice Segreto Registrazione',
    'note_registration_code' => 'Il codice che gli studenti devono inserire per registrarsi.',
    'err_invalid_registration_code' => 'Il Codice Residenza è errato. Lo trovi appeso in lavanderia.',
    'btn_save' => 'Salva Modifiche',
    'note_immediate_effect' => '*Questa modifica ha effetto immediato su tutte le nuove prenotazioni.',
    'delete_user' => 'Elimina',
    'reset_user_pwd' => 'Resetta Password',

    'admin_sect_machines' => 'Stato Macchine',
    'th_name' => 'Nome',
    'th_type' => 'Tipo',
    'th_status' => 'Stato Attuale',
    'th_action' => 'Azione',
    'st_active' => 'Attiva',
    'st_maint' => 'Manutenzione',
    'btn_set_maint' => 'Metti in Manutenzione',
    'btn_set_active' => 'Riattiva',

    'admin_sect_users' => 'Lista Utenti',
    'th_username' => 'Username',
    'th_email' => 'Email',
    'th_apt' => 'Appartamento',

    // Messaggi Admin (Backend)
    'msg_config_updated' => 'Limite ore settimanali aggiornato a: <strong>%d</strong>',
    'msg_invalid_num' => 'Inserisci un numero valido.',
    'msg_machine_updated' => 'Stato macchina aggiornato a: <strong>%s</strong>',
    'msg_pass_reset' => 'Password resettata. Nuova password:',
    'msg_user_deleted' => 'Utente anonimizzato e prenotazioni future cancellate.',

    // Modali Admin
    'modal_reset_title' => 'Reset Password',
    'modal_reset_body' => 'Sei sicuro di voler resettare la password per l\'utente <b>%s</b>?<br>La nuova password verrà mostrata a schermo.',
    'modal_delete_title' => 'Elimina Utente',
    'modal_delete_body' => 'ATTENZIONE: Stai per anonimizzare l\'utente <b>%s</b>.<br>Questa azione è irreversibile.',
    'btn_reset_confirm' => 'Reset Password',
    'btn_delete_confirm' => 'Elimina Definitivamente',

    // --- COMUNI & AUTH ---
    'email_label' => 'Email Istituzionale',
    'password_label' => 'Password',
    'password_confirm_label' => 'Conferma Password',
    'apt_label' => 'Appartamento',
    'username_label' => 'Username',
    'login_title' => 'Accedi',
    'btn_enter' => 'Entra',
    'link_no_account' => 'Non hai un account?',
    'link_create_account' => 'Crea account',
    'error_creds' => 'Credenziali non valide.',
    'register_title' => 'Crea Account',
    'residence_code_info' => 'Il codice è appeso sulla bacheca della lavanderia.',
    'btn_register' => 'Registrati',
    'link_have_account' => 'Hai già un account?',
    'link_login_here' => 'Accedi qui',
    'lbl_username_gen' => 'Username (Generato)',
    'lbl_username_desc' => 'sarà il tuo nome utente',

    // --- ERRORI ---
    'err_apt_range' => 'Il numero appartamento deve essere compreso tra 1 e 23.',
    'err_email_domain' => 'Devi usare una mail istituzionale (@studio.unibo.it o @unibo.it)',
    'err_username_empty' => 'Lo username non è stato generato correttamente.',
    'err_username_format' => 'Formato username non valido (es. nome12-89).',
    'err_pass_match' => 'Le password non coincidono.',
    'err_pass_short' => 'La password deve essere di almeno 8 caratteri.',
    'err_user_taken' => 'Email già registrata o Username non disponibile.',
    'err_db_generic' => 'Errore generico nel database.',
    'err_db_conn' => 'Errore di connessione al database: ',
    'err_method' => 'Metodo errato',
    'err_login_required' => 'Login richiesto',
    'err_current_week_only' => 'Puoi prenotare solo per la settimana corrente!',
    'err_past_date' => 'Non puoi prenotare nel passato!',
    'err_future_date' => 'Non puoi prenotare nel futuro!',
    'err_invalid_date' => 'Data non valida',
    'err_machine_maintenance' => 'Questa macchina è in manutenzione al momento.',
    'err_limit_reached' => 'Hai raggiunto il limite di %d ore settimanali!',
    'err_slot_occupied' => 'Slot appena occupato da un altro utente!',
    'err_tech_lock' => 'Errore Tecnico (Lock)',
    'err_unauthorized' => 'Non autorizzato',
    'err_booking_not_found' => 'Prenotazione non trovata o non tua.',
    'err_missing_id' => 'ID prenotazione mancante',
    'err_booking_expired' => 'Prenotazione non trovata o scaduta.',
    'err_tech_confirm' => 'Errore tecnico durante la conferma.',
    'err_missing_params' => 'Parametri mancanti',

    // --- FOOTER & PRIVACY ---
    'footer_desc' => 'Sistema di prenotazione lavatrici e asciugatrici per lo studentato Galvani.',
    'footer_support' => 'Supporto',
    'footer_rules' => 'Regolamento',
    'footer_privacy' => 'Privacy Policy',
    'footer_report' => 'Segnala Problema',
    'footer_source_code' => 'Codice Sorgente',
    'footer_operational' => 'Sistema Operativo',
    'footer_server_time' => 'SRV',
    'footer_coded' => 'Coded with',
    'footer_by' => 'by',
    'btn_back_dashboard' => 'Torna alla Dashboard',

    // Privacy (Titoli generici se servono dynamicamente)
    'privacy_title' => 'Privacy & Gestione Dati',

    // JS MODALS
    'modal_cancel_title' => 'Cancella Prenotazione',
    'modal_cancel_msg' => 'Vuoi davvero cancellare la prenotazione delle',
    'modal_confirm_title' => 'Conferma Prenotazione',
    'modal_booking_msg' => 'Stai prenotando per le ore',
    'btn_confirm' => 'Conferma',
    'btn_close' => 'Chiudi',
    'btn_cancel' => 'Annulla',
    'btn_delete' => 'Elimina',
    'msg_info' => 'Info',
    'msg_error' => 'Errore',
    'err_network' => 'Errore di Rete',
    'err_server' => 'Errore Server',
    'remaining_time' => 'Tempo Rimanente',
    'msg_timeout' => 'Prenotazione scaduta! Riprova.',

    // --- PAGINA PRIVACY ---
    'priv_title' => 'Privacy & Gestione Dati',
    'priv_last_update' => 'Ultimo agg:',
    'priv_ref_gdpr' => 'Rif. GDPR (UE 2016/679)',

    // Sezione 1
    'priv_s1_title' => '1. Quali dati raccogliamo',
    'priv_s1_desc' => 'Raccogliamo solo le informazioni strettamente necessarie (Principio di Minimizzazione) per garantire il funzionamento del servizio.',
    'priv_data_email' => 'Email Istituzionale',
    'priv_data_email_desc' => 'Per identificarti univocamente come studente autorizzato.',
    'priv_data_apt' => 'Numero Appartamento',
    'priv_data_apt_desc' => 'Per comunicazioni urgenti relative all\'uso delle macchine.',
    'priv_data_pass' => 'Password',
    'priv_data_pass_desc' => 'Salvata esclusivamente in formato hash (criptato irreversibilmente).',
    'priv_data_log' => 'Log di Prenotazione',
    'priv_data_log_desc' => 'Storico di utilizzo per la gestione dei turni.',

    // Sezione 2
    'priv_s2_title' => '2. Finalità del trattamento',
    'priv_s2_intro' => 'I tuoi dati servono esclusivamente a:',
    'priv_use_1' => 'Gestire l\'accesso sicuro (Login).',
    'priv_use_2' => 'Organizzare il calendario ed evitare conflitti tra inquilini.',
    'priv_use_3' => 'Garantire il rispetto delle regole comuni.',
    'priv_note_vis_title' => 'Nota sulla visibilità:',
    'priv_note_vis_desc' => 'I tuoi dati non vengono ceduti a terzi. Tuttavia, il tuo <em>Username</em> sarà visibile agli altri studenti sul calendario prenotazioni per permettere l\'organizzazione interna (es. scambi turno).',

    // Sezione 3
    'priv_s3_title' => '3. Cancellazione e Diritto all\'Oblio',
    'priv_s3_desc' => 'Puoi richiedere la cancellazione del tuo account in qualsiasi momento (Art. 17 GDPR). Quando un account viene eliminato, applichiamo una procedura di <strong>Anonimizzazione</strong>.',
    'priv_del_title' => 'Dati Eliminati',
    'priv_del_1' => 'Email e Password',
    'priv_del_2' => 'Associazione col numero appartamento',
    'priv_del_3' => 'Tutte le prenotazioni future',
    'priv_anon_title' => 'Dati Anonimizzati',
    'priv_anon_1' => 'Lo storico delle prenotazioni passate rimane per statistica.',
    'priv_anon_2' => 'L\'autore diventa genericamente <em>"Utente Cancellato"</em>.',

    // Sezione 4
    'priv_s4_title' => '4. I tuoi Diritti',
    'priv_s4_desc' => 'In conformità al GDPR (Art. 15-22), hai diritto di chiedere al gestore:',
    'priv_right_access' => 'L\'accesso ai tuoi dati',
    'priv_right_rect' => 'La rettifica di dati errati',
    'priv_right_limit' => 'La limitazione del trattamento',
    'priv_right_port' => 'La portabilità dei dati',
    'priv_contact_text' => 'Per esercitare i diritti:',

    // Sezione 5
    'priv_s5_title' => '5. Cookie Policy',
    'priv_s5_desc' => 'Utilizziamo esclusivamente <strong>Cookie Tecnici</strong> (Session ID) necessari per mantenere l\'accesso attivo mentre navighi. Non effettuiamo tracciamento, profilazione pubblicitaria né usiamo analytics di terze parti invasive.',

    // Segnalazione Ritardo
    'lbl_delay' => 'Ritardo',
    'lbl_delay_min' => 'min',
    'modal_delay_title' => 'Segnala Ritardo',
    'modal_delay_desc' => 'Inserisci i minuti di ritardo accumulati da questa macchina per avvisare i prossimi utenti.',
    'btn_update_delay' => 'Aggiorna Ritardo',
    'delay_saved' => 'Ritardo aggiornato!',

    // Lavanderia
    'Lavatrice 1' => 'Lavatrice 1',
    'Lavatrice 2' => 'Lavatrice 2',
    'Asciugatrice' => 'Asciugatrice',
    'lavatrice' => 'Lavatrice',
    'asciugatrice' => 'Asciugatrice',

    // Messaggi
    'no_other_registered_users' => 'Nessun altro utente registrato.',
    'no_other_users' => 'Nessun altro utente.',
];
