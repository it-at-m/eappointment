import type { Config } from "dompurify";

import DOMPurify from "dompurify";

// DOMPurify-backed sanitizer. Keeps defaults and adds minimal SVG tags/attrs for icon sprites.
const config: Config = {
  ADD_TAGS: ["svg", "symbol", "defs", "use", "path", "g"],
  ADD_ATTR: [
    "viewBox",
    "xmlns",
    "fill",
    "stroke",
    "d",
    "width",
    "height",
    "focusable",
    "xlink:href",
  ],
  FORBID_TAGS: ["img"],
};

export function sanitizeHtml(dirtyHtml: string | null | undefined): string {
  const input = (dirtyHtml ?? "").toString();
  if (input.trim() === "") return "";
  return DOMPurify.sanitize(input, config) as string;
}

export default sanitizeHtml;
