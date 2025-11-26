/**
 * Returns the current Unix time in seconds.
 */
export function nowUnixSeconds(): number {
  return Math.floor(Date.now() / 1000);
}

/**
 * Normalizes a timestamp to Unix seconds.
 * @returns second timestamp or null for invalid input
 */
export function normalizeToUnixSeconds(raw: unknown): number | null {
  const numericTimestamp = Number(raw);
  if (!Number.isFinite(numericTimestamp)) return null;
  // Current ms epoch values ~1.6e12–1.9e12
  if (numericTimestamp > 1e12) {
    return Math.floor(numericTimestamp / 1000);
  }
  return Math.floor(numericTimestamp);
}

/**
 * Checks whether a timestamp is in the past (>= now).
 */
export function isExpired(
  ts: unknown,
  nowSec: number = nowUnixSeconds()
): boolean {
  const sec = normalizeToUnixSeconds(ts);
  return sec !== null && nowSec >= sec;
}
