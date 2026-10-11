export function captchaTokenExpiryMs(
  token: string | null | undefined
): number | null {
  if (!token) return null;
  const payload = token.split(".")[1];
  if (!payload) return null;
  try {
    const normalized = payload.replace(/-/g, "+").replace(/_/g, "/");
    const padded = normalized + "=".repeat((4 - (normalized.length % 4)) % 4);
    const json = JSON.parse(atob(padded)) as { exp?: unknown };
    return typeof json.exp === "number" ? json.exp * 1000 : null;
  } catch {
    return null;
  }
}

export function isCaptchaTokenExpired(
  token: string | null | undefined,
  nowMs: number = Date.now()
): boolean {
  const expiryMs = captchaTokenExpiryMs(token);
  return expiryMs != null && nowMs >= expiryMs;
}
