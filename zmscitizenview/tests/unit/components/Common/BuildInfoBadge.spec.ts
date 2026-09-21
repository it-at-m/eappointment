import { flushPromises, mount } from "@vue/test-utils";
import { afterEach, describe, expect, it, vi } from "vitest";

import BuildInfoBadge from "@/components/Common/BuildInfoBadge.vue";

describe("BuildInfoBadge", () => {
  afterEach(() => {
    vi.unstubAllEnvs();
    vi.unstubAllGlobals();
  });

  it("renders commit and ref in development without fetching", async () => {
    vi.stubEnv("VITE_GIT_COMMIT", "a1b2c3d");
    vi.stubEnv("VITE_GIT_REF", "next");
    vi.stubEnv("VITE_NODE_ENV", "development");
    const fetchMock = vi.fn();
    vi.stubGlobal("fetch", fetchMock);

    const wrapper = mount(BuildInfoBadge);
    await flushPromises();
    expect(wrapper.text()).toBe("a1b2c3d · next");
    expect(fetchMock).not.toHaveBeenCalled();
  });

  it("renders nothing for a production build without ZMS_ENV", async () => {
    vi.stubEnv("VITE_GIT_COMMIT", "a1b2c3d");
    vi.stubEnv("VITE_GIT_REF", "next");
    vi.stubEnv("VITE_NODE_ENV", "production");
    vi.stubGlobal(
      "fetch",
      vi.fn().mockResolvedValue({
        ok: false,
      })
    );

    const wrapper = mount(BuildInfoBadge);
    await flushPromises();
    expect(wrapper.text()).toBe("");
    expect(wrapper.find(".build-info").exists()).toBe(false);
  });

  it("renders for a production build when runtime-config has ZMS_ENV=test", async () => {
    vi.stubEnv("VITE_GIT_COMMIT", "a1b2c3d");
    vi.stubEnv("VITE_GIT_REF", "next");
    vi.stubEnv("VITE_NODE_ENV", "production");
    vi.stubGlobal(
      "fetch",
      vi.fn().mockResolvedValue({
        ok: true,
        json: async () => ({ ZMS_ENV: "test" }),
      })
    );

    const wrapper = mount(BuildInfoBadge);
    await flushPromises();
    expect(wrapper.text()).toBe("a1b2c3d · next");
  });
});
