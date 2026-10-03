import type { VariantOverwrite } from "@/api/models/Service";
import type { I18n } from "vue-i18n";

import { inject } from "vue";
import { I18nInjectionKey } from "vue-i18n";

export interface Variant {
  id: number;
  name: string;
  hint?: string;
}

export function useVariant(t: (key: string) => string) {
  const i18n = inject<I18n | null>(I18nInjectionKey, null);

  return (
    id: number | null,
    overwrite?: VariantOverwrite
  ): Variant | undefined => {
    if (id == null) return undefined;

    const activeLocale = i18n?.global.locale;
    const locale =
      typeof activeLocale === "string"
        ? activeLocale
        : (activeLocale?.value ?? "de-DE");
    const shortLocale = locale.split("-")[0];
    const shortOverwrite = overwrite?.[shortLocale];
    const fullOverwrite = overwrite?.[locale];
    const hintKey = `variants.${id}.hint`;
    const translatedHint =
      !i18n || i18n.global.te(hintKey, locale) ? t(hintKey) : hintKey;

    return {
      id,
      name:
        shortOverwrite?.name ?? fullOverwrite?.name ?? t(`variants.${id}.name`),
      hint:
        shortOverwrite?.hint ??
        fullOverwrite?.hint ??
        (translatedHint === hintKey ? undefined : translatedHint),
    };
  };
}
