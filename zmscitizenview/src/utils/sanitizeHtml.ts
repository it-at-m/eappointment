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
 * Returns false if there are leading text nodes with non-whitespace content.
 */
function hasBlockLevelRoot(html: string): boolean {
  // Parse the HTML into a temporary container to inspect structure
  const parser = new DOMParser();
  const doc = parser.parseFromString(`<div>${html}</div>`, "text/html");
  const container = doc.body.firstElementChild;

  if (!container || container.childNodes.length === 0) {
    // No nodes found - it's plain text or empty
    return false;
  }

  // Check for leading text nodes before the first element
  // If there's meaningful text before any element, we need to wrap it
  for (let i = 0; i < container.childNodes.length; i++) {
    const node = container.childNodes[i];

    if (node.nodeType === Node.TEXT_NODE) {
      // Found a text node - check if it has non-whitespace content
      if (node.textContent && node.textContent.trim().length > 0) {
        // Leading text with content means we need to wrap
        return false;
      }
      // Whitespace-only text node, continue checking
      continue;
    }

    if (node.nodeType === Node.ELEMENT_NODE) {
      // Found the first element node - check if it's block-level
      const element = node as Element;

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

      return blockLevelTags.has(element.tagName.toLowerCase());
    }
  }

  // No elements found, only text nodes (or empty)
  return false;
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
