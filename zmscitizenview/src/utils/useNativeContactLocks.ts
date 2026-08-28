import type { ComputedRef, Ref } from "vue";

import { nextTick, onUpdated, watch } from "vue";

const LOCKED_CONTROL_BG = "var(--color-neutral-100, #f2f2f2)";
const LOCKED_CONTROL_FG = "var(--color-neutral-600, #6d6d6d)";

function applyNativeDisabledOnFieldset(fieldset: HTMLFieldSetElement): void {
  const locked = fieldset.disabled;
  const control = fieldset.querySelector<
    HTMLInputElement | HTMLTextAreaElement
  >(
    "input:not([type=hidden]):not([type=checkbox]):not([type=radio]), textarea"
  );
  if (!control) {
    return;
  }
  control.disabled = locked;
  if (locked) {
    control.setAttribute("disabled", "");
    control.style.setProperty(
      "background-color",
      LOCKED_CONTROL_BG,
      "important"
    );
    control.style.setProperty("color", LOCKED_CONTROL_FG, "important");
    control.style.setProperty("cursor", "not-allowed", "important");
  } else {
    control.removeAttribute("disabled");
    control.style.removeProperty("background-color");
    control.style.removeProperty("color");
    control.style.removeProperty("cursor");
  }
}

/**
 * muc-input / muc-text-area have no disabled prop (fallthrough lands on a
 * wrapper div). A fieldset plus native disable actually locks the control.
 */
export function useNativeContactLocks(
  form: Ref<HTMLFormElement | null>,
  locks: Array<Ref<boolean> | ComputedRef<boolean>>
): void {
  const applyContactLocksToNativeControls = async () => {
    await nextTick();
    const formEl = form.value;
    if (!formEl) {
      return;
    }
    formEl
      .querySelectorAll<HTMLFieldSetElement>("fieldset[data-contact-lock]")
      .forEach(applyNativeDisabledOnFieldset);
  };

  watch(
    locks,
    () => {
      void applyContactLocksToNativeControls();
    },
    { immediate: true, flush: "post" }
  );

  onUpdated(() => {
    void applyContactLocksToNativeControls();
  });
}
