/**
 * PUBLIC/JS/APP.JS
 * Versione Corretta e Definitiva
 */

// Variabili globali
let currentSlotElement = null;
let currentLockId = null;

document.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);
  const currentDate =
    urlParams.get("date") || new Date().toISOString().split("T")[0];

  // Caricamento iniziale
  fetchPrenotazioni(currentDate);

  // Polling ogni 3 secondi per vedere aggiornamenti in tempo reale
  setInterval(() => fetchPrenotazioni(currentDate), 3000);

  // Gestione chiusura modal cliccando fuori
  const modal = document.getElementById("bookingModal");
  if (modal) {
    modal.addEventListener("click", (e) => {
      if (e.target.classList.contains("modal-overlay")) handleCloseModal();
    });
  }
});

/**
 * Gestisce il click sullo slot
 */
async function prenotaSlot(element) {
  currentSlotElement = element;

  const machineId = element.dataset.machine;
  const date = element.dataset.date;
  const hour = element.dataset.hour;

  // Recupero sicuro dell'orario
  const timeEl = element.querySelector(".time-label");
  const timeLabel = timeEl ? timeEl.innerText : hour + ":00";

  // 1. SLOT PASSATO
  if (element.classList.contains("past")) return;

  // 2. SLOT OCCUPATO (Rosso) o IN ATTESA (Giallo)
  if (
    element.classList.contains("taken") ||
    element.classList.contains("pending")
  ) {
    let msg = element.classList.contains("pending")
      ? t("status_pending")
      : `${t("status_taken")} (${element.dataset.username || "..."})`;

    openModal(t("status_taken"), msg, "info");
    return;
  }

  // 3. SLOT MIO (Azzurro) -> CANCELLAZIONE
  if (element.classList.contains("mine")) {
    currentLockId = element.dataset.idprenotazione;
    openModal(
      t("modal_cancel_title"),
      `${t("modal_cancel_msg")} <b>${timeLabel}</b>?`,
      "danger",
      confirmCancellation
    );
    return;
  }

  // 4. SLOT LIBERO -> NUOVA PRENOTAZIONE (Lock)
  element.style.opacity = "0.5"; // Feedback visivo immediato

  try {
    const response = await fetch(BASE_URL + "/api/lock", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `idmacchina=${machineId}&data=${date}&ora=${hour}`,
    });

    // Definiamo result QUI, dopo la chiamata
    const result = await response.json();

    element.style.opacity = "1";

    if (result.success) {
      // Lock riuscito!
      currentLockId = result.lock_id;

      // Visualizza stato "In corso" (Giallo)
      updateSlotVisual(element, "pending", t("status_pending"));

      // Apre modale conferma
      openModal(
        t("modal_confirm_title"),
        `${t("modal_booking_msg")} <b>${timeLabel}</b>.<br>${t(
          "btn_confirm"
        )}?`,
        "confirm",
        confirmBooking
      );
    } else {
      // Errore (es. Limite ore raggiunto, Già preso, etc.)
      openModal("Info", result.message, "info");
      fetchPrenotazioni(date);
    }
  } catch (e) {
    console.error(e);
    element.style.opacity = "1";
    openModal("Error", "Server Error", "info");
  }
}

/**
 * Conferma finale della prenotazione
 */
async function confirmBooking() {
  const btnConfirm = document.querySelector(".btn-confirm");
  if (btnConfirm) {
    btnConfirm.disabled = true;
    btnConfirm.innerText = "...";
  }

  try {
    const response = await fetch(BASE_URL + "/api/prenota", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `lock_id=${currentLockId}`,
    });
    const result = await response.json();

    if (result.success) {
      // SUCCESSO
      if (currentSlotElement) {
        updateSlotVisual(currentSlotElement, "mine", t("status_mine"));
        currentSlotElement.dataset.idprenotazione = currentLockId;
      }

      currentLockId = null;
      closeModalRaw();

      // Refresh sicurezza
      if (currentSlotElement) {
        setTimeout(
          () => fetchPrenotazioni(currentSlotElement.dataset.date),
          500
        );
      }
    } else {
      // ERRORE
      closeModalRaw();
      openModal("Info", result.message, "info");
      if (currentSlotElement)
        fetchPrenotazioni(currentSlotElement.dataset.date);
    }
  } catch (e) {
    closeModalRaw();
    openModal("Error", "Network Error", "info");
  }
}

/**
 * Cancellazione Prenotazione
 */
