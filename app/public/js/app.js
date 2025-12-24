/**
 * PUBLIC/JS/APP.JS
 * Gestione Prenotazioni, Cancellazioni e Ritardi.
 *
 * Logica principale:
 * 1. Fetch periodico dello stato (polling).
 * 2. Sistema di "Locking" ottimistico per prenotare.
 * 3. Gestione visiva tramite classi Tailwind.
 */

// --- STATO GLOBALE ---

/** Elemento DOM dello slot attualmente selezionato per interazione. */
let currentSlotElement = null;

/** ID del lock o della prenotazione corrente. Usato per confermare/cancellare. */
let currentLockId = null;

/** Riferimento all'intervallo del timer del modale (countdown). */
let modalTimerInterval = null;

/** Riferimento all'intervallo del polling delle prenotazioni. */
let pollingInterval = null;

// --- INIZIALIZZAZIONE ---

document.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);
  // Usa la data nell'URL o, fallback, la data odierna (YYYY-MM-DD)
  const currentDate =
    urlParams.get("date") || new Date().toISOString().split("T")[0];

  // Primo caricamento immediato
  fetchPrenotazioni(currentDate);

  // Polling ogni 3 secondi per aggiornare la griglia
  pollingInterval = setInterval(() => fetchPrenotazioni(currentDate), 3000);

  // Gestione chiusura modali cliccando sull'overlay scuro
  document.querySelectorAll(".modal-overlay").forEach((modal) => {
    modal.addEventListener("click", (e) => {
      // Verifica che il click sia proprio sull'overlay e non sul contenuto
      if (e.target.classList.contains("modal-overlay")) {
        if (modal.id === "bookingModal") handleCloseModal();
        if (modal.id === "delayModal") closeDelayModal();
      }
    });
  });
});

// --- LOGICA DI PRENOTAZIONE (CORE) ---

/**
 * Gestisce il click su uno slot della griglia.
 * Determina se avviare una prenotazione, una cancellazione o mostrare info.
 *
 * @param {HTMLElement} element - L'elemento DOM dello slot cliccato.
 */
async function prenotaSlot(element) {
  currentSlotElement = element;
  const machineId = element.dataset.machine;
  const date = element.dataset.date;
  const hour = element.dataset.hour;
  // Recupera l'etichetta oraria visiva o costruisce una stringa fallback
  const timeLabel =
    element.querySelector(".time-label")?.innerText || hour + ":00";

  // Ignora slot passati
  if (element.classList.contains("past")) return;

  // 1. SCENARIO: SLOT OCCUPATO (DA ALTRI O IN ATTESA)
  if (
    element.classList.contains("taken") ||
    element.classList.contains("pending")
  ) {
    let msg = element.classList.contains("pending")
      ? t("status_pending") // "In attesa..."
      : `${t("status_taken")} (${element.dataset.username || "..."})`; // "Occupato da (User)"

    openModal(t("msg_info"), msg, "info");
    return;
  }

  // 2. SCENARIO: CANCELLAZIONE (SLOT MIO)
  if (element.classList.contains("mine")) {
    currentLockId = element.dataset.idprenotazione;

    // Fallback: Se l'ID manca nel DOM, prova a ricaricare i dati dal server
    if (!currentLockId) {
      console.warn("ID Prenotazione mancante nel DOM. Tentativo di refresh...");
      await fetchPrenotazioni(date);
      currentLockId = element.dataset.idprenotazione;

      if (!currentLockId) {
        openModal(
          t("msg_error"),
          "Errore sincronizzazione. Ricarica la pagina.",
          "info"
        );
        return;
      }
    }

    openModal(
      t("modal_cancel_title"),
      `${t("modal_cancel_msg")} <b>${timeLabel}</b>?`,
      "danger",
      confirmCancellation
    );
    return;
  }

  // 3. SCENARIO: NUOVA PRENOTAZIONE (LOCK)
  // Feedback visivo immediato (opacity) per indicare elaborazione
  element.style.opacity = "0.5";

  try {
    // Chiamata API per ottenere un "Lock" temporaneo
    const response = await fetch(BASE_URL + "/api/lock", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `idmacchina=${machineId}&data=${date}&ora=${hour}`,
    });
    const result = await response.json();

    element.style.opacity = "1"; // Ripristina opacità

    if (result.success) {
      currentLockId = result.lock_id;
      updateSlotVisual(element, "pending", t("status_pending"));

      // Apre il modale di conferma con un timer (es. 60 secondi di lock)
      // Nota: La durata del timer dovrebbe matchare quella del server.
      openModal(
        t("modal_confirm_title"),
        `${t("modal_booking_msg")} <b>${timeLabel}</b>.<br>${t(
          "btn_confirm"
        )}?`,
        "confirm",
        confirmBooking,
        119 // Durata timer in secondi
      );
    } else {
      // Lock fallito (es. qualcun altro ha cliccato un ms prima)
      openModal(t("msg_info"), result.message, "info");
      fetchPrenotazioni(date);
    }
  } catch (e) {
    element.style.opacity = "1";
    console.error("Errore Lock:", e);
    openModal(t("msg_error"), t("err_server"), "info");
  }
}

