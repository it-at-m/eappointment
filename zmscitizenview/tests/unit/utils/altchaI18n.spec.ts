import "altcha";
import "altcha/i18n/de";

import { describe, expect, it } from "vitest";

import { applyAltchaStrings, resolveAltchaLanguage } from "@/utils/altchaI18n";

describe("resolveAltchaLanguage", () => {
  it("maps vue-i18n locales to ALTCHA language codes", () => {
    expect(resolveAltchaLanguage("de-DE")).toBe("de");
    expect(resolveAltchaLanguage("en-US")).toBe("en");
    expect(resolveAltchaLanguage("fr-FR")).toBe("de");
  });
});

describe("applyAltchaStrings", () => {
  it("applies vue-i18n strings to $altcha.i18n", () => {
    applyAltchaStrings((key) => `translated:${key}`, "de");
    expect(globalThis.$altcha.i18n.get("de")?.label).toBe(
      "translated:altcha.label"
    );
  });
});
