/**
 * Client-side plain-text normalization used for UX checks (required + rough
 * length guardrails) before submit.
 *
 * Backend normalization/validation in
 * {@link BO\Zmsentities\Helper\ProcessPlainText::normalize} and entities
 * validators remains authoritative.
 *
 * Implemented without assigning user-controlled strings to innerHTML, to
 * avoid DOM XSS sinks and satisfy static analysis (CodeQL).
 */
export function normalizePlainText(
  rawInput: string | null | undefined
): string {
  if (rawInput == null || rawInput === "") {
    return "";
  }
  let workingText = String(rawInput);
  workingText = decodeHtmlEntities(workingText);
  workingText = workingText.replaceAll("\r\n", "\n").replaceAll("\r", "\n");
  workingText = workingText.replaceAll(/<\s*br\s*\/?>/gi, "\n");
  workingText = stripHtmlTags(workingText);
  return workingText;
}

/**
 * Client-side character count after local normalization.
 *
 * Note: This is a UX pre-check and can differ from backend length in edge
 * cases (e.g. unsupported entities or decoding semantics). API/entity layer is
 * authoritative.
 */
export function plainTextCharCount(
  rawInput: string | null | undefined
): number {
  return Array.from(normalizePlainText(rawInput)).length;
}

/**
 * Decode a conservative subset of HTML entities for client-side checks.
 *
 * This intentionally does not implement full HTML5 entity decoding. Backend
 * `html_entity_decode(..., ENT_QUOTES | ENT_HTML5)` remains the source of
 * truth for persisted validation.
 */
function decodeHtmlEntities(encodedText: string): string {
  let decodedText = encodedText;
  let previousDecodedText: string;
  do {
    previousDecodedText = decodedText;
    decodedText = decodedText
      .replaceAll(/&nbsp;/gi, " ")
      .replaceAll("&lt;", "<")
      .replaceAll("&gt;", ">")
      .replaceAll("&quot;", '"')
      .replaceAll(/&#0*39;/g, "'")
      .replaceAll(/&#x0*27;/gi, "'")
      .replaceAll("&amp;", "&");
  } while (decodedText !== previousDecodedText);
  return decodedText;
}

/**
 * Remove HTML tags similarly to PHP strip_tags: repeatedly strip
 * well-formed &lt;...&gt; segments until none remain (handles nested tags).
 */
function stripHtmlTags(markupWithTags: string): string {
  let textAfterStrip = markupWithTags;
  let previousTextAfterStrip: string;
  do {
    previousTextAfterStrip = textAfterStrip;
    textAfterStrip = textAfterStrip.replaceAll(/<[^>]*>/g, "");
  } while (textAfterStrip !== previousTextAfterStrip);
  return textAfterStrip;
}
