/**
 * Git commit / ref baked in at Vite build time.
 *
 * Visibility is env-based, never host-based:
 * - local `npm run dev` uses VITE_NODE_ENV=development
 * - deployed images read ZMS_ENV from the same runtime-config.json
 *   as SHOW_CITIZEN_LOGIN
 *
 * Magnolia embeds (e.g. lhm-i) run the WC on another origin. Resolve the
 * JSON from the script URL (`…/buergeransicht/src/entry-*.js`), not `./`
 * on the host page — that would hit Magnolia and miss ZMS_ENV.
 */
export function runtimeConfigUrl(moduleUrl: string): string {
  try {
    return new URL("../runtime-config.json", moduleUrl).href;
  } catch {
    return "./runtime-config.json";
  }
}

export function isProductionBuild(nodeEnv: string | undefined): boolean {
  return (nodeEnv ?? "").trim().toLowerCase() === "production";
}

export function isProductionZmsEnv(zmsEnv: string | undefined): boolean {
  const env = (zmsEnv ?? "").trim().toLowerCase();
  return env === "prod" || env === "production";
}

export function shouldShowBuildInfo(
  viteNodeEnv: string | undefined,
  zmsEnv: string | undefined
): boolean {
  const runtimeEnv = (zmsEnv ?? "").trim();
  if (runtimeEnv) {
    return !isProductionZmsEnv(runtimeEnv);
  }
  return !isProductionBuild(viteNodeEnv);
}

export function formatBuildInfoLabel(
  commit: string | undefined,
  ref: string | undefined
): string | null {
  const sha = (commit ?? "").trim();
  const name = (ref ?? "").trim();
  if (!sha && !name) {
    return null;
  }
  if (sha && name) {
    return `${sha} · ${name}`;
  }
  return sha || name;
}

export function resolveBuildInfoLabel(
  commit: string | undefined,
  ref: string | undefined,
  viteNodeEnv: string | undefined,
  zmsEnv: string | undefined
): string | null {
  if (!shouldShowBuildInfo(viteNodeEnv, zmsEnv)) {
    return null;
  }
  return formatBuildInfoLabel(commit, ref);
}
