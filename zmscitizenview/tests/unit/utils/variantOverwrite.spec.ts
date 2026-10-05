import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import { defineComponent } from "vue";
import { createI18n, I18nInjectionKey } from "vue-i18n";

import de from "@/utils/de-DE.json";
import en from "@/utils/en-US.json";
import { useVariant } from "@/utils/useVariant";

describe("variant text", () => {
  it("resolves name and optional hint for the active locale", () => {
    const i18n = createI18n({
      legacy: false,
      locale: "de-DE",
      messages: { "de-DE": de, "en-US": en },
    });
    const component = defineComponent({
      setup() {
        return { getVariant: useVariant((key) => i18n.global.t(key)) };
      },
      template: "<div />",
    });
    const wrapper = mount(component, {
      global: { provide: { [I18nInjectionKey as symbol]: i18n } },
    });
    const getVariant = wrapper.vm.getVariant;

    expect(getVariant(2)).toEqual({
      id: 2,
      name: de.variants["2"].name,
      hint: de.variants["2"].hint,
    });
    expect(getVariant(6)).toEqual({
      id: 6,
      name: de.variants["6"].name,
      hint: undefined,
    });

    const overwrite = {
      de: { hint: "Eigener Hinweis" },
      en: { name: "Custom name", hint: "Custom hint" },
    };
    expect(getVariant(2, overwrite)).toEqual({
      id: 2,
      name: de.variants["2"].name,
      hint: "Eigener Hinweis",
    });
    expect(
      getVariant(2, {
        de: { hint: "Eigener Hinweis" },
        "de-DE": { name: "Eigener Name" },
      })
    ).toEqual({ id: 2, name: "Eigener Name", hint: "Eigener Hinweis" });

    i18n.global.locale.value = "en-US";
    expect(getVariant(2, overwrite)).toEqual({
      id: 2,
      name: "Custom name",
      hint: "Custom hint",
    });
    expect(getVariant(4)).toEqual({
      id: 4,
      name: en.variants["4"].name,
      hint: undefined,
    });
    expect(getVariant(null)).toBeUndefined();
  });
});
