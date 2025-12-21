/**
 * PUBLIC/JS/APP.JS
 * Fix Cancellazione + Gestione Ritardi
 */

let currentSlotElement = null;
let currentLockId = null;

document.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);
  const currentDate =
    urlParams.get("date") || new Date().toISOString().split("T")[0];

  fetchPrenotazioni(currentDate);
  setInterval(() => fetchPrenotazioni(currentDate), 3000);

  // Listener chiusura modali
  document.querySelectorAll(".modal-overlay").forEach((modal) => {
    modal.addEventListener("click", (e) => {
      if (e.target.classList.contains("modal-overlay")) {
        if (modal.id === "bookingModal") handleCloseModal();
        if (modal.id === "delayModal") closeDelayModal();
      }
    });
  });
});

async function prenotaSlot(element) {
  currentSlotElement = element;
  const machineId = element.dataset.machine;
  const date = element.dataset.date;
  const hour = element.dataset.hour;
  const timeLabel =
    element.querySelector(".time-label")?.innerText || hour + ":00";

  if (element.classList.contains("past")) return;

  // 1. SLOT OCCUPATO
  if (
    element.classList.contains("taken") ||
    element.classList.contains("pending")
  ) {
    let msg = element.classList.contains("pending")
      ? t("status_pending")
      : `${t("status_taken")} (${element.dataset.username || "..."})`;
    openModal(t("msg_info"), msg, "info");
    return;
  }

  // 2. CANCELLAZIONE (FIX QUI)
  if (element.classList.contains("mine")) {
    // PRENDIAMO L'ID DAL DATASET
    currentLockId = element.dataset.idprenotazione;

    if (!currentLockId) {
      // Se per qualche motivo manca, ricarichiamo i dati
      console.error("ID Prenotazione mancante nel DOM");
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

  // 3. NUOVA PRENOTAZIONE
  element.style.opacity = "0.5";
  try {
    const response = await fetch(BASE_URL + "/api/lock", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `idmacchina=${machineId}&data=${date}&ora=${hour}`,
    });
    const result = await response.json();
    element.style.opacity = "1";

    if (result.success) {
      currentLockId = result.lock_id;
      updateSlotVisual(element, "pending", t("status_pending"));
      openModal(
        t("modal_confirm_title"),
        `${t("modal_booking_msg")} <b>${timeLabel}</b>.<br>${t(
          "btn_confirm"
        )}?`,
        "confirm",
        confirmBooking
      );
    } else {
      openModal(t("msg_info"), result.message, "info");
      fetchPrenotazioni(date);
    }
  } catch (e) {
    element.style.opacity = "1";
    openModal(t("msg_error"), t("err_server"), "info");
  }
}

async function confirmBooking() {
  const btn = document.querySelector(".btn-confirm");
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

    // --- MODIFICA DEBUG INIZIO ---
    const textRaw = await response.text(); // Leggiamo la risposta grezza
    // console.log("Risposta Server:", textRaw); // Guardala nella Console (F12)

    let result;
    try {
      result = JSON.parse(textRaw); // Proviamo a convertirla
    } catch (e) {
      throw new Error("Il server non ha risposto con JSON valido: " + textRaw);
    }
    // --- MODIFICA DEBUG FINE ---

    if (result.success) {
      if (currentSlotElement) {
        updateSlotVisual(currentSlotElement, "mine", t("status_mine"));
        currentSlotElement.dataset.idprenotazione = currentLockId;
      }
      currentLockId = null;
      closeModalRaw();
      if (currentSlotElement)
        setTimeout(
          () => fetchPrenotazioni(currentSlotElement.dataset.date),
          500
        );
    } else {
      closeModalRaw();
      openModal(t("msg_info"), result.message, "info");
      if (currentSlotElement)
        fetchPrenotazioni(currentSlotElement.dataset.date);
    }
  } catch (e) {
    console.error("Errore fetch:", e); // Vedi l'errore specifico
    closeModalRaw();
    // Mostra l'errore tecnico nell'alert invece di "Errore Rete" generico
    openModal(t("msg_error"), e.message, "error");
  }
}

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
      if (currentSlotElement) {
        updateSlotVisual(currentSlotElement, "free", t("status_free"));
        delete currentSlotElement.dataset.idprenotazione;
      }
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

