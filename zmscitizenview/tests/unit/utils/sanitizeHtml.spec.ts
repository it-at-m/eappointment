import { describe, it, expect } from "vitest";

// @ts-expect-error: Vue SFC import for test
import { sanitizeHtml } from "@/utils/sanitizeHtml";

describe("sanitizeHtml", () => {
  it("returns empty string for null/undefined/empty", () => {
    expect(sanitizeHtml(undefined)).toBe("");
    expect(sanitizeHtml(null)).toBe("");
    expect(sanitizeHtml("")).toBe("");
    expect(sanitizeHtml("   ")).toBe("");
  });

  it("allows basic formatting and lists", () => {
    const dirty = `<p>Hello <strong>World</strong> <em>!</em></p><ul><li>A</li><li>B</li></ul>`;
    const clean = sanitizeHtml(dirty);
    expect(clean).toContain("<p>");
    expect(clean).toContain("<strong>");
    expect(clean).toContain("<em>");
    expect(clean).toContain("<ul>");
    expect(clean).toContain("<li>");
    expect(clean).not.toMatch(/<script/i);
  });

  it("strips scripts and event handlers", () => {
    const dirty = `<div onclick="alert('x')">Click<script>alert('x')</script></div>`;
    const clean = sanitizeHtml(dirty);
    expect(clean).toContain("<div>");
    expect(clean).not.toMatch(/onclick=/i);
    expect(clean).not.toMatch(/<script/i);
  });

  it("removes javascript: and data: URLs but keeps http(s)", () => {
    const dirty = `
      <a href="javascript:alert(1)">bad</a>
      <a href="data:text/html;base64,xxx">bad2</a>
      <a href="/relative">ok</a>
      <a href="https://example.com" target="_blank" rel="noopener">ok2</a>
    `;
    const clean = sanitizeHtml(dirty);
    expect(clean).not.toMatch(/href=\"javascript:/i);
    expect(clean).not.toMatch(/href=\"data:/i);
    expect(clean).toMatch(/href=\"\/relative\"/);
    expect(clean).toMatch(/href=\"https:\/\/example.com\"/);
  });

  it("keeps minimal SVG sprite usage", () => {
    const dirty = `
      <svg viewBox="0 0 10 10" focusable="false"><use xlink:href="#icon-test"></use></svg>
    `;
    const clean = sanitizeHtml(dirty);
    expect(clean).toMatch(/<svg/i);
    expect(clean).toMatch(/<use/i);
    expect(clean).toMatch(/xlink:href=\"#icon-test\"/);
  });

  it("removes disallowed elements like iframe", () => {
    const dirty = `<p>text</p><iframe src="https://example.com"></iframe>`;
    const clean = sanitizeHtml(dirty);
    expect(clean).toContain("<p>text</p>");
    expect(clean).not.toMatch(/<iframe/i);
  });

  it("removes img tags completely when they are forbidden", () => {
    const dirty = `
      <img src="javascript:alert('x')" onerror="alert('x')" alt="x" />
      <img src="http://example.com/a.png" alt="ok" />
      <img src="https://example.com/b.png" alt="ok2" />
    `;
    const clean = sanitizeHtml(dirty);
    // All img tags should be completely removed
    expect(clean).not.toMatch(/<img/i);
    expect(clean).not.toMatch(/src=/i);
    expect(clean).not.toMatch(/alt=/i);
    // Should only contain whitespace
    expect(clean.trim()).toBe("");
  });

  // This is the important test for the issue we are facing when the content is text but not wrapped in a block-level element
  it("should auto-wrap plain text in <p> tag", () => {
    const input = "Viel Glück bei deinem Termin!";
    const expected = "<p>Viel Glück bei deinem Termin!</p>";
    expect(sanitizeHtml(input)).toBe(expected);
  });

  it("should auto-wrap inline-only content in <p> tag", () => {
    const input = "Hello <strong>world</strong>!";
    const expected = "<p>Hello <strong>world</strong>!</p>";
    expect(sanitizeHtml(input)).toBe(expected);
  });

  it("should NOT auto-wrap content that starts with block element", () => {
    const input = "<p>Already wrapped</p>";
    expect(sanitizeHtml(input)).toBe("<p>Already wrapped</p>");
  });

  it("should NOT auto-wrap content with div", () => {
    const input = "<div>Already wrapped</div>";
    expect(sanitizeHtml(input)).toBe("<div>Already wrapped</div>");
  });

  it("should NOT auto-wrap content with ul", () => {
    const input = "<ul><li>Item</li></ul>";
    expect(sanitizeHtml(input)).toBe("<ul><li>Item</li></ul>");
  });

  it("should NOT auto-wrap content with heading", () => {
    const input = "<h1>Heading</h1>";
    expect(sanitizeHtml(input)).toBe("<h1>Heading</h1>");
  });

  it("should NOT auto-wrap content with blockquote", () => {
    const input = "<blockquote>Quote text</blockquote>";
    expect(sanitizeHtml(input)).toBe("<blockquote>Quote text</blockquote>");
  });

  it("should NOT auto-wrap content with pre", () => {
    const input = "<pre>Code block</pre>";
    expect(sanitizeHtml(input)).toBe("<pre>Code block</pre>");
  });

  it("should NOT auto-wrap content with table", () => {
    const input = "<table><tr><td>Cell</td></tr></table>";
    const result = sanitizeHtml(input);
    // DOMPurify normalizes tables by adding <tbody>
    expect(result).toContain("<table>");
    expect(result).toContain("<td>Cell</td>");
    expect(result).not.toMatch(/^<p>/);
  });

  it("should NOT auto-wrap content with hr", () => {
    const input = "<hr />";
    const result = sanitizeHtml(input);
    // DOMPurify normalizes self-closing tags
    expect(result).toContain("<hr");
    expect(result).not.toMatch(/^<p>/);
  });

  it("should NOT auto-wrap content with address", () => {
    const input = "<address>123 Main St</address>";
    expect(sanitizeHtml(input)).toBe("<address>123 Main St</address>");
  });

  it("should NOT auto-wrap content with section", () => {
    const input = "<section>Content</section>";
    expect(sanitizeHtml(input)).toBe("<section>Content</section>");
  });

  it("should NOT auto-wrap content with dl", () => {
    const input = "<dl><dt>Term</dt><dd>Definition</dd></dl>";
    expect(sanitizeHtml(input)).toBe("<dl><dt>Term</dt><dd>Definition</dd></dl>");
  });

  it("should wrap inline elements like span", () => {
    const input = "<span>Inline content</span>";
    expect(sanitizeHtml(input)).toBe("<p><span>Inline content</span></p>");
  });

  it("should wrap multiple inline elements", () => {
    const input = "<span>First</span><span>Second</span>";
    expect(sanitizeHtml(input)).toBe("<p><span>First</span><span>Second</span></p>");
  });
});