/**
 * Conferma la prenotazione dopo aver ottenuto il Lock.
 * Inviata quando l'utente clicca "Conferma" nel modale.
 */
async function confirmBooking() {
  const btn = document.querySelector(".btn-confirm");

  // UI: Disabilita il bottone per evitare doppi click
  if (btn) {
    btn.disabled = true;
    btn.innerText = "...";
  }

  try {
    const response = await fetch(BASE_URL + "/api/prenota", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `lock_id=${currentLockId}`,
    });

    // DEBUG: Leggiamo come testo per intercettare errori PHP non-JSON
    const textRaw = await response.text();
    let result;
    try {
      result = JSON.parse(textRaw);
    } catch (e) {
      throw new Error("Risposta server non valida (non-JSON): " + textRaw);
    }

    if (result.success) {
      // Aggiornamento ottimistico dell'UI
      if (currentSlotElement) {
        updateSlotVisual(currentSlotElement, "mine", t("status_mine"));
        currentSlotElement.dataset.idprenotazione = currentLockId; // Aggiorna ID persistente
      }

      // Reset variabili di stato
      currentLockId = null;
      closeModalRaw();

      // Refresh di sicurezza dei dati
      if (currentSlotElement) {
        setTimeout(
          () => fetchPrenotazioni(currentSlotElement.dataset.date),
          500
        );
      }
    } else {
      // Errore logico (es. Lock scaduto)
      closeModalRaw();
      openModal(t("msg_info"), result.message, "info");
      if (currentSlotElement)
        fetchPrenotazioni(currentSlotElement.dataset.date);
    }
  } catch (e) {
    console.error("Errore Confirm:", e);
    closeModalRaw();
    openModal(t("msg_error"), e.message, "error");
  }
}

/**
 * Conferma la cancellazione di una prenotazione esistente.
 */
async function confirmCancellation() {
  if (!currentLockId) return;

  try {
    const response = await fetch(BASE_URL + "/api/cancella", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `idprenotazione=${currentLockId}`,
    });
    const result = await response.json();

    if (result.success) {
      closeModalRaw();
      // Aggiornamento ottimistico
      if (currentSlotElement) {
        updateSlotVisual(currentSlotElement, "free", t("status_free"));
        delete currentSlotElement.dataset.idprenotazione;
      }
      // Sync finale
      fetchPrenotazioni(currentSlotElement.dataset.date);
    } else {
      closeModalRaw();
      openModal(t("msg_error"), result.message, "info");
    }
  } catch (e) {
    closeModalRaw();
    openModal(t("msg_error"), t("err_network"), "info");
  }
}

// --- FUNZIONI RITARDO ---

/**
 * Apre il modale per gestire il ritardo di una macchina.
 * @param {string|number} id - ID della macchina.
 * @param {string} name - Nome della macchina (opzionale).
 * @param {number} currentDelay - Ritardo attuale in minuti.
 */
function openDelayModal(id, name, currentDelay) {
  document.getElementById("delayMachineId").value = id;
  document.getElementById("delayInput").value = currentDelay;
  if (name) document.getElementById("delayMachineName").innerText = name;
  document.getElementById("delayModal").classList.add("open");
}

function closeDelayModal() {
  document.getElementById("delayModal").classList.remove("open");
}

