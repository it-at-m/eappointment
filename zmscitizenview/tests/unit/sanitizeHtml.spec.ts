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

  it("sanitizes img tags: keeps http(s) src, strips events and javascript:", () => {
    const dirty = `
      <img src="javascript:alert('x')" onerror="alert('x')" alt="x" />
      <img src="http://example.com/a.png" alt="ok" />
      <img src="https://example.com/b.png" alt="ok2" />
    `;
    const clean = sanitizeHtml(dirty);
    // No javascript: URL
    expect(clean).not.toMatch(/src=\"javascript:/i);
    // Event handlers removed
    expect(clean).not.toMatch(/onerror=/i);
    // http/https preserved
    expect(clean).toMatch(/<img[^>]*src=\"http:\/\/example.com\/a.png\"[^>]*alt=\"ok\"/);
    expect(clean).toMatch(/<img[^>]*src=\"https:\/\/example.com\/b.png\"[^>]*alt=\"ok2\"/);
  });
});


