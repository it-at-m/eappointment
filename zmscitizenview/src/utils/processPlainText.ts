/**
 * Mirrors {@link BO\Zmsentities\Helper\ProcessPlainText::normalize} (PHP) for
 * client-side length/required checks aligned with the API/entities layer.
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

/** Like PHP mb_strlen(normalize($input), 'UTF-8'). */
export function plainTextCharCount(
  rawInput: string | null | undefined
): number {
  return Array.from(normalizePlainText(rawInput)).length;
}

/**
 * Decode a subset of HTML entities in a loop until stable, with "&amp;"
 * applied last each round (avoids turning "&amp;lt;" into "<" in one step).
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
