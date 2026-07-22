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
 * Minimal host chrome so local Keycloak login mirrors the CDN dbs-login button
 * on non-embedded pages (localhost and zms host /buergeransicht paths).
 * Uses MDE `m-button` classes (same as muc-button) from the host page stylesheet.
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
  host.setAttribute("style", "position:fixed;top:12px;left:12px;z-index:10000");

  const button = document.createElement("button");
  button.type = "button";
  // Match muc-button default (primary) / secondary when signed in (Abmelden).
  button.className = loggedIn
    ? "m-button m-button--secondary"
    : "m-button m-button--primary";
  button.textContent = loggedIn
    ? name
      ? `Abmelden (${name})`
      : "Abmelden"
    : "Mein Bereich";
  if (loggedIn && name) {
    button.setAttribute("aria-label", `Abmelden, angemeldet als ${name}`);
  }

  button.addEventListener("click", () => {
    if (loggedIn) {
      clearSession();
      renderHostChrome(config);
      return;
    }
    void startLogin(config);
  });

  host.appendChild(button);
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
