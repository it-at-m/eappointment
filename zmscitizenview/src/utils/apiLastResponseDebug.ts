import { ref, Ref } from "vue";

/** Last offices-and-services (or status) fetch — for DEV callout screenshots */
export const lastApiFailureDebug: Ref<string> = ref("");

/** Enough for headers + body preview (Network-tab-style dump) */
const DEBUG_MAX_LEN = 14000;

export function recordApiFailureDebug(info: string): void {
  lastApiFailureDebug.value = info.slice(0, DEBUG_MAX_LEN);
}

/**
 * Build a long debug string similar to what you’d see in DevTools Network
 * (request URL, timing, response status, all headers, body preview).
 * If fetch throws before any Response, there is no status/headers — typical for CORS.
 */
export function formatFetchNetworkDebug(opts: {
  requestUrl: string;
  method?: string;
  startedMs: number;
  response?: Response;
  bodyText?: string;
  err?: unknown;
}): string {
  const lines: string[] = [];
  const elapsed = Math.round(performance.now() - opts.startedMs);

  lines.push("=== Network-style fetch log ===");
  if (typeof window !== "undefined") {
    lines.push(`document.URL: ${window.location.href}`);
    lines.push(`Origin: ${window.location.origin}`);
    lines.push(`navigator.onLine: ${navigator.onLine}`);
    lines.push(`User-Agent: ${navigator.userAgent.slice(0, 160)}`);
  }
  lines.push("");
  lines.push("--- Request ---");
  lines.push(`${opts.method ?? "GET"} ${opts.requestUrl}`);
  lines.push(`mode: cors (default)`);
  lines.push(`elapsed: ${elapsed}ms`);
  lines.push("");

  if (opts.response) {
    const r = opts.response;
    lines.push("--- Response ---");
    lines.push(`status: ${r.status} ${r.statusText}`);
    lines.push(`ok: ${r.ok}`);
    lines.push(`type: ${r.type}`);
    lines.push(`redirected: ${r.redirected}`);
    if (r.url) lines.push(`response.url: ${r.url}`);
    lines.push("");
    lines.push("Response headers:");
    try {
      const headers: string[] = [];
      r.headers.forEach((v, k) => headers.push(`${k}: ${v}`));
      lines.push(headers.length ? headers.join("\n") : "(empty)");
    } catch {
      lines.push("(could not read headers)");
    }
    lines.push("");
    if (opts.bodyText !== undefined) {
      lines.push("Response body (truncated):");
      lines.push(opts.bodyText.slice(0, 6000));
    }
  }

  if (opts.err !== undefined) {
    lines.push("");
    lines.push(
      "--- Thrown before/during read (often CORS — no status line in Network) ---"
    );
    const e = opts.err;
    if (e instanceof Error) {
      lines.push(`name: ${e.name}`);
      lines.push(`message: ${e.message}`);
      if (e.stack) lines.push(`stack:\n${e.stack.slice(0, 2000)}`);
    } else {
      lines.push(String(e));
    }
    lines.push("");
    lines.push(
      "Note: If curl from the same host works but this shows NetworkError, the browser likely blocked cross-origin access (CORS). Response headers/body are not exposed to JS in that case."
    );
  }

  return lines.join("\n");
}

export function clearApiFailureDebug(): void {
  lastApiFailureDebug.value = "";
}
