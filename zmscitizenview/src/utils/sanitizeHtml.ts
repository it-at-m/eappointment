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
    "target",
    "aria-label",
    "rel",
  ],
  FORBID_TAGS: ["img"],
};

/**
 * Checks if the HTML content already has block-level elements at the root level.
 * Uses DOM parsing to accurately detect block-level elements.
 */
function hasBlockLevelRoot(html: string): boolean {
  // Parse the HTML into a temporary container to inspect structure
  const parser = new DOMParser();
  const doc = parser.parseFromString(`<div>${html}</div>`, "text/html");
  const container = doc.body.firstElementChild;

  if (!container || container.children.length === 0) {
    // No elements found - it's plain text or empty
    return false;
  }

  // Check if the first top-level element is block-level
  const firstElement = container.firstElementChild;
  if (!firstElement) return false;

  // List of block-level HTML elements
  // Based on HTML5 specification for block-level elements
  const blockLevelTags = new Set([
    "address",
    "article",
    "aside",
    "blockquote",
    "details",
    "dialog",
    "dd",
    "div",
    "dl",
    "dt",
    "fieldset",
    "figcaption",
    "figure",
    "footer",
    "form",
    "h1",
    "h2",
    "h3",
    "h4",
    "h5",
    "h6",
    "header",
    "hgroup",
    "hr",
    "li",
    "main",
    "nav",
    "ol",
    "p",
    "pre",
    "section",
    "table",
    "tbody",
    "td",
    "tfoot",
    "th",
    "thead",
    "tr",
    "ul",
  ]);

  return blockLevelTags.has(firstElement.tagName.toLowerCase());
}

export function sanitizeHtml(dirtyHtml: string | null | undefined): string {
  const input = (dirtyHtml ?? "").toString();
  if (input.trim() === "") return "";

  const cleaned = DOMPurify.sanitize(input, config) as string;
  const trimmed = cleaned.trim();

  if (!trimmed) return "";

  // Check if content already has a block-level root element
  if (hasBlockLevelRoot(trimmed)) {
    return trimmed;
  }

  // Wrap plain text or inline-only content in <p> tag
  return `<p>${trimmed}</p>`;
}

export default sanitizeHtml;
