/**
 * Git commit / ref baked in at Vite build time.
 * Shown when VITE_NODE_ENV is not production (.env.development vs .env.production).
 */

export function isProductionBuild(nodeEnv: string | undefined): boolean {
  return (nodeEnv ?? "").trim().toLowerCase() === "production";
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
  nodeEnv: string | undefined
): string | null {
  if (isProductionBuild(nodeEnv)) {
    return null;
  }
  return formatBuildInfoLabel(commit, ref);
}
