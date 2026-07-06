const listEl = document.getElementById("list");
const detailEl = document.getElementById("detail");
const statusEl = document.getElementById("status");
const autoRefreshEl = document.getElementById("autoRefresh");
let mails = [];
let timer = null;

function formatTimestamp(value) {
  let ms = Number(value);
  if (!Number.isFinite(ms)) {
    return String(value ?? "");
  }
  // ZMS API timestamps are Unix seconds, not milliseconds.
  if (ms < 1e12) {
    ms *= 1000;
  }
  return new Date(ms).toLocaleString();
}

function recipient(mail) {
  return (
    mail?.client?.email ||
    mail?.process?.clients?.[0]?.email ||
    mail?.process?.clients?.[0]?.familyName ||
    "—"
  );
}

function multipartPart(mail, mime) {
  return (mail?.multipart || []).find((part) => part.mime === mime);
}

function renderList() {
  if (!mails.length) {
    listEl.innerHTML =
      '<div class="empty">No mails in the queue. Trigger a booking or confirmation to populate it.</div>';
    return;
  }

  listEl.innerHTML = mails
    .map(
      (mail) => `
      <article class="mail-card" data-id="${mail.id}">
        <h3>${escapeHtml(mail.subject || "(no subject)")}</h3>
        <div class="sub">#${mail.id} · ${escapeHtml(recipient(mail))} · ${formatTimestamp(mail.createTimestamp)}</div>
      </article>`,
    )
    .join("");

  listEl.querySelectorAll(".mail-card").forEach((card) => {
    card.addEventListener("click", () => openDetail(card.dataset.id));
  });
}

function normalizeMailHtml(html) {
  if (!html) {
    return html;
  }
  return html.replace(/href="(localhost:\d+)/gi, 'href="http://$1');
}

function openDetail(id) {
  const mail = mails.find((entry) => String(entry.id) === String(id));
  if (!mail) {
    return;
  }

  listEl.classList.add("hidden");
  detailEl.classList.remove("hidden");
  document.getElementById("detailTitle").textContent =
    mail.subject || `Mail #${mail.id}`;

  const processId = mail?.process?.id;
  document.getElementById("meta").innerHTML = `
    <div><strong>ID:</strong> ${mail.id}</div>
    <div><strong>To:</strong> ${escapeHtml(recipient(mail))}</div>
    <div><strong>Created:</strong> ${formatTimestamp(mail.createTimestamp)}</div>
    ${processId ? `<div><strong>Process:</strong> ${processId}</div>` : ""}`;

  const htmlPart = multipartPart(mail, "text/html");
  const plainPart = multipartPart(mail, "text/plain");
  document.getElementById("htmlFrame").srcdoc =
    normalizeMailHtml(htmlPart?.content) || "<p>No HTML part.</p>";
  document.getElementById("plainText").textContent =
    plainPart?.content || "No plain text part.";
  document.getElementById("rawJson").textContent = JSON.stringify(mail, null, 2);
  setActiveTab("html");
}

function closeDetail() {
  detailEl.classList.add("hidden");
  listEl.classList.remove("hidden");
}

function setActiveTab(name) {
  document.querySelectorAll(".tab").forEach((tab) => {
    tab.classList.toggle("active", tab.dataset.tab === name);
  });
  for (const panel of ["html", "plain", "raw"]) {
    document
      .getElementById(`panel-${panel}`)
      .classList.toggle("hidden", panel !== name);
  }
}

function escapeHtml(value) {
  return String(value)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;");
}

async function loadMails() {
  statusEl.textContent = "Loading…";
  try {
    const response = await fetch("/api/mails", { credentials: "same-origin" });
    const payload = await response.json();
    if (!response.ok || payload?.meta?.error) {
      const apiMessage =
        payload?.meta?.message ||
        payload?.message ||
        payload?.meta?.exception ||
        `HTTP ${response.status}`;
      throw new Error(apiMessage);
    }
    mails = Array.isArray(payload.data) ? payload.data : [];
    mails.sort(
      (a, b) => Number(b.createTimestamp || 0) - Number(a.createTimestamp || 0),
    );
    renderList();
    statusEl.textContent = `${mails.length} mail(s) · ${new Date().toLocaleTimeString()}`;
  } catch (error) {
    statusEl.textContent = `Error: ${error.message}`;
  }
}

function scheduleAutoRefresh() {
  if (timer) {
    clearInterval(timer);
    timer = null;
  }
  if (autoRefreshEl.checked && detailEl.classList.contains("hidden")) {
    timer = setInterval(loadMails, 5000);
  }
}

document.getElementById("refresh").addEventListener("click", loadMails);
document.getElementById("closeDetail").addEventListener("click", closeDetail);
autoRefreshEl.addEventListener("change", scheduleAutoRefresh);
document.querySelectorAll(".tab").forEach((tab) => {
  tab.addEventListener("click", () => setActiveTab(tab.dataset.tab));
});

loadMails();
scheduleAutoRefresh();
