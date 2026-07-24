/**
 * Local stand-in for the external dbs-login webcomponent.
 * Listens for `authorization-request`, runs OIDC authorization-code + PKCE
 * against local Keycloak, then emits `authorization-event` (same contract).
 *
 * Enabled when VITE_USE_LOCAL_CITIZEN_LOGIN=true (see .env.development).
 * Lives under src/local-dev/ — local host-page tooling only, not the webcomponent.
 *
 * Tokens are kept in localStorage so overview/detail/slider pages stay logged
 * in across tabs on the same origin (sessionStorage is per-tab only).
 */
import { KEYCLOAK_AUTH_LEVEL1 } from "@/types/AuthorizationEventDetails";

const AUTH_REQUEST = "authorization-request";
const AUTH_EVENT = "authorization-event";
const STORAGE_STATE = "zms-local-oidc-state";
const STORAGE_VERIFIER = "zms-local-oidc-verifier";
const STORAGE_SESSION = "zms-local-oidc-session";

type LoginConfig = {
  kcUrl: string;
  realm: string;
  clientId: string;
};

type StoredSession = {
  accessToken: string;
  idToken?: string;
};

function readConfig(): LoginConfig {
  const el = document.querySelector("dbs-login");
  return {
    kcUrl: (
      el?.getAttribute("kc-url") ||
      import.meta.env.VITE_KC_URL ||
      ""
    ).replace(/\/$/, ""),
    realm:
      el?.getAttribute("kc-realm") || import.meta.env.VITE_KC_REALM || "zms",
    clientId:
      el?.getAttribute("kc-client-id") ||
      import.meta.env.VITE_KC_CLIENT_ID ||
      "dbs-fragments",
  };
}

function redirectUri(): string {
  return window.location.origin + window.location.pathname;
}