/**
 * Incrementa o decrementa il valore dell'input ritardo.
 * @param {number} delta - Valore da aggiungere (es. +5 o -5).
 */
function adjustDelay(delta) {
  const input = document.getElementById("delayInput");
  // ParseInt sicuro con fallback a 0
  let val = parseInt(input.value) || 0;
  // Impedisce valori negativi
  input.value = Math.max(0, val + delta);
}

/**
 * Salva il ritardo chiamando l'API backend.
 */
async function saveDelay() {
  const id = document.getElementById("delayMachineId").value;
  const minutes = document.getElementById("delayInput").value;
  const btn = document.querySelector(
    '#delayModal button[onclick="saveDelay()"]'
  );

  // UI Feedback: disabilita bottone durante il salvataggio
  const originalText = btn.innerText;
  btn.innerText = "...";
  btn.disabled = true;

  try {
    const response = await fetch(BASE_URL + "/api/delay", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `idmacchina=${id}&minuti=${minutes}`,
    });
    const result = await response.json();

    if (result.success) {
      closeDelayModal();
      const urlParams = new URLSearchParams(window.location.search);
      // Refresh immediato per mostrare il badge ritardo aggiornato
      fetchPrenotazioni(
        urlParams.get("date") || new Date().toISOString().split("T")[0]
      );
    } else {
      alert(result.message);
    }
  } catch (e) {
    console.error("Errore Salvataggio Ritardo:", e);
  } finally {
    // Ripristina stato bottone
    btn.innerText = originalText;
    btn.disabled = false;
  }
}

// --- CORE UTILS & MODAL ---

/**
 * Gestisce la chiusura "intelligente" del modale di prenotazione.
 * Se c'è un lock attivo e non è stato confermato, lo sblocca via API.
 */
function handleCloseModal() {
  // 1. Pulisce sempre il timer del modale
  if (modalTimerInterval) {
    clearInterval(modalTimerInterval);
    modalTimerInterval = null;
  }

  const modal = document.getElementById("bookingModal");
  if (!modal.classList.contains("open")) return;

  // Se stiamo chiudendo un modale con un lock attivo (es. utente clicca "Annulla" o overlay)
  // E il bottone conferma non è disabilitato (quindi non stiamo già inviando i dati)
  if (currentLockId && !document.querySelector(".btn-confirm")?.disabled) {
    // Chiamata "Fire and forget" per sbloccare
    fetch(BASE_URL + "/api/unlock", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `lock_id=${currentLockId}`,
    });

    // Reset visivo immediato
    if (
      currentSlotElement &&
      currentSlotElement.classList.contains("pending")
    ) {
      updateSlotVisual(currentSlotElement, "free", t("status_free"));
    }
  }

  currentLockId = null;
  modal.classList.remove("open");
}

/**
 * Chiude il modale senza logica aggiuntiva (solo UI).
 * Usato dopo che un'operazione è stata completata con successo o fallita definitivamente.
 */
function closeModalRaw() {
  if (modalTimerInterval) {
    clearInterval(modalTimerInterval);
    modalTimerInterval = null;
  }
  document.getElementById("bookingModal").classList.remove("open");
}

/**
 * Apre un modale generico con titolo, corpo, tipo e callback opzionale.
 * * @param {string} title - Titolo del modale.
 * @param {string} body - Contenuto HTML del corpo.
 * @param {string} type - 'info', 'danger', 'confirm'.
 * @param {function} callback - Funzione da eseguire alla conferma.
 * @param {number} duration - Durata opzionale del timer in secondi.
 */
