/**
 * Chooses local Keycloak login shim vs external dbs-login loader.
 * Host pages load this module instead of hardcoding the CDN script.
 * Lives under src/local-dev/ — local host-page tooling only, not the webcomponent.
 *
 * SHOW_CITIZEN_LOGIN (runtime-config.json or env) disables the host-page chrome
 * on zms-* /buergeransicht shells. Magnolia embeds never include these shells.
 */

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

async function readRuntimeShowCitizenLogin(): Promise<boolean | undefined> {
  try {
    const response = await fetch("./runtime-config.json", {
      cache: "no-store",
    });
    if (!response.ok) {
      return undefined;
    }
    const data = (await response.json()) as {
      showCitizenLogin?: unknown;
      SHOW_CITIZEN_LOGIN?: unknown;
    };
    if (data.showCitizenLogin !== undefined) {
      return parseBool(data.showCitizenLogin, true);
    }
    if (data.SHOW_CITIZEN_LOGIN !== undefined) {
      return parseBool(data.SHOW_CITIZEN_LOGIN, true);
    }
  } catch {
    // Missing in local Vite / older images — fall back to build-time env.
  }
  return undefined;
}

async function shouldShowCitizenLogin(): Promise<boolean> {
  const runtime = await readRuntimeShowCitizenLogin();
  if (runtime !== undefined) {
    return runtime;
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
  if (!(await shouldShowCitizenLogin())) {
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
