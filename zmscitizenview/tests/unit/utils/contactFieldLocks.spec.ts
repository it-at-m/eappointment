import type { AppointmentDTO } from "@/api/models/AppointmentDTO";

import { describe, expect, it } from "vitest";

import {
  applyContactLocksToNativeControls,
  getContactFieldLocks,
} from "@/utils/contactFieldLocks";
import { PLACEHOLDER_RESERVE_EMAIL } from "@/utils/rebookingContact";

const baseAppointment = (
  overrides: Partial<AppointmentDTO> = {}
): AppointmentDTO =>
  ({
    processId: "1",
    timestamp: 1,
    authKey: "k",
    familyName: "Max Mustermann",
    email: "max@example.com",
    officeId: "1",
    scope: {} as AppointmentDTO["scope"],
    subRequestCounts: [],
    serviceId: "1",
    serviceName: "Service",
    serviceCount: 1,
    ...overrides,
  }) as AppointmentDTO;

describe("contactFieldLocks", () => {
  it("locks filled name and email on rebooking and leaves empty fields editable", () => {
    const locks = getContactFieldLocks(
      true,
      baseAppointment({ telephone: "", customTextfield2: "" })
    );
    expect(locks.firstName).toBe(true);
    expect(locks.lastName).toBe(true);
    expect(locks.mailAddress).toBe(true);
    expect(locks.telephoneNumber).toBe(false);
    expect(locks.customTextfield2).toBe(false);
  });

  it("does not lock fields during a new booking", () => {
    const locks = getContactFieldLocks(false, baseAppointment());
    expect(locks.firstName).toBe(false);
    expect(locks.mailAddress).toBe(false);
  });

  it("does not treat the reserve placeholder email as filled", () => {
    const locks = getContactFieldLocks(
      true,
      baseAppointment({ email: PLACEHOLDER_RESERVE_EMAIL })
    );
    expect(locks.mailAddress).toBe(false);
  });

  it("disables the native control inside a disabled contact fieldset", () => {
    const form = document.createElement("form");
    form.innerHTML =
      '<fieldset data-contact-lock="firstname" disabled><input id="firstname" /></fieldset>';
    applyContactLocksToNativeControls(form);
    const input = form.querySelector("input") as HTMLInputElement;
    expect(input.disabled).toBe(true);
    expect(input.style.cursor).toBe("not-allowed");
  });
});