function openModal(title, body, type, callback, duration = 0) {
  // 1. Pulizia preventiva timer
  if (modalTimerInterval) clearInterval(modalTimerInterval);

  document.getElementById("modalTitle").innerText = title;
  const bodyEl = document.getElementById("modalBody");
  bodyEl.innerHTML = body;

  // 2. Setup Timer (se duration > 0)
  if (duration > 0) {
    // Aggiungiamo un contenitore specifico con ID per poterlo rimpiazzare facilmente
    const timerHtml = `
            <div id="timerContainer" class="mt-4 pt-4 border-t border-zinc-700 text-center transition-all duration-300">
                <p class="text-xs text-gray-400 mb-1 uppercase tracking-wider">Tempo rimanente</p>
                <span id="modalTimerDisplay" class="font-mono text-2xl font-bold text-white"></span>
            </div>
        `;
    bodyEl.insertAdjacentHTML("beforeend", timerHtml);

    const timerDisplay = document.getElementById("modalTimerDisplay");
    let timeLeft = duration;

    const updateTimer = () => {
      const minutes = Math.floor(timeLeft / 60);
      const seconds = timeLeft % 60;
      const formattedTime = `${minutes}:${seconds < 10 ? "0" : ""}${seconds}`;

      if (timerDisplay) {
        timerDisplay.innerText = formattedTime;
        // Effetto visivo urgenza (< 15s) - Rosso pulsante
        if (timeLeft < 15) {
          timerDisplay.classList.remove("text-white");
          timerDisplay.classList.add("text-red-500", "animate-pulse");
        }
      }

      // --- TEMPO SCADUTO ---
      if (timeLeft <= 0) {
        clearInterval(modalTimerInterval);
        modalTimerInterval = null;

        // A. Rimuoviamo il bottone "Conferma" perché il lock è perso
        const btnConfirm = document.querySelector(".btn-confirm");
        if (btnConfirm) btnConfirm.remove();

        // B. Modifichiamo il testo del bottone "Annulla" in "Chiudi"
        const btnCancel = document.querySelector("#modalActions button");
        if (btnCancel) {
          btnCancel.innerText = t("btn_close") || "Chiudi";
          btnCancel.classList.remove("bg-zinc-700");
          btnCancel.classList.add(
            "bg-red-900/50",
            "text-red-200",
            "border",
            "border-red-800"
          );
        }

        // C. Sostituiamo il Timer con il messaggio di errore elegante
        const container = document.getElementById("timerContainer");
        if (container) {
          container.innerHTML = `
                        <div class="p-3 bg-red-500/10 border border-red-500/50 rounded-lg text-red-400 text-sm font-bold flex items-center justify-center gap-2 animate-in fade-in zoom-in duration-300">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                            ${t("msg_timeout")}
                        </div>
                    `;
        }

        // D. Rilasciamo il lock visivamente nella griglia (refresh dati)
        currentLockId = null; // Annulliamo il riferimento locale
        const urlParams = new URLSearchParams(window.location.search);
        fetchPrenotazioni(
          urlParams.get("date") || new Date().toISOString().split("T")[0]
        );
      }
      timeLeft--;
    };

    // Avvio immediato
    updateTimer();
    modalTimerInterval = setInterval(updateTimer, 1000);
  }

  // 3. Costruzione Bottoni Azione
  const actions = document.getElementById("modalActions");
  actions.innerHTML = "";

  // Bottone Annulla (che diventerà "Chiudi" al timeout)
  const btnCancel = document.createElement("button");
  btnCancel.className =
    "flex-1 px-4 py-2 rounded bg-zinc-700 text-white font-medium hover:bg-zinc-600 transition-colors";
  btnCancel.innerText = type === "info" ? t("btn_close") : t("btn_cancel");
  btnCancel.onclick = () => {
    handleCloseModal();
  };
  actions.appendChild(btnCancel);

  // Bottone Conferma
  if (type !== "info" && callback) {
    const btnOk = document.createElement("button");
    const colorClass =
      type === "danger"
        ? "bg-red-600 hover:bg-red-500 shadow-red-900/20"
        : "bg-accent hover:bg-blue-600 shadow-blue-900/20";

    btnOk.className = `flex-1 px-4 py-2 rounded font-bold text-white transition-colors shadow-lg btn-confirm ${colorClass}`;
    btnOk.innerText = type === "danger" ? t("btn_delete") : t("btn_confirm");

    btnOk.onclick = () => {
      if (modalTimerInterval) clearInterval(modalTimerInterval);
      callback();
    };
    actions.appendChild(btnOk);
  }

  document.getElementById("bookingModal").classList.add("open");
}

/**
 * Recupera lo stato delle prenotazioni dal server e aggiorna il DOM.
 * @param {string} date - Data in formato YYYY-MM-DD.
 */
