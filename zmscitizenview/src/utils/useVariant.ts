import type { VariantOverwrite } from "@/api/models/Service";
import type { I18n } from "vue-i18n";

import { inject, unref } from "vue";
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

    const locale = unref(i18n?.global.locale) ?? "de-DE";
    const shortOverwrite = overwrite?.[locale.split("-")[0]];
    const fullOverwrite = overwrite?.[locale];
    const hintKey = `variants.${id}.hint`;
    const translatedHint = t(hintKey);

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
