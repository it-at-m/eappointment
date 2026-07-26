/**
 * Chooses local Keycloak login shim vs external dbs-login loader.
 * Host pages load this module instead of hardcoding the CDN script.
 * Lives under src/local-dev/ — local host-page tooling only, not the webcomponent.
 *
 * SHOW_CITIZEN_LOGIN (runtime-config.json or env) disables the host-page chrome
 * on zms-* /buergeransicht shells. Magnolia embeds never include these shells.
 *
 * Deployed host pages also read kcUrl / kcRealm / kcClientId from runtime-config.json
 * so login matches the gateway SSO_* for that environment (avoids 401 after login).
 */

type RuntimeConfig = {
  showCitizenLogin?: unknown;
  SHOW_CITIZEN_LOGIN?: unknown;
  kcUrl?: string;
  kcRealm?: string;
  kcClientId?: string;
};

function parseBool(value: unknown, fallback: boolean): boolean {
  if (typeof value === "boolean") {
    return value;
  }
  if (typeof value === "string") {
    const normalized = value.toLowerCase();
    if (normalized === "true" || normalized === "1") {
      return true;
    }
    if (normalized === "false" || normalized === "0") {
      return false;
    }
  }
  return fallback;
}

async function readRuntimeConfig(): Promise<RuntimeConfig | undefined> {
  try {
    const response = await fetch("./runtime-config.json", {
      cache: "no-store",
    });
    if (!response.ok) {
      return undefined;
    }
    return (await response.json()) as RuntimeConfig;
  } catch {
    // Missing in local Vite / older images — fall back to build-time env / HTML attrs.
  }
  return undefined;
}

function shouldShowCitizenLogin(runtime: RuntimeConfig | undefined): boolean {
  if (runtime?.showCitizenLogin !== undefined) {
    return parseBool(runtime.showCitizenLogin, true);
  }
  if (runtime?.SHOW_CITIZEN_LOGIN !== undefined) {
    return parseBool(runtime.SHOW_CITIZEN_LOGIN, true);
  }

  return parseBool(
    import.meta.env.SHOW_CITIZEN_LOGIN ??
      import.meta.env.VITE_SHOW_CITIZEN_LOGIN,
    true
  );
}

function removeLoginHostChrome(): void {
  document.querySelector("dbs-login")?.remove();
}

/** Align <dbs-login> with gateway SSO for this environment before the CDN upgrades it. */
function applyKeycloakAttributes(runtime: RuntimeConfig | undefined): void {
  const host = document.querySelector("dbs-login");
  if (!host) {
    return;
  }

  const kcUrl = runtime?.kcUrl || import.meta.env.VITE_KC_URL;
  const kcRealm = runtime?.kcRealm || import.meta.env.VITE_KC_REALM;
  const kcClientId = runtime?.kcClientId || import.meta.env.VITE_KC_CLIENT_ID;

  if (kcUrl) {
    host.setAttribute("kc-url", kcUrl);
  }
  if (kcRealm) {
    host.setAttribute("kc-realm", kcRealm);
  }
  if (kcClientId) {
    host.setAttribute("kc-client-id", kcClientId);
  }
}

async function bootstrapLogin(): Promise<void> {
  const runtime = await readRuntimeConfig();

  if (!shouldShowCitizenLogin(runtime)) {
    removeLoginHostChrome();
    return;
  }

  applyKeycloakAttributes(runtime);

  const useLocal =
    String(import.meta.env.VITE_USE_LOCAL_CITIZEN_LOGIN || "").toLowerCase() ===
    "true";

  if (useLocal) {
    await import("./local-dbs-login");
    return;
  }

  const loaderUrl = import.meta.env.VITE_DBS_LOGIN_LOADER_URL;
  if (!loaderUrl) {
    console.warn(
      "[bootstrap-login] No VITE_DBS_LOGIN_LOADER_URL and local login disabled."
    );
    return;
  }

  await new Promise<void>((resolve, reject) => {
    const script = document.createElement("script");
    script.src = loaderUrl;
    script.async = true;
    script.onload = () => resolve();
    script.onerror = () =>
      reject(new Error(`Failed to load dbs-login loader: ${loaderUrl}`));
    document.head.appendChild(script);
  });
}

void bootstrapLogin().catch((err) => {
  console.error("[bootstrap-login]", err);
});
