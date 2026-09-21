import { mount } from "@vue/test-utils";
import { afterEach, describe, expect, it, vi } from "vitest";

import BuildInfoBadge from "@/components/Common/BuildInfoBadge.vue";

describe("BuildInfoBadge", () => {
  afterEach(() => {
    vi.unstubAllEnvs();
  });

  it("renders commit and ref in development", () => {
    vi.stubEnv("VITE_GIT_COMMIT", "a1b2c3d");
    vi.stubEnv("VITE_GIT_REF", "next");
    vi.stubEnv("VITE_NODE_ENV", "development");

    const wrapper = mount(BuildInfoBadge);
    expect(wrapper.text()).toBe("a1b2c3d · next");
  });

  it("renders nothing in production", () => {
    vi.stubEnv("VITE_GIT_COMMIT", "a1b2c3d");
    vi.stubEnv("VITE_GIT_REF", "next");
    vi.stubEnv("VITE_NODE_ENV", "production");

    const wrapper = mount(BuildInfoBadge);
    expect(wrapper.text()).toBe("");
    expect(wrapper.find(".build-info").exists()).toBe(false);
  });
});
