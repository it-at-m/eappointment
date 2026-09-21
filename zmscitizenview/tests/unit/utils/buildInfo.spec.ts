import { describe, expect, it } from "vitest";

import {
  formatBuildInfoLabel,
  isProductionBuild,
  resolveBuildInfoLabel,
} from "@/utils/buildInfo";

describe("isProductionBuild", () => {
  it("treats VITE_NODE_ENV=production as production", () => {
    expect(isProductionBuild("production")).toBe(true);
    expect(isProductionBuild("PRODUCTION")).toBe(true);
    expect(isProductionBuild(" production ")).toBe(true);
  });

  it("treats development and missing env as non-production", () => {
    expect(isProductionBuild("development")).toBe(false);
    expect(isProductionBuild("")).toBe(false);
    expect(isProductionBuild(undefined)).toBe(false);
  });
});

describe("formatBuildInfoLabel", () => {
  it("joins commit and branch or tag", () => {
    expect(formatBuildInfoLabel("a1b2c3d", "next")).toBe("a1b2c3d · next");
    expect(formatBuildInfoLabel("a1b2c3d", "v1.2.3")).toBe("a1b2c3d · v1.2.3");
  });

  it("returns a single part when the other is missing", () => {
    expect(formatBuildInfoLabel("a1b2c3d", "")).toBe("a1b2c3d");
    expect(formatBuildInfoLabel("", "next")).toBe("next");
    expect(formatBuildInfoLabel(undefined, undefined)).toBeNull();
    expect(formatBuildInfoLabel("  ", "  ")).toBeNull();
  });
});

describe("resolveBuildInfoLabel", () => {
  it("returns null for production builds even when git info is present", () => {
    expect(resolveBuildInfoLabel("a1b2c3d", "next", "production")).toBeNull();
  });

  it("returns the label for development builds", () => {
    expect(resolveBuildInfoLabel("a1b2c3d", "next", "development")).toBe(
      "a1b2c3d · next"
    );
  });
});