function base64UrlEncode(buffer: ArrayBuffer): string {
  const bytes = new Uint8Array(buffer);
  let binary = "";
  bytes.forEach((b) => {
    binary += String.fromCharCode(b);
  });
  return btoa(binary)
    .replace(/\+/g, "-")
    .replace(/\//g, "_")
    .replace(/=+$/, "");
}

function randomString(length = 64): string {
  const chars =
    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~";
  const values = crypto.getRandomValues(new Uint8Array(length));
  return Array.from(values, (v) => chars[v % chars.length]).join("");
}

async function pkceChallenge(verifier: string): Promise<string> {
  const digest = await crypto.subtle.digest(
    "SHA-256",
    new TextEncoder().encode(verifier)
  );
  return base64UrlEncode(digest);
}

function parseJwt(token: string): Record<string, unknown> {
  const base64Url = token.split(".")[1];
  const base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/");
  return JSON.parse(
    decodeURIComponent(
      atob(base64)
        .split("")
        .map((c) => "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2))
        .join("")
    )
  );
}

function isTokenUsable(accessToken: string): boolean {
  try {
    const claims = parseJwt(accessToken);
    const exp = Number(claims.exp);
    if (!Number.isFinite(exp)) {
      return false;
    }
    // 30s skew so we do not restore a token about to expire
    return exp * 1000 > Date.now() + 30_000;
  } catch {
    return false;
  }
}

function saveSession(accessToken: string, idToken?: string): void {
  const session: StoredSession = { accessToken, idToken };
  localStorage.setItem(STORAGE_SESSION, JSON.stringify(session));
  // Drop any leftover per-tab copy from earlier builds.
  sessionStorage.removeItem(STORAGE_SESSION);
}

function loadSession(): StoredSession | null {
  const raw =
    localStorage.getItem(STORAGE_SESSION) ||
    sessionStorage.getItem(STORAGE_SESSION);
  if (!raw) {
    return null;
  }
  try {
    const parsed = JSON.parse(raw) as StoredSession;
    if (!parsed.accessToken || !isTokenUsable(parsed.accessToken)) {
      localStorage.removeItem(STORAGE_SESSION);
      sessionStorage.removeItem(STORAGE_SESSION);
      return null;
    }
    // Migrate sessionStorage → localStorage once.
    localStorage.setItem(STORAGE_SESSION, raw);
    sessionStorage.removeItem(STORAGE_SESSION);
    return parsed;
  } catch {
    localStorage.removeItem(STORAGE_SESSION);
    sessionStorage.removeItem(STORAGE_SESSION);
    return null;
  }
}

function emitAuthEvent(accessToken: string, idToken?: string): void {
  const claims = parseJwt(idToken || accessToken);
  const given = String(claims.given_name || claims.preferred_username || "");
  const family = String(claims.family_name || "");
  const email = String(claims.email || "");
  const name = [given, family].filter(Boolean).join(" ") || given;

  document.dispatchEvent(
    new CustomEvent(AUTH_EVENT, {
      detail: {
        buergerName: name,
        buergerMail: email,
        loginProvider: "keycloak",
        trustLevel: KEYCLOAK_AUTH_LEVEL1,
        accessToken,
      },
    })
  );
}

/**
 * Vue registers `authorization-event` in onMounted; this module may finish earlier.
 * Emit now and again shortly after so late subscribers still receive the session.
 */
function publishSession(accessToken: string, idToken?: string): void {
  saveSession(accessToken, idToken);
  emitAuthEvent(accessToken, idToken);
  window.setTimeout(() => emitAuthEvent(accessToken, idToken), 0);
  window.setTimeout(() => emitAuthEvent(accessToken, idToken), 300);
}

async function startLogin(config: LoginConfig): Promise<void> {
  if (!config.kcUrl || !config.realm || !config.clientId) {
    console.error(
      "[local-dbs-login] Missing Keycloak config (kc-url / realm / client-id)."
    );
    return;
  }

  const verifier = randomString(64);
  const state = randomString(32);
  const challenge = await pkceChallenge(verifier);
  sessionStorage.setItem(STORAGE_VERIFIER, verifier);
  sessionStorage.setItem(STORAGE_STATE, state);

  const url = new URL(
    `${config.kcUrl}/realms/${config.realm}/protocol/openid-connect/auth`
  );
  url.searchParams.set("client_id", config.clientId);
  url.searchParams.set("redirect_uri", redirectUri());
  url.searchParams.set("response_type", "code");
  url.searchParams.set("scope", "openid profile email");
  url.searchParams.set("state", state);
  url.searchParams.set("code_challenge", challenge);
  url.searchParams.set("code_challenge_method", "S256");

  window.location.assign(url.toString());
}

async function handleCallback(config: LoginConfig): Promise<boolean> {
  const params = new URLSearchParams(window.location.search);
  const code = params.get("code");
  const state = params.get("state");
  if (!code) {
    return false;
  }

  const expectedState = sessionStorage.getItem(STORAGE_STATE);
  const verifier = sessionStorage.getItem(STORAGE_VERIFIER);
  sessionStorage.removeItem(STORAGE_STATE);
  sessionStorage.removeItem(STORAGE_VERIFIER);

  if (!verifier || !state || state !== expectedState) {
    console.error("[local-dbs-login] OIDC state/verifier mismatch.");
    return true;
  }

  const tokenUrl = `${config.kcUrl}/realms/${config.realm}/protocol/openid-connect/token`;
  const body = new URLSearchParams({
    grant_type: "authorization_code",
    client_id: config.clientId,
    code,
    redirect_uri: redirectUri(),
    code_verifier: verifier,
  });

  const response = await fetch(tokenUrl, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body,
  });

  if (!response.ok) {
    console.error(
      "[local-dbs-login] Token exchange failed:",
      response.status,
      await response.text()
    );
    return true;
  }

  const tokens = (await response.json()) as {
    access_token: string;
    id_token?: string;
  };

  // Drop OIDC query params so a refresh does not re-exchange the code.
  window.history.replaceState(
    {},
    document.title,
    redirectUri() + window.location.hash
  );

  publishSession(tokens.access_token, tokens.id_token);
  return true;
}

function restoreSession(): boolean {
  const session = loadSession();
  if (!session) {
    return false;
  }
  publishSession(session.accessToken, session.idToken);
  return true;
}

function clearSession(): void {
  localStorage.removeItem(STORAGE_SESSION);
  sessionStorage.removeItem(STORAGE_SESSION);
  document.dispatchEvent(new CustomEvent(AUTH_EVENT, { detail: undefined }));
}

function displayNameFromSession(session: StoredSession | null): string {
  if (!session) {
    return "";
  }
  try {
    const claims = parseJwt(session.idToken || session.accessToken);
    const given = String(claims.given_name || claims.preferred_username || "");
    const family = String(claims.family_name || "");
    return [given, family].filter(Boolean).join(" ") || given || "Angemeldet";
  } catch {
    return "Angemeldet";
  }
}

/**
 * muc-icon `user` path from @muenchen/muc-patternlab-vue (muc-icons.svg #icon-user).
 * Inlined because host chrome lives outside the webcomponent shadow DOM sprite.
 */
function createUserIcon(): SVGSVGElement {
  const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
  svg.setAttribute("aria-hidden", "true");
  svg.setAttribute("viewBox", "0 0 32 32");
  svg.setAttribute("class", "icon m-button__icon m-button__icon--before");
  svg.setAttribute(
    "style",
    "width:1.5rem;height:1.5rem;flex-shrink:0;margin-right:.75rem;fill:currentColor"
  );
  svg.innerHTML =
    '<path d="M26.219 23.855l-2.667-4c-0.485-0.72-1.297-1.188-2.219-1.188h-10.667c-0.922 0-1.734 0.468-2.212 1.179l-0.006 0.010-2.667 4c-0.281 0.416-0.448 0.928-0.448 1.48 0 0.46 0.117 0.893 0.322 1.271l-0.007-0.014c0.459 0.844 1.339 1.408 2.351 1.408h16c1.012 0 1.892-0.564 2.344-1.394l0.007-0.014c0.199-0.364 0.316-0.798 0.316-1.259 0-0.551-0.167-1.063-0.454-1.488l0.006 0.010zM8 25.333l2.667-4h10.667l2.667 4z"></path>' +
    '<path d="M16 16c3.314 0 6-2.686 6-6s-2.686-6-6-6-6 2.686-6 6 2.686 6 6 6zM16 6.667c1.841 0 3.333 1.492 3.333 3.333s-1.492 3.333-3.333 3.333-3.333-1.492-3.333-3.333 1.492-3.333 3.333-3.333z"></path>';
  return svg;
}

/** App root: `/` locally, `/buergeransicht/` on zms hosts. */
function citizenviewRootHref(): string {
  const { origin, pathname } = window.location;
  if (pathname.startsWith("/buergeransicht")) {
    return `${origin}/buergeransicht/`;
  }
  return `${origin}/`;
}

/** Host-page URL under the citizenview root (works on localhost and /buergeransicht/). */
function pageHref(file: string): string {
  if (!file || file === "/" || file === "index.html") {
    return citizenviewRootHref();
  }
  return `${citizenviewRootHref()}${file.replace(/^\//, "")}`;
}

function logoutAndReturnHome(): void {
  clearSession();
  window.location.assign(citizenviewRootHref());
}

const MENU_ITEM_STYLE = [
  "display:block",
  "width:100%",
  "padding:.75rem 1rem",
  "border:0",
  "background:transparent",
  "color:#005a9f",
  "font:inherit",
  "text-align:left",
  "cursor:pointer",
  "white-space:nowrap",
  "text-decoration:none",
  "box-sizing:border-box",
].join(";");

function styleMenuItem(el: HTMLElement): void {
  el.setAttribute("style", MENU_ITEM_STYLE);
  el.addEventListener("mouseenter", () => {
    el.style.background = "#e8f1f8";
  });
  el.addEventListener("mouseleave", () => {
    el.style.background = "transparent";
  });
}

function currentPageFile(): string {
  const path = window.location.pathname.replace(/\/$/, "");
  const segment = path.split("/").pop() || "";
  if (!segment || segment === "buergeransicht") {
    return "index.html";
  }
  return segment;
}

type NavLink = { label: string; file: string };

const HOST_NAV_LINKS: NavLink[] = [
  { label: "Terminvereinbarung", file: "index.html" },
  { label: "Terminübersicht", file: "appointment-overview.html" },
  { label: "Termin-Detail", file: "appointment-detail.html" },
  { label: "Termin-Slider", file: "appointment-slider.html" },
];

/**
 * Host chrome for non-embedded pages (localhost and zms host /buergeransicht).
 * Logged out: Anmelden. Logged in: Mein Bereich dropdown with nav + Abmelden.
 */
function renderHostChrome(config: LoginConfig): void {
  const host = document.querySelector("dbs-login");
  if (!host) {
    return;
  }

  const session = loadSession();
  const loggedIn = !!session;
  const name = displayNameFromSession(session);

  host.replaceChildren();
  host.setAttribute(
    "style",
    "position:fixed;top:0;left:0;z-index:10000;display:block"
  );

  const button = document.createElement("button");
  button.type = "button";
  // Same primary MDE style logged in and out (CDN dbs-login does not flip to secondary).
  button.className = "m-button m-button--primary";
  button.appendChild(createUserIcon());
  button.appendChild(
    document.createTextNode(loggedIn ? "Mein Bereich" : "Anmelden")
  );

  if (!loggedIn) {
    button.addEventListener("click", () => {
      void startLogin(config);
    });
    host.appendChild(button);
    return;
  }

  button.setAttribute("aria-haspopup", "true");
  button.setAttribute("aria-expanded", "false");
  button.setAttribute(
    "aria-label",
    name ? `Mein Bereich, angemeldet als ${name}` : "Mein Bereich"
  );

  const menu = document.createElement("div");
  menu.setAttribute("role", "menu");
  menu.hidden = true;
  menu.setAttribute(
    "style",
    [
      "position:absolute",
      "top:100%",
      "left:0",
      "min-width:14rem",
      "margin:0",
      "padding:0",
      "background:#fff",
      "border:1px solid #005a9f",
      "box-shadow:0 2px 8px rgba(0,0,0,.15)",
      "z-index:1",
    ].join(";")
  );

  if (name) {
    const identity = document.createElement("div");
    identity.setAttribute(
      "style",
      "padding:.75rem 1rem;border-bottom:1px solid #dce3e8;font-size:.875rem;color:#3a5368;white-space:nowrap"
    );
    identity.textContent = name;
    menu.appendChild(identity);
  }

  const activeFile = currentPageFile();
  for (const link of HOST_NAV_LINKS) {
    const item = document.createElement("a");
    item.setAttribute("role", "menuitem");
    item.href = pageHref(link.file);
    item.textContent = link.label;
    styleMenuItem(item);
    if (link.file === activeFile) {
      item.setAttribute("aria-current", "page");
      item.style.fontWeight = "700";
      item.style.background = "#e8f1f8";
    }
    menu.appendChild(item);
  }

  const separator = document.createElement("div");
  separator.setAttribute("style", "border-top:1px solid #dce3e8;margin:0");
  separator.setAttribute("role", "separator");
  menu.appendChild(separator);

  const logoutItem = document.createElement("button");
  logoutItem.type = "button";
  logoutItem.setAttribute("role", "menuitem");
  logoutItem.textContent = "Abmelden";
  styleMenuItem(logoutItem);
  logoutItem.addEventListener("click", (event) => {
    event.stopPropagation();
    logoutAndReturnHome();
  });
  menu.appendChild(logoutItem);

  const setOpen = (open: boolean): void => {
    menu.hidden = !open;
    button.setAttribute("aria-expanded", open ? "true" : "false");
  };

  button.addEventListener("click", (event) => {
    event.stopPropagation();
    setOpen(menu.hidden);
  });

  const onDocClick = (event: MouseEvent): void => {
    if (!host.contains(event.target as Node)) {
      setOpen(false);
    }
  };
  document.addEventListener("click", onDocClick);

  host.appendChild(button);
  host.appendChild(menu);
}

function ensureCustomElement(): void {
  if (customElements.get("dbs-login")) {
    return;
  }
  customElements.define(
    "dbs-login",
    class extends HTMLElement {
      // Upgraded by initLocalDbsLogin; chrome is rendered into light DOM.
    }
  );
}

export async function initLocalDbsLogin(): Promise<void> {
  ensureCustomElement();
  const config = readConfig();

  let handledCallback = false;
  try {
    handledCallback = await handleCallback(config);
  } catch (err) {
    console.error("[local-dbs-login] Callback handling failed:", err);
  }

  if (!handledCallback) {
    restoreSession();
  }

  document.addEventListener(AUTH_REQUEST, () => {
    void startLogin(config);
  });

  renderHostChrome(config);

  console.info(
    "[local-dbs-login] Ready — login uses",
    `${config.kcUrl}/realms/${config.realm}`,
    `(client ${config.clientId})`
  );
}

void initLocalDbsLogin();
