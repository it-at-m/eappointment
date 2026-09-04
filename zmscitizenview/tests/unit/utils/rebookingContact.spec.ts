import type { AppointmentDTO } from "@/api/models/AppointmentDTO";

import { describe, expect, it } from "vitest";

import { CustomerData } from "@/types/CustomerData";
import {
  applyAppointmentContactToCustomerData,
  getContactFieldLocks,
  hasMissingRequiredContact,
  isFilledContactValue,
  isFilledEmail,
  isPlaceholderEmail,
  joinFamilyName,
  PLACEHOLDER_RESERVE_EMAIL,
  splitFamilyName,
} from "@/utils/rebookingContact";

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

describe("rebookingContact", () => {
  it("treats reserve placeholder mail as empty only for email", () => {
    expect(isPlaceholderEmail(PLACEHOLDER_RESERVE_EMAIL)).toBe(true);
    expect(isPlaceholderEmail("Max@Example.com")).toBe(false);
    expect(isFilledEmail(PLACEHOLDER_RESERVE_EMAIL)).toBe(false);
    expect(isFilledEmail("max@example.com")).toBe(true);
    expect(isFilledContactValue(PLACEHOLDER_RESERVE_EMAIL)).toBe(true);
    expect(isFilledContactValue("  ")).toBe(false);
    expect(isFilledContactValue("max@example.com")).toBe(true);
  });

  it("locks filled rebooking fields and leaves empty or placeholder ones editable", () => {
    expect(
      getContactFieldLocks(true, {
        familyName: "Max Mustermann",
        email: "max@example.com",
        telephone: "",
        customTextfield: "note",
        customTextfield2: "",
      } as AppointmentDTO)
    ).toEqual({
      firstName: true,
      lastName: true,
      mailAddress: true,
      telephoneNumber: false,
      customTextfield: true,
      customTextfield2: false,
    });
    expect(
      getContactFieldLocks(true, {
        email: PLACEHOLDER_RESERVE_EMAIL,
        customTextfield: PLACEHOLDER_RESERVE_EMAIL,
      } as AppointmentDTO)
    ).toMatchObject({ mailAddress: false, customTextfield: true });
    expect(
      getContactFieldLocks(false, {
        familyName: "Max Mustermann",
        email: "max@example.com",
      } as AppointmentDTO)
    ).toMatchObject({ firstName: false, mailAddress: false });
    expect(
      getContactFieldLocks(true, {
        familyName: "Max",
        email: "max@example.com",
      } as AppointmentDTO)
    ).toMatchObject({ firstName: true, lastName: false });
  });

  it("splits familyName into first and last name", () => {
    expect(splitFamilyName("Max Mustermann")).toEqual({
      firstName: "Max",
      lastName: "Mustermann",
    });
    expect(splitFamilyName("Max")).toEqual({
      firstName: "Max",
      lastName: "",
    });
    expect(joinFamilyName("Max", "Mustermann")).toBe("Max Mustermann");
    expect(joinFamilyName("Max", "")).toBe("Max");
    expect(joinFamilyName("  ", "Mustermann")).toBe("Mustermann");
  });

  it("copies contact data onto customerData without overwriting filled fields", () => {
    const customerData = new CustomerData("Anna", "", "", "", "", "");
    applyAppointmentContactToCustomerData(
      customerData,
      baseAppointment({
        familyName: "Max Mustermann",
        email: "max@example.com",
        telephone: "089123",
        customTextfield: "note",
        customTextfield2: "note2",
      })
    );
    expect(customerData.firstName).toBe("Anna");
    expect(customerData.lastName).toBe("Mustermann");
    expect(customerData.mailAddress).toBe("max@example.com");
    expect(customerData.telephoneNumber).toBe("089123");
    expect(customerData.customTextfield).toBe("note");
    expect(customerData.customTextfield2).toBe("note2");
  });

  it("does not copy placeholder email onto customerData", () => {
    const customerData = new CustomerData("", "", "", "", "", "");
    applyAppointmentContactToCustomerData(
      customerData,
      baseAppointment({ email: PLACEHOLDER_RESERVE_EMAIL })
    );
    expect(customerData.mailAddress).toBe("");
  });

  it("detects missing required fields for the target scope", () => {
    const appointment = baseAppointment({
      telephone: "",
      customTextfield: "",
      customTextfield2: "",
    });
    expect(hasMissingRequiredContact(appointment, {})).toBe(false);
    expect(
      hasMissingRequiredContact(appointment, {
        telephoneActivated: true,
        telephoneRequired: true,
      })
    ).toBe(true);
    expect(
      hasMissingRequiredContact(appointment, {
        customTextfield2Activated: true,
        customTextfield2Required: true,
      })
    ).toBe(true);
    expect(
      hasMissingRequiredContact(
        baseAppointment({ email: PLACEHOLDER_RESERVE_EMAIL }),
        {}
      )
    ).toBe(true);
    expect(
      hasMissingRequiredContact(
        baseAppointment({ customTextfield: PLACEHOLDER_RESERVE_EMAIL }),
        {
          customTextfieldActivated: true,
          customTextfieldRequired: true,
        }
      )
    ).toBe(false);
  });
});
