import { State } from "altcha/types";
import { flushPromises, mount } from "@vue/test-utils";
import { describe, expect, it, vi } from "vitest";

import AltchaCaptcha from "@/components/Appointment/ServiceFinder/AltchaCaptcha.vue";

globalThis.scrollTo = vi.fn();

describe("AltchaCaptcha", () => {
  const mockBaseUrl = "https://www.muenchen.de";

  const createWrapper = () =>
    mount(AltchaCaptcha, {
      props: {
        baseUrl: mockBaseUrl,
        t: (key: string) => key,
      },
      global: {
        stubs: { "altcha-widget": true },
      },
    });

  it("shows error message when captcha is disabled", async () => {
    vi.spyOn(globalThis, "fetch").mockResolvedValueOnce({
      ok: true,
      json: async () => ({ captchaEnabled: false }),
    } as Response);
    const wrapper = createWrapper();
    await flushPromises();
    expect(wrapper.find("altcha-widget-stub").exists()).toBe(false);
    expect(wrapper.text()).toContain("altcha.loadError");
  });

  it("emits validationResult on statechange", () => {
    const wrapper = createWrapper();
    wrapper.vm.onStateChange({ detail: { state: State.VERIFIED } });
    expect(wrapper.emitted("validationResult")?.at(-1)).toEqual([true]);
  });

  it("emits tokenChanged on serververification", () => {
    const wrapper = createWrapper();
    wrapper.vm.onServerVerification({
      detail: {
        meta: { success: true },
        data: { valid: true },
        token: "test-token",
      },
    });
    expect(wrapper.emitted("tokenChanged")?.at(-1)).toEqual(["test-token"]);
  });

  it("shows load error when fetchCaptchaDetails fails", async () => {
    vi.spyOn(globalThis, "fetch").mockRejectedValueOnce(
      new Error("Fetch failed")
    );
    const wrapper = createWrapper();
    await flushPromises();
    expect(wrapper.text()).toContain("altcha.loadError");
  });
});
