const LATEST_API =
  "https://api.github.com/repos/it-at-m/eappointment/releases/latest";

let cached = null;
let inflight = null;

/**
 * @returns {Promise<{ tagName: string, tagUrl: string }>}
 */
export async function fetchLatestRelease() {
  if (cached) {
    return cached;
  }
  if (inflight) {
    return inflight;
  }
  inflight = (async () => {
    try {
      const res = await fetch(LATEST_API, {
        headers: { Accept: "application/vnd.github+json" }
      });
      if (!res.ok) {
        cached = { tagName: "", tagUrl: "" };
        return cached;
      }
      const data = await res.json();
      const name = data.tag_name;
      const url = data.html_url;
      if (typeof name === "string" && name && typeof url === "string" && url) {
        cached = { tagName: name, tagUrl: url };
      } else {
        cached = { tagName: "", tagUrl: "" };
      }
      return cached;
    } catch {
      cached = { tagName: "", tagUrl: "" };
      return cached;
    } finally {
      inflight = null;
    }
  })();
  return inflight;
}