async function confirmCancellation() {
  try {
    const response = await fetch(BASE_URL + "/api/cancella", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `idprenotazione=${currentLockId}`,
    });
    const result = await response.json();

    if (result.success) {
      closeModalRaw();
      if (currentSlotElement) {
        updateSlotVisual(currentSlotElement, "free", t("status_free"));
        delete currentSlotElement.dataset.idprenotazione;
      }
      fetchPrenotazioni(currentSlotElement.dataset.date);
    } else {
      closeModalRaw();
      openModal("Error", result.message, "info");
    }
  } catch (e) {
    closeModalRaw();
    openModal("Error", "Network Error", "info");
  }
}

// --- GESTIONE MODAL ---

function handleCloseModal() {
  const modal = document.getElementById("bookingModal");
  if (!modal.classList.contains("open")) return;

  // Unlock se chiudo senza confermare
  if (currentLockId && !document.querySelector(".btn-confirm")?.disabled) {
    fetch(BASE_URL + "/api/unlock", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `lock_id=${currentLockId}`,
    });
    // Reset visivo
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

function closeModalRaw() {
  document.getElementById("bookingModal").classList.remove("open");
}

function openModal(title, body, type, callback) {
  document.getElementById("modalTitle").innerText = title;
  document.getElementById("modalBody").innerHTML = body;

  const actions = document.getElementById("modalActions");
  actions.innerHTML = "";

  // Tasto Annulla
  const btnCancel = document.createElement("button");
  btnCancel.className =
    "flex-1 px-4 py-2 rounded bg-zinc-700 text-white font-medium hover:bg-zinc-600 transition-colors";
  btnCancel.innerText = type === "info" ? t("btn_close") : t("btn_cancel");
  btnCancel.onclick = handleCloseModal;
  actions.appendChild(btnCancel);

  // Tasto Azione
  if (type !== "info" && callback) {
    const btnOk = document.createElement("button");
    const colorClass =
      type === "danger"
        ? "bg-red-600 hover:bg-red-500 shadow-red-900/20"
        : "bg-accent hover:bg-blue-600 shadow-blue-900/20";

    btnOk.className = `flex-1 px-4 py-2 rounded font-bold text-white transition-colors shadow-lg btn-confirm ${colorClass}`;
    btnOk.innerText = type === "danger" ? t("btn_delete") : t("btn_confirm");
    btnOk.onclick = callback;
    actions.appendChild(btnOk);
  }

  document.getElementById("bookingModal").classList.add("open");
}

// --- UTILITIES GRIGLIA ---

async function fetchPrenotazioni(date) {
  try {
    const timestamp = new Date().getTime();
    const response = await fetch(
      `${BASE_URL}/api/read?date=${date}&t=${timestamp}`
    );
    const data = await response.json();

    if (!data.success) return;

    const dataMap = {};
    data.prenotazioni.forEach((p) => {
      const hourInt = parseInt(p.ora_inizio.split(":")[0]);
      const slotId = `slot_${p.idmacchina}_${p.data_prenotazione}_${hourInt}`;
      dataMap[slotId] = p;
    });

    const allSlots = document.querySelectorAll(".slot");
    allSlots.forEach((slotEl) => {
      // if (slotEl.classList.contains("past")) return;
      
      const p = dataMap[slotEl.id];

      // Stato desiderato (con traduzioni)
      let targetClass = "free";
      let targetText = t("status_free");

      if (p) {
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
        }
      }

      // Aggiorna DOM solo se diverso
      if (!slotEl.classList.contains(targetClass)) {
        slotEl.classList.remove("free", "taken", "mine", "pending");
        slotEl.classList.add(targetClass);
      }
      const span = slotEl.querySelector(".status-text");
      if (span && span.innerText !== targetText) span.innerText = targetText;

      // Dataset
      const newId = p ? p.idprenotazione : "";
      const newName = p ? p.username : "";

      if (slotEl.dataset.idprenotazione != newId) {
        if (newId) slotEl.dataset.idprenotazione = newId;
        else delete slotEl.dataset.idprenotazione;
      }
      if (slotEl.dataset.username != newName) {
        if (newName) slotEl.dataset.username = newName;
        else delete slotEl.dataset.username;
      }
    });
  } catch (e) {
    console.error(e);
  }
}

function updateSlotVisual(el, className, text) {
  el.classList.remove("free", "taken", "mine", "pending");
  el.classList.add(className);
  const span = el.querySelector(".status-text");
  if (span) span.innerText = text;
}
