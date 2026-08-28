import type { AppointmentDTO } from "@/api/models/AppointmentDTO";
import type { Scope } from "@/api/models/Scope";
import type { CustomerData } from "@/types/CustomerData";

/** Placeholder set by reserve before real contact data is written. */
export const PLACEHOLDER_RESERVE_EMAIL = "test@muenchen.de";

export function isPlaceholderEmail(email?: string | null): boolean {
  return (email ?? "").trim().toLowerCase() === PLACEHOLDER_RESERVE_EMAIL;
}

export function isFilledContactValue(value?: string | null): boolean {
  return (value ?? "").trim() !== "";
}

export function isFilledEmail(email?: string | null): boolean {
  return isFilledContactValue(email) && !isPlaceholderEmail(email);
}

export function getContactFieldLocks(
  isRebooking: boolean | undefined,
  sourceAppointment?: AppointmentDTO | null
): {
  firstName: boolean;
  lastName: boolean;
  mailAddress: boolean;
  telephoneNumber: boolean;
  customTextfield: boolean;
  customTextfield2: boolean;
} {
  const lockIfStoredOnSource = (value?: string | null) =>
    Boolean(isRebooking) && isFilledContactValue(value);
  const storedName = splitFamilyName(sourceAppointment?.familyName);
  return {
    firstName: lockIfStoredOnSource(storedName.firstName),
    lastName: lockIfStoredOnSource(storedName.lastName),
    mailAddress:
      Boolean(isRebooking) && isFilledEmail(sourceAppointment?.email),
    telephoneNumber: lockIfStoredOnSource(sourceAppointment?.telephone),
    customTextfield: lockIfStoredOnSource(sourceAppointment?.customTextfield),
    customTextfield2: lockIfStoredOnSource(sourceAppointment?.customTextfield2),
  };
}

export function splitFamilyName(familyName?: string | null): {
  firstName: string;
  lastName: string;
} {
  const trimmed = (familyName ?? "").trim();
  if (!trimmed) {
    return { firstName: "", lastName: "" };
  }
  const space = trimmed.indexOf(" ");
  if (space === -1) {
    return { firstName: trimmed, lastName: "" };
  }
  return {
    firstName: trimmed.slice(0, space),
    lastName: trimmed.slice(space + 1).trim(),
  };
}

export function joinFamilyName(
  firstName?: string | null,
  lastName?: string | null
): string {
  return [firstName, lastName]
    .map((part) => (part ?? "").trim())
    .filter((part) => part !== "")
    .join(" ");
}

export function applyAppointmentContactToCustomerData(
  customerData: CustomerData,
  appointment: AppointmentDTO
): void {
  const { firstName, lastName } = splitFamilyName(appointment.familyName);
  if (firstName && !customerData.firstName) {
    customerData.firstName = firstName;
  }
  if (lastName && !customerData.lastName) {
    customerData.lastName = lastName;
  }
  if (
    isFilledEmail(appointment.email) &&
    !isFilledEmail(customerData.mailAddress)
  ) {
    customerData.mailAddress = appointment.email;
  }
  if (appointment.telephone && !customerData.telephoneNumber) {
    customerData.telephoneNumber = appointment.telephone;
  }
  if (appointment.customTextfield && !customerData.customTextfield) {
    customerData.customTextfield = appointment.customTextfield;
  }
  if (appointment.customTextfield2 && !customerData.customTextfield2) {
    customerData.customTextfield2 = appointment.customTextfield2;
  }
}

/**
 * True when the target scope still needs contact data the citizen has not provided.
 * Email is always required in the Bürgerfrontend contact form.
 */
export function hasMissingRequiredContact(
  appointment: AppointmentDTO,
  scope?: Scope | null
): boolean {
  if (!isFilledEmail(appointment.email)) {
    return true;
  }
  if (!isFilledContactValue(appointment.familyName)) {
    return true;
  }
  if (scope?.telephoneActivated && scope?.telephoneRequired) {
    if (!isFilledContactValue(appointment.telephone)) {
      return true;
    }
  }
  if (scope?.customTextfieldActivated && scope?.customTextfieldRequired) {
    if (!isFilledContactValue(appointment.customTextfield)) {
      return true;
    }
  }
  if (scope?.customTextfield2Activated && scope?.customTextfield2Required) {
    if (!isFilledContactValue(appointment.customTextfield2)) {
      return true;
    }
  }
  return false;
}
