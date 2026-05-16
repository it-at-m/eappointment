export const ALTCHA_LANGUAGE = {
  "de-DE": "de",
  "en-US": "en",
} as const satisfies Record<string, "de" | "en">;

export type AltchaLanguage = (typeof ALTCHA_LANGUAGE)[keyof typeof ALTCHA_LANGUAGE];

export const ALTCHA_I18N_KEYS = [
  "error",
  "expired",
  "label",
  "verified",
  "verifying",
  "waitAlert",
] as const;

export function resolveAltchaLanguage(vueLocale: string): AltchaLanguage {
  return ALTCHA_LANGUAGE[vueLocale as keyof typeof ALTCHA_LANGUAGE] ?? "de";
}

/* Applies vue-i18n altcha strings to the global ALTCHA i18n registry. */
export function applyAltchaStrings(
  translate: (key: string) => string,
  language: string
): void {
  const existing = globalThis.$altcha.i18n.get(language) ?? {};
  const overrides = Object.fromEntries(
    ALTCHA_I18N_KEYS.map((key) => [key, translate(`altcha.${key}`)])
  );
  globalThis.$altcha.i18n.set(language, { ...existing, ...overrides });
}
