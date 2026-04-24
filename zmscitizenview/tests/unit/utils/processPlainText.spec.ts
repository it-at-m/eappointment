import { describe, expect, it } from "vitest";

import {
  normalizePlainText,
  plainTextCharCount,
} from "@/utils/processPlainText";

describe("processPlainText", () => {
  it("returns empty for null/undefined/empty", () => {
    expect(normalizePlainText(null)).toBe("");
    expect(normalizePlainText(undefined)).toBe("");
    expect(normalizePlainText("")).toBe("");
    expect(plainTextCharCount(null)).toBe(0);
  });

  it("matches PHP normalize: entities, CRLF, br, strip tags", () => {
    expect(normalizePlainText("a\r\nb")).toBe("a\nb");
    expect(normalizePlainText("x<br/>y")).toBe("x\ny");
    expect(normalizePlainText("a&amp;b")).toBe("a&b");
    expect(normalizePlainText('hello <span class="x">there</span>')).toBe(
      "hello there"
    );
  });

  it("counts Unicode code points like PHP mb_strlen UTF-8", () => {
    expect(plainTextCharCount("é")).toBe(1);
    expect(plainTextCharCount("😀")).toBe(1);
  });

  it("allows raw length over 255 when normalized length is within limit", () => {
    const raw = `${"a".repeat(252)}&amp;b`;
    expect(raw.length).toBeGreaterThan(255);
    expect(plainTextCharCount(raw)).toBe(254);
    expect(plainTextCharCount(raw) > 255).toBe(false);
  });
});
