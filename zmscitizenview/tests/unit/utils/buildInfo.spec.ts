import { describe, expect, it } from "vitest";

import {
  formatBuildInfoLabel,
  isProductionBuild,
  isProductionZmsEnv,
  resolveBuildInfoLabel,
  shouldShowBuildInfo,
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

describe("isProductionZmsEnv", () => {
  it("treats prod and production as production", () => {
    expect(isProductionZmsEnv("prod")).toBe(true);
    expect(isProductionZmsEnv("production")).toBe(true);
    expect(isProductionZmsEnv("PROD")).toBe(true);
  });

  it("treats test, demo, and dev as non-production", () => {
    expect(isProductionZmsEnv("test")).toBe(false);
    expect(isProductionZmsEnv("demo")).toBe(false);
    expect(isProductionZmsEnv("dev")).toBe(false);
  });
});

describe("shouldShowBuildInfo", () => {
  it("uses ZMS_ENV when it is present, even for Vite production builds", () => {
    expect(shouldShowBuildInfo("production", "test")).toBe(true);
    expect(shouldShowBuildInfo("production", "demo")).toBe(true);
    expect(shouldShowBuildInfo("production", "dev")).toBe(true);
    expect(shouldShowBuildInfo("production", "prod")).toBe(false);
    expect(shouldShowBuildInfo("production", "production")).toBe(false);
  });

  it("falls back to VITE_NODE_ENV when ZMS_ENV is missing", () => {
    expect(shouldShowBuildInfo("development", undefined)).toBe(true);
    expect(shouldShowBuildInfo("production", undefined)).toBe(false);
    expect(shouldShowBuildInfo("production", "")).toBe(false);
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
  it("shows the label for a production Vite build with ZMS_ENV=test", () => {
    expect(resolveBuildInfoLabel("a1b2c3d", "next", "production", "test")).toBe(
      "a1b2c3d · next"
    );
  });

  it("hides the label for a production Vite build with ZMS_ENV=prod", () => {
    expect(
      resolveBuildInfoLabel("a1b2c3d", "next", "production", "prod")
    ).toBeNull();
  });

  it("hides the label for a production Vite build without ZMS_ENV", () => {
    expect(
      resolveBuildInfoLabel("a1b2c3d", "next", "production", undefined)
    ).toBeNull();
  });
});
