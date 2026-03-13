import { describe, it, expect } from "vitest";
import { containsParagraphTag } from "@/utils/containsParagraphTag";

describe("containsParagraphTag", () => {
  it("returns false for null/undefined/empty", () => {
    expect(containsParagraphTag(undefined)).toBe(false);
    expect(containsParagraphTag(null)).toBe(false);
    expect(containsParagraphTag("")).toBe(false);
    expect(containsParagraphTag("   ")).toBe(false);
  });

  it("returns false when no p tag is present", () => {
    expect(containsParagraphTag("Nur Text")).toBe(false);
    expect(containsParagraphTag("<div><span>Hinweis</span></div>")).toBe(false);
    expect(containsParagraphTag("<ul><li>A</li><li>B</li></ul>")).toBe(false);
  });

  it("returns true when a p tag is present", () => {
    expect(containsParagraphTag("<p>Hinweis</p>")).toBe(true);
  });

  it("returns true for nested p tags", () => {
    expect(
      containsParagraphTag("<div><p>Bitte Unterlagen mitbringen.</p></div>")
    ).toBe(true);
  });

  it("returns true for p tags with attributes and mixed casing", () => {
    expect(containsParagraphTag('<p class="hint">Hinweis</p>')).toBe(true);
    expect(containsParagraphTag('<P data-test="hint">Hinweis</P>')).toBe(true);
  });
});
