/**
 * Chooses local Keycloak login shim vs external dbs-login loader.
 * Host pages load this module instead of hardcoding the CDN script.
 * Lives under src/local-dev/ — local host-page tooling only, not the webcomponent.
 *
 * SHOW_CITIZEN_LOGIN (runtime-config.json or env) disables the host-page chrome
 * on zms-* /buergeransicht shells. Magnolia embeds never include these shells.
 *
 * Keycloak / BayernID attributes stay on <dbs-login> from the built HTML
 * (VITE_KC_*). Do not overwrite them from gateway SSO_* — that is a different client.
 */

type RuntimeConfig = {
  showCitizenLogin?: unknown;
  SHOW_CITIZEN_LOGIN?: unknown;
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
      signal: AbortSignal.timeout(2000),
    });
    if (!response.ok) {
      return undefined;
    }
    return (await response.json()) as RuntimeConfig;
  } catch {
    // Missing / slow / older images — fall back to build-time env. Never block CDN.
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

async function bootstrapLogin(): Promise<void> {
  const runtime = await readRuntimeConfig();

  if (!shouldShowCitizenLogin(runtime)) {
    removeLoginHostChrome();
    return;
  }

  const useLocal =
    String(import.meta.env.VITE_USE_LOCAL_CITIZEN_LOGIN || "").toLowerCase() ===
    "true";

  if (useLocal) {
    await import("./local-dbs-login");
    return;
  }

  // CDN dbs-login (BayernID). Uses kc-* from built HTML / VITE_KC_*.
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
