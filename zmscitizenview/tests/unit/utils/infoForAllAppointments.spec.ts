import { describe, it, expect } from "vitest";
import {
  cleanProviderName,
  optimizeProviderNames,
  generateAvailabilityInfoHtml,
  type ProviderInfo,
// @ts-expect-error: Vue SFC import for test
} from "@/utils/infoForAllAppointments";

// Mock sanitizeHtml function
const mockSanitizeHtml = (html: string): string => html;

describe("infoForAllAppointments utility", () => {
  describe("cleanProviderName", () => {
    it("removes dashes and replaces with spaces", () => {
      expect(cleanProviderName("Feuerwache 9 - Neuperlach")).toBe("Feuerwache Neuperlach");
      expect(cleanProviderName("Bürgerbüro - Ruppertstraße")).toBe("Bürgerbüro Ruppertstraße");
    });

    it("removes various types of dashes", () => {
      expect(cleanProviderName("Office – Main")).toBe("Office Main");
      expect(cleanProviderName("Office — Branch")).toBe("Office Branch");
    });

    it("removes commas and semicolons", () => {
      expect(cleanProviderName("Office, Main")).toBe("Office Main");
      expect(cleanProviderName("Office; Branch")).toBe("Office Branch");
    });

    it("removes numbers", () => {
      expect(cleanProviderName("Feuerwache 8 Föhring")).toBe("Feuerwache Föhring");
      expect(cleanProviderName("Office 123 Main")).toBe("Office Main");
    });

    it("removes multiple spaces and trims", () => {
      expect(cleanProviderName("  Office   Main  ")).toBe("Office Main");
      expect(cleanProviderName("Office\t\nMain")).toBe("Office Main");
    });

    it("handles complex combinations", () => {
      expect(cleanProviderName("Feuerwache 9 - Neuperlach, 6 - Pasing, 2 - Sendling")).toBe(
        "Feuerwache Neuperlach Pasing Sendling"
      );
    });

    it("handles empty and whitespace-only strings", () => {
      expect(cleanProviderName("")).toBe("");
      expect(cleanProviderName("   ")).toBe("");
      expect(cleanProviderName("\t\n")).toBe("");
    });
  });

  describe("optimizeProviderNames", () => {
    it("returns cleaned name for single provider", () => {
      expect(optimizeProviderNames(["Feuerwache 9 - Neuperlach"])).toBe("Feuerwache Neuperlach");
    });

    it("returns empty string for empty array", () => {
      expect(optimizeProviderNames([])).toBe("");
    });

    it("optimizes names with common prefix", () => {
      const names = [
        "Bürgerbüro Ruppertstraße",
        "Bürgerbüro Pasing",
        "Bürgerbüro Leonrodstraße",
      ];
      expect(optimizeProviderNames(names)).toBe("Bürgerbüro Ruppertstraße, Pasing, Leonrodstraße");
    });

    it("optimizes names with common prefix and numbers", () => {
      const names = [
        "Feuerwache 9 - Neuperlach",
        "Feuerwache 6 - Pasing",
        "Feuerwache 2 - Sendling",
      ];
      expect(optimizeProviderNames(names)).toBe("Feuerwache Neuperlach, Pasing, Sendling");
    });

    it("does not optimize when prefix is too short", () => {
      const names = ["A Main", "A Branch"];
      expect(optimizeProviderNames(names)).toBe("A Main, A Branch");
    });

    it("does not optimize when prefix doesn't end with space", () => {
      const names = ["OfficeMain", "OfficeBranch"];
      expect(optimizeProviderNames(names)).toBe("OfficeMain, OfficeBranch");
    });

    it("handles names without common prefix", () => {
      const names = ["Office A", "Branch B", "Location C"];
      expect(optimizeProviderNames(names)).toBe("Office A, Branch B, Location C");
    });

    it("handles names with mixed cleaning needs", () => {
      const names = [
        "Bürgerbüro 1 - Ruppertstraße",
        "Bürgerbüro 2 - Pasing",
        "Bürgerbüro 3 - Leonrodstraße",
      ];
      expect(optimizeProviderNames(names)).toBe("Bürgerbüro Ruppertstraße, Pasing, Leonrodstraße");
    });
  });

  describe("generateAvailabilityInfoHtml", () => {
    const mockProvider1: ProviderInfo = {
      id: "1",
      name: "Bürgerbüro Ruppertstraße",
      scope: {
        infoForAllAppointments: "Test info 1",
      },
    };

    const mockProvider2: ProviderInfo = {
      id: "2",
      name: "Bürgerbüro Pasing",
      scope: {
        infoForAllAppointments: "Test info 2",
      },
    };

    const mockProvider3: ProviderInfo = {
      id: "3",
      name: "Feuerwache Neuperlach",
      scope: {
        infoForAllAppointments: "Test info 1", // Same as provider 1
      },
    };

    it("returns empty string when no providers are selected", () => {
      const result = generateAvailabilityInfoHtml(
        {},
        [mockProvider1, mockProvider2],
        undefined,
        mockSanitizeHtml
      );
      expect(result).toBe("");
    });

    it("returns selectedProvider info when no providers are selected but selectedProvider exists", () => {
      const result = generateAvailabilityInfoHtml(
        {},
        undefined,
        mockProvider1,
        mockSanitizeHtml
      );
      expect(result).toBe("Test info 1");
    });

    it("returns empty string when selectedProvider has no info", () => {
      const providerWithoutInfo: ProviderInfo = {
        id: "1",
        name: "Test Office",
        scope: {},
      };
      const result = generateAvailabilityInfoHtml(
        {},
        undefined,
        providerWithoutInfo,
        mockSanitizeHtml
      );
      expect(result).toBe("");
    });

    it("returns single info when all selected providers have the same info", () => {
      const result = generateAvailabilityInfoHtml(
        { "1": true, "3": true },
        [mockProvider1, mockProvider3],
        undefined,
        mockSanitizeHtml
      );
      expect(result).toBe("Test info 1");
    });

    it("groups providers by info when they have different info", () => {
      const result = generateAvailabilityInfoHtml(
        { "1": true, "2": true },
        [mockProvider1, mockProvider2],
        undefined,
        mockSanitizeHtml
      );
      expect(result).toContain("<h3 class=\"first-provider\">Bürgerbüro Ruppertstraße</h3>");
      expect(result).toContain("<div>Test info 1</div>");
      expect(result).toContain("<h3>Bürgerbüro Pasing</h3>");
      expect(result).toContain("<div>Test info 2</div>");
    });

    it("optimizes provider names when multiple providers share the same info", () => {
      const providerWithSimilarName: ProviderInfo = {
        id: "4",
        name: "Bürgerbüro Leonrodstraße",
        scope: {
          infoForAllAppointments: "Test info 1", // Same as provider 1
        },
      };

      const result = generateAvailabilityInfoHtml(
        { "1": true, "4": true },
        [mockProvider1, providerWithSimilarName],
        undefined,
        mockSanitizeHtml
      );
      // When providers have the same info, it returns just the info without HTML structure
      expect(result).toBe("Test info 1");
    });

    it("handles providers without names", () => {
      const providerWithoutName: ProviderInfo = {
        id: "5",
        scope: {
          infoForAllAppointments: "Test info 3",
        },
      };

      const result = generateAvailabilityInfoHtml(
        { "5": true },
        [providerWithoutName],
        undefined,
        mockSanitizeHtml
      );
      expect(result).toBe("Test info 3");
    });

    it("filters out providers with empty or whitespace-only info", () => {
      const providerWithEmptyInfo: ProviderInfo = {
        id: "6",
        name: "Empty Office",
        scope: {
          infoForAllAppointments: "",
        },
      };

      const providerWithWhitespaceInfo: ProviderInfo = {
        id: "7",
        name: "Whitespace Office",
        scope: {
          infoForAllAppointments: "   ",
        },
      };

      const result = generateAvailabilityInfoHtml(
        { "1": true, "6": true, "7": true },
        [mockProvider1, providerWithEmptyInfo, providerWithWhitespaceInfo],
        undefined,
        mockSanitizeHtml
      );
      expect(result).toBe("Test info 1");
    });

    it("handles mixed scenarios with some providers having same info and others different", () => {
      const result = generateAvailabilityInfoHtml(
        { "1": true, "2": true, "3": true },
        [mockProvider1, mockProvider2, mockProvider3],
        undefined,
        mockSanitizeHtml
      );
      
      // Should group provider 1 and 3 together (same info)
      expect(result).toContain("<h3 class=\"first-provider\">Bürgerbüro Ruppertstraße, Feuerwache Neuperlach</h3>");
      expect(result).toContain("<div>Test info 1</div>");
      
      // Should have separate entry for provider 2 (different info)
      expect(result).toContain("<h3>Bürgerbüro Pasing</h3>");
      expect(result).toContain("<div>Test info 2</div>");
    });

    it("handles providers with undefined scope", () => {
      const providerWithoutScope: ProviderInfo = {
        id: "8",
        name: "No Scope Office",
      };

      const result = generateAvailabilityInfoHtml(
        { "8": true },
        [providerWithoutScope],
        undefined,
        mockSanitizeHtml
      );
      expect(result).toBe("");
    });

    it("handles providers with undefined infoForAllAppointments", () => {
      const providerWithUndefinedInfo: ProviderInfo = {
        id: "9",
        name: "Undefined Info Office",
        scope: {
          infoForAllAppointments: undefined,
        },
      };

      const result = generateAvailabilityInfoHtml(
        { "9": true },
        [providerWithUndefinedInfo],
        undefined,
        mockSanitizeHtml
      );
      expect(result).toBe("");
    });
  });
});