function openDelayModal(id, name, currentDelay) {
  document.getElementById("delayMachineId").value = id;
  document.getElementById("delayInput").value = currentDelay;
  if (name) document.getElementById("delayMachineName").innerText = name;
  document.getElementById("delayModal").classList.add("open");
}

function closeDelayModal() {
  document.getElementById("delayModal").classList.remove("open");
}

function adjustDelay(delta) {
  const input = document.getElementById("delayInput");
  let val = parseInt(input.value) || 0;
  input.value = Math.max(0, val + delta);
}

async function saveDelay() {
  const id = document.getElementById("delayMachineId").value;
  const minutes = document.getElementById("delayInput").value;
  const btn = document.querySelector(
    '#delayModal button[onclick="saveDelay()"]'
  );

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
      fetchPrenotazioni(
        urlParams.get("date") || new Date().toISOString().split("T")[0]
      );
    } else {
      alert(result.message);
    }
  } catch (e) {
    console.error(e);
  } finally {
    btn.innerText = originalText;
    btn.disabled = false;
  }
}

// --- CORE UTILS ---

function handleCloseModal() {
  const modal = document.getElementById("bookingModal");
  if (!modal.classList.contains("open")) return;

  if (currentLockId && !document.querySelector(".btn-confirm")?.disabled) {
    fetch(BASE_URL + "/api/unlock", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `lock_id=${currentLockId}`,
    });
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

  const btnCancel = document.createElement("button");
  btnCancel.className =
    "flex-1 px-4 py-2 rounded bg-zinc-700 text-white font-medium hover:bg-zinc-600 transition-colors";
  btnCancel.innerText = type === "info" ? t("btn_close") : t("btn_cancel");
  btnCancel.onclick = handleCloseModal;
  actions.appendChild(btnCancel);

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

async function fetchPrenotazioni(date) {
  try {
    const response = await fetch(
      `${BASE_URL}/api/read?date=${date}&t=${new Date().getTime()}`
    );
    const data = await response.json();
    if (!data.success) return;

    // 1. Aggiorna Slot
    const dataMap = {};
    data.prenotazioni.forEach((p) => {
      const hourInt = parseInt(p.ora_inizio.split(":")[0]);
      const slotId = `slot_${p.idmacchina}_${p.data_prenotazione}_${hourInt}`;
      dataMap[slotId] = p;
    });

    document.querySelectorAll(".slot").forEach((slotEl) => {
      const p = dataMap[slotEl.id];
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

      if (!slotEl.classList.contains(targetClass)) {
        slotEl.classList.remove("free", "taken", "mine", "pending");
        slotEl.classList.add(targetClass);
      }
      const span = slotEl.querySelector(".status-text");
      if (span && span.innerText !== targetText) span.innerText = targetText;

      const newId = p ? p.idprenotazione : "";
      if (slotEl.dataset.idprenotazione != newId) {
        if (newId) slotEl.dataset.idprenotazione = newId;
        else delete slotEl.dataset.idprenotazione;
      }
      if (p && p.username) slotEl.dataset.username = p.username;
    });

    // 2. Aggiorna Ritardi (FIX CLASSI CSS)
    if (data.macchine) {
      data.macchine.forEach((macchina) => {
        const btn = document.querySelector(
          `#machine-header-${macchina.idmacchina} .machine-delay-btn`
        );
        const span = document.querySelector(
          `#machine-header-${macchina.idmacchina} .delay-val`
        );

        if (btn && span) {
          const rit = parseInt(macchina.ritardo);
          const txt =
            rit > 0 ? `+${rit} ` + t("lbl_delay_min") : t("lbl_delay");

          if (span.innerText !== txt) span.innerText = txt;

          // Definiamo le classi ESATTE usate in dashboard.php
          const yellowClasses = [
            "bg-yellow-500/10",
            "text-yellow-400",
            "border-yellow-500/30",
            "animate-pulse",
          ];
          const grayClasses = [
            "bg-zinc-900/80",
            "text-zinc-500",
            "border-zinc-700",
            "hover:text-gray-300",
            "hover:border-zinc-500",
          ];

          if (rit > 0) {
            // Attiva Giallo, Rimuovi Grigio
            btn.classList.add(...yellowClasses);
            btn.classList.remove(...grayClasses);
          } else {
            // Attiva Grigio, Rimuovi Giallo
            btn.classList.remove(...yellowClasses);
            btn.classList.add(...grayClasses);
          }

          btn.onclick = () => openDelayModal(macchina.idmacchina, "", rit);
        }
      });
    }
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
