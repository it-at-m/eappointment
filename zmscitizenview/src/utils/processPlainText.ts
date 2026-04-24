/**
 * Mirrors {@link BO\Zmsentities\Helper\ProcessPlainText::normalize} (PHP) for
 * client-side length/required checks aligned with the API/entities layer.
 *
 * Implemented without assigning user-controlled strings to innerHTML, to
 * avoid DOM XSS sinks and satisfy static analysis (CodeQL).
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

/**
 * Decode a subset of HTML entities in a loop until stable, with "&amp;"
 * applied last each round (avoids turning "&amp;lt;" into "<" in one step).
 */
function decodeHtmlEntities(str: string): string {
  let s = str;
  let prev: string;
  do {
    prev = s;
    s = s
      .replaceAll(/&nbsp;/gi, " ")
      .replaceAll("&lt;", "<")
      .replaceAll("&gt;", ">")
      .replaceAll("&quot;", '"')
      .replaceAll(/&#0*39;/g, "'")
      .replaceAll(/&#x0*27;/gi, "'")
      .replaceAll("&amp;", "&");
  } while (s !== prev);
  return s;
}

/**
 * Remove HTML tags similarly to PHP strip_tags: repeatedly strip
 * well-formed &lt;...&gt; segments until none remain (handles nested tags).
 */
function stripHtmlTags(s: string): string {
  let cur = s;
  let prev: string;
  do {
    prev = cur;
    cur = cur.replaceAll(/<[^>]*>/g, "");
  } while (cur !== prev);
  return cur;
}