async function fetchPrenotazioni(date) {
  try {
    // Aggiunge timestamp per evitare caching aggressivo del browser
    const response = await fetch(
      `${BASE_URL}/api/read?date=${date}&t=${new Date().getTime()}`
    );
    const data = await response.json();

    if (!data.success) return;

    // --- 1. Aggiornamento Griglia Slot ---

    // Creiamo una mappa per accesso O(1) invece di cercare nell'array ogni volta
    const dataMap = {};
    data.prenotazioni.forEach((p) => {
      const hourInt = parseInt(p.ora_inizio.split(":")[0]);
      const slotId = `slot_${p.idmacchina}_${p.data_prenotazione}_${hourInt}`;
      dataMap[slotId] = p;
    });

    document.querySelectorAll(".slot").forEach((slotEl) => {
      const p = dataMap[slotEl.id];

      // Default: Slot Libero
      let targetClass = "free";
      let targetText = t("status_free");
      let username = null;
      let newId = "";

      if (p) {
        newId = p.idprenotazione;
        if (p.is_mine) {
          targetClass = p.stato === "in_attesa" ? "pending" : "mine";
          targetText =
            p.stato === "in_attesa" ? t("status_pending") : t("status_mine");
        } else {
          targetClass = p.stato === "in_attesa" ? "pending" : "taken";
          targetText =
            p.stato === "in_attesa"
              ? t("status_pending")
              : p.username || t("status_taken");
          username = p.username;
        }
      }

      // Ottimizzazione DOM: Aggiorna classi solo se cambiano
      if (!slotEl.classList.contains(targetClass)) {
        slotEl.classList.remove("free", "taken", "mine", "pending");
        slotEl.classList.add(targetClass);
      }

      // Aggiorna testo stato
      const span = slotEl.querySelector(".status-text");
      if (span && span.innerText !== targetText) span.innerText = targetText;

      // Aggiorna dataset (ID prenotazione e Username)
      if (slotEl.dataset.idprenotazione != newId) {
        if (newId) slotEl.dataset.idprenotazione = newId;
        else delete slotEl.dataset.idprenotazione;
      }

      if (username) slotEl.dataset.username = username;
      else delete slotEl.dataset.username;
    });

    // --- 2. Aggiornamento Ritardi ---

    // Definiamo le classi CSS una volta sola per pulizia codice
    const CSS_DELAY_ACTIVE = [
      "bg-yellow-500/10",
      "text-yellow-400",
      "border-yellow-500/30",
      "animate-pulse",
    ];
    const CSS_DELAY_INACTIVE = [
      "bg-zinc-900/80",
      "text-zinc-500",
      "border-zinc-700",
      "hover:text-gray-300",
      "hover:border-zinc-500",
    ];

    if (data.macchine) {
      data.macchine.forEach((macchina) => {
        const headerId = `#machine-header-${macchina.idmacchina}`;
        const btn = document.querySelector(`${headerId} .machine-delay-btn`);
        const span = document.querySelector(`${headerId} .delay-val`);

        if (btn && span) {
          const rit = parseInt(macchina.ritardo);
          const txt =
            rit > 0 ? `+${rit} ` + t("lbl_delay_min") : t("lbl_delay");

          if (span.innerText !== txt) span.innerText = txt;

          if (rit > 0) {
            btn.classList.add(...CSS_DELAY_ACTIVE);
            btn.classList.remove(...CSS_DELAY_INACTIVE);
          } else {
            btn.classList.remove(...CSS_DELAY_ACTIVE);
            btn.classList.add(...CSS_DELAY_INACTIVE);
          }

          // Riattacca il listener con il nuovo valore di ritardo
          btn.onclick = () => openDelayModal(macchina.idmacchina, "", rit);
        }
      });
    }
  } catch (e) {
    console.error("Errore fetch aggiornamento:", e);
  }
}

/**
 * Aggiorna visivamente un singolo slot senza ricaricare tutto.
 * @param {HTMLElement} el - Elemento DOM.
 * @param {string} className - Classe da applicare (free, mine, taken, pending).
 * @param {string} text - Testo da mostrare.
 */
function updateSlotVisual(el, className, text) {
  el.classList.remove("free", "taken", "mine", "pending");
  el.classList.add(className);
  const span = el.querySelector(".status-text");
  if (span) span.innerText = text;
}
