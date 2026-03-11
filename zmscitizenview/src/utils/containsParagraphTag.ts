/**
 * Shared utility for callout type handling
 */

export function containsParagraphTag(
  html: string | null | undefined
): boolean {
  const input = (html ?? "").toString().trim();
  if (input === "") return false;

  if (typeof DOMParser !== "undefined") {
    const doc = new DOMParser().parseFromString(input, "text/html");
    return doc.body.querySelector("p") !== null;
  }

  return /<p\b[^>]*>/i.test(input);
}

export default containsParagraphTag;
