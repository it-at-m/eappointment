/**
 * Mirrors {@link BO\Zmsentities\Helper\ProcessPlainText::normalize} (PHP) for
 * client-side length/required checks aligned with the API/entities layer.
 */
export function normalizePlainText(input: string | null | undefined): string {
  if (input == null || input === "") {
    return "";
  }
  let s = String(input);
  s = decodeHtmlEntities(s);
  s = s.replaceAll("\r\n", "\n").replaceAll("\r", "\n");
  s = s.replaceAll(/<\s*br\s*\/?>/gi, "\n");
  s = stripHtmlTags(s);
  return s;
}

/** Like PHP mb_strlen(normalize($input), 'UTF-8'). */
export function plainTextCharCount(input: string | null | undefined): number {
  return Array.from(normalizePlainText(input)).length;
}

function decodeHtmlEntities(str: string): string {
  if (typeof document !== "undefined") {
    const el = document.createElement("textarea");
    el.innerHTML = str;
    return el.value;
  }
  return str
    .replaceAll(/&nbsp;/gi, " ")
    .replaceAll("&amp;", "&")
    .replaceAll("&lt;", "<")
    .replaceAll("&gt;", ">")
    .replaceAll("&quot;", '"')
    .replaceAll(/&#0*39;/g, "'")
    .replaceAll(/&#x0*27;/gi, "'");
}

function stripHtmlTags(s: string): string {
  if (typeof document === "undefined") {
    return s.replaceAll(/<[^>]*>/g, "");
  }
  try {
    const d = document.createElement("div");
    d.innerHTML = s;
    return d.textContent ?? "";
  } catch {
    return s.replaceAll(/<[^>]*>/g, "");
  }
}
