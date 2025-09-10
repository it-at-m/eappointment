import { mount } from "@vue/test-utils";
import { describe, expect, it, vi } from "vitest";
import { nextTick } from "vue";

// @ts-expect-error: Vue SFC import for test
import AltchaCaptcha from "@/components/Appointment/AltchaCaptcha.vue";

// Mock window.scrollTo for jsdom
globalThis.scrollTo = vi.fn();

describe("AltchaCaptcha", () => {
  const mockBaseUrl = "https://www.muenchen.de";

  const createWrapper = (props = {}) => {
    return mount(AltchaCaptcha, {
      props: {
        baseUrl: mockBaseUrl,
        t: (key: string) => key,
        ...props,
      },
      global: {
        stubs: {
          'altcha-widget': {
            template: "<div data-test='altcha-widget'></div>",
            props: ["challengeurl", "verifyurl"],
            emits: ["statechange", "serververification"],
          },
        },
      },
    });
  };

  describe("Rendering", () => {
    it("shows error message when captcha is disabled", async () => {
      const wrapper = createWrapper();
      wrapper.vm.captchaEnabled = false;
      await nextTick();
      expect(wrapper.find('[data-test="altcha-widget"]').exists()).toBe(false);
      expect(wrapper.text()).toContain("altcha.loadError");
    });
  });

  describe("Event Handling", () => {
    it("emits validationResult when statechange event is triggered", async () => {
      const wrapper = createWrapper();
      await nextTick();
      const altchaWidget = wrapper.find('[data-test="altcha-widget"]');
      if (altchaWidget.exists()) {
        altchaWidget.trigger("statechange", { detail: { state: "verified" } });
        const validationResult = wrapper.emitted("validationResult");
        expect(validationResult).toBeTruthy();
        if (validationResult) {
          expect(validationResult[0]).toEqual([true]);
        }
      }
    });

    it("emits tokenChanged when serververification event is triggered", async () => {
      const wrapper = createWrapper();
      await nextTick();
      const altchaWidget = wrapper.find('[data-test="altcha-widget"]');
      if (altchaWidget.exists()) {
        altchaWidget.trigger("serververification", { detail: { token: "test-token" } });
        const tokenChanged = wrapper.emitted("tokenChanged");
        expect(tokenChanged).toBeTruthy();
        if (tokenChanged) {
          expect(tokenChanged[0]).toEqual(["test-token"]);
        }
      }
    });
  });

  describe("Error Handling", () => {
    it("disables captcha when fetchCaptchaDetails fails", async () => {
      const wrapper = createWrapper();
      await nextTick();
      vi.spyOn(global, 'fetch').mockRejectedValueOnce(new Error("Fetch failed"));
      await wrapper.vm.fetchCaptchaDetails();
      expect(wrapper.vm.captchaEnabled).toBe(false);
    });
  });
});
