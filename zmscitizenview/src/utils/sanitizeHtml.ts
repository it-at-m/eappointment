// A lightweight HTML sanitizer to be used before binding with v-html.
// It removes disallowed elements and attributes and ensures URLs use safe protocols.

const ALLOWED_TAGS = new Set<string>([
  // Text/structure
  "p",
  "br",
  "b",
  "strong",
  "i",
  "em",
  "u",
  "s",
  "span",
  "ul",
  "ol",
  "li",
  "h1",
  "h2",
  "h3",
  "h4",
  // Links
  "a",
  // Minimal SVG support for icon sprites
  "svg",
  "symbol",
  "defs",
  "use",
  "path",
  "g",
]);

const ALLOWED_ATTRS = new Set<string>([
  // Global-ish
  "class",
  "id",
  "title",
  "role",
  // ARIA
  "aria-hidden",
  "aria-label",
  "aria-labelledby",
  // Links
  "href",
  "target",
  "rel",
  // SVG
  "viewBox",
  "xmlns",
  "fill",
  "stroke",
  "d",
  "width",
  "height",
  "focusable",
  "xlink:href",
]);

const SAFE_URL_PROTOCOLS = ["http:", "https:", "mailto:", "tel:"];

function isSafeUrl(url: string | null): boolean {
  if (!url) return true; // empty or fragment only
  // Allow same-page fragments
  if (url.startsWith("#")) return true;
  try {
    const parsed = new URL(url, "http://example.com");
    return SAFE_URL_PROTOCOLS.includes(parsed.protocol);
  } catch {
    // Relative URLs are ok
    return !/^\s*javascript:/i.test(url) && !/^\s*data:/i.test(url);
  }
}

function sanitizeNode(node: Node): Node | null {
  switch (node.nodeType) {
    case Node.ELEMENT_NODE: {
      const el = node as Element;
      const tag = el.tagName.toLowerCase();
      if (!ALLOWED_TAGS.has(tag)) {
        return document.createTextNode("");
      }

      // Remove disallowed attributes and all event handlers
      Array.from(el.attributes).forEach((attr) => {
        const name = attr.name;
        const lower = name.toLowerCase();
        if (lower.startsWith("on")) {
          el.removeAttribute(name);
          return;
        }
        if (!ALLOWED_ATTRS.has(name)) {
          el.removeAttribute(name);
          return;
        }

        if (
          (name === "href" || name === "xlink:href") &&
          !isSafeUrl(attr.value)
        ) {
          el.removeAttribute(name);
        }
      });

      // Recurse into children
      Array.from(el.childNodes).forEach((child) => {
        const cleaned = sanitizeNode(child);
        if (cleaned === null) {
          el.removeChild(child);
        }
      });
      return el;
    }
    case Node.TEXT_NODE:
      return node;
    case Node.COMMENT_NODE:
    default:
      return document.createTextNode("");
  }
}

export function sanitizeHtml(dirtyHtml: string | null | undefined): string {
  const input = (dirtyHtml ?? "").toString();
  if (input.trim() === "") return "";

  // Parse within inert container to avoid executing anything
  const container = document.createElement("div");
  container.innerHTML = input;

  // Sanitize children in place
  Array.from(container.childNodes).forEach((child) => {
    const cleaned = sanitizeNode(child);
    if (cleaned === null) container.removeChild(child);
  });

  return container.innerHTML;
}

export default sanitizeHtml;
