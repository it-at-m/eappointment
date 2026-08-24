import type { AppointmentDTO } from "@/api/models/AppointmentDTO";

import {
  isFilledContactValue,
  splitFamilyName,
} from "@/utils/rebookingContact";

export const CONTACT_LOCK_FIELDSET_SELECTOR = "fieldset[data-contact-lock]";

const LOCKED_CONTROL_BG = "var(--color-neutral-100, #f2f2f2)";
const LOCKED_CONTROL_FG = "var(--color-neutral-600, #6d6d6d)";

export type ContactFieldLocks = {
  firstName: boolean;
  lastName: boolean;
  mailAddress: boolean;
  telephoneNumber: boolean;
  customTextfield: boolean;
  customTextfield2: boolean;
};

const unlockedContactFields = (): ContactFieldLocks => ({
  firstName: false,
  lastName: false,
  mailAddress: false,
  telephoneNumber: false,
  customTextfield: false,
  customTextfield2: false,
});

/**
 * Lock from the previous appointment, not live form state. Typing into an empty
 * required field must not disable it; a mount snapshot also misses values copied
 * onto customerData a tick later.
 */
export function getContactFieldLocks(
  isRebooking: boolean,
  previousAppointment?: AppointmentDTO | null
): ContactFieldLocks {
  if (!isRebooking || !previousAppointment) {
    return unlockedContactFields();
  }
  const { firstName, lastName } = splitFamilyName(
    previousAppointment.familyName
  );
  return {
    firstName: isFilledContactValue(firstName),
    lastName: isFilledContactValue(lastName),
    mailAddress: isFilledContactValue(previousAppointment.email),
    telephoneNumber: isFilledContactValue(previousAppointment.telephone),
    customTextfield: isFilledContactValue(previousAppointment.customTextfield),
    customTextfield2: isFilledContactValue(
      previousAppointment.customTextfield2
    ),
  };
}

export function applyNativeDisabledOnFieldset(
  fieldset: HTMLFieldSetElement
): void {
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

export function applyContactLocksToNativeControls(
  form: HTMLFormElement | null
): void {
  if (!form) {
    return;
  }
  form
    .querySelectorAll<HTMLFieldSetElement>(CONTACT_LOCK_FIELDSET_SELECTOR)
    .forEach(applyNativeDisabledOnFieldset);
}
