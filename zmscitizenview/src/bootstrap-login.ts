/**
 * Chooses local Keycloak login shim vs external dbs-login loader.
 * Host pages load this module instead of hardcoding the CDN script.
 */
async function bootstrapLogin(): Promise<void> {
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
