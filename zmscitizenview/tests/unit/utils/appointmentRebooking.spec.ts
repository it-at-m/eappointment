import type { AppointmentDTO } from "@/api/models/AppointmentDTO";
import type { GlobalState } from "@/types/GlobalState";

import { beforeEach, describe, expect, it, vi } from "vitest";

import { updateAppointment } from "@/api/ZMSAppointmentAPI";
import { CustomerData } from "@/types/CustomerData";
import {
  continueRebookingAfterReserve,
  copyRebookedContactOntoAppointment,
  fillCustomerDataFromRebookedAppointment,
  setRebookData,
  targetScopeForRebooking,
} from "@/utils/appointmentRebooking";

vi.mock("@/api/ZMSAppointmentAPI", () => ({
  updateAppointment: vi.fn(),
}));

const globalState: GlobalState = {
  baseUrl: "https://www.muenchen.de",
  accessToken: null,
  isLoggedIn: false,
  isLoadingAuthentication: false,
};

const rebookedAppointment = {
  processId: "old",
  authKey: "oldkey",
  timestamp: 1,
  familyName: "Max Mustermann",
  email: "max@example.com",
  telephone: "0891234567",
  customTextfield: "note",
  customTextfield2: "note2",
  officeId: "1",
  scope: {},
  subRequestCounts: [],
  serviceId: "1",
  serviceName: "Service",
  serviceCount: 1,
} as AppointmentDTO;

const reservedAppointment = {
  processId: "new-1",
  authKey: "newkey",
  timestamp: 2,
  familyName: "",
  email: "test@muenchen.de",
  officeId: "1",
  scope: {},
  subRequestCounts: [],
  serviceId: "1",
  serviceName: "Service",
  serviceCount: 1,
} as AppointmentDTO;

describe("appointmentRebooking", () => {
  beforeEach(() => {
    vi.mocked(updateAppointment).mockReset();
    vi.mocked(updateAppointment).mockResolvedValue({
      processId: "new-1",
    } as AppointmentDTO);
  });

  it("copies old contact onto the reserved appointment", () => {
    const appointment = { ...reservedAppointment };
    copyRebookedContactOntoAppointment(appointment, rebookedAppointment);
    expect(appointment.familyName).toBe("Max Mustermann");
    expect(appointment.email).toBe("max@example.com");
    expect(appointment.telephone).toBe("0891234567");
    expect(appointment.customTextfield).toBe("note");
    expect(appointment.customTextfield2).toBe("note2");
  });

  it("fills customerData from the old appointment", () => {
    const customerData = new CustomerData("", "", "", "", "", "");
    fillCustomerDataFromRebookedAppointment(customerData, rebookedAppointment);
    expect(customerData.firstName).toBe("Max");
    expect(customerData.lastName).toBe("Mustermann");
    expect(customerData.mailAddress).toBe("max@example.com");
  });

  it("prefers the selected office scope for the target", () => {
    const providerScope = { id: "p", provider: null, shortName: "p" };
    const appointmentScope = { id: "a", provider: null, shortName: "a" };
    expect(
      targetScopeForRebooking(
        { scope: providerScope },
        { scope: appointmentScope }
      )
    ).toBe(providerScope);
    expect(
      targetScopeForRebooking(undefined, { scope: appointmentScope })
    ).toBe(appointmentScope);
  });

  it("opens contact when the target scope still needs a required field", () => {
    const goToContact = vi.fn();
    const prepareUpdate = vi.fn();
    continueRebookingAfterReserve({
      appointment: { ...reservedAppointment },
      rebookedAppointment: { ...rebookedAppointment, customTextfield2: "" },
      customerData: new CustomerData("", "", "", "", "", ""),
      targetScope: {
        id: "1",
        provider: null,
        shortName: "test",
        customTextfield2Activated: true,
        customTextfield2Required: true,
      },
      globalState,
      prepareUpdate,
      goToContact,
      goToOverview: vi.fn(),
      handleUpdateError: vi.fn(),
    });
    expect(goToContact).toHaveBeenCalledTimes(1);
    expect(prepareUpdate).not.toHaveBeenCalled();
    expect(updateAppointment).not.toHaveBeenCalled();
  });

  it("updates immediately when the target scope is already complete", async () => {
    const goToOverview = vi.fn();
    const goToContact = vi.fn();
    await continueRebookingAfterReserve({
      appointment: { ...reservedAppointment },
      rebookedAppointment: { ...rebookedAppointment },
      customerData: new CustomerData("", "", "", "", "", ""),
      targetScope: {
        id: "1",
        provider: null,
        shortName: "test",
        telephoneActivated: true,
        telephoneRequired: true,
      },
      globalState,
      prepareUpdate: vi.fn(),
      goToContact,
      goToOverview,
      handleUpdateError: vi.fn(),
    });
    expect(updateAppointment).toHaveBeenCalledTimes(1);
    expect(goToOverview).toHaveBeenCalledTimes(1);
    expect(goToContact).not.toHaveBeenCalled();
  });

  it("falls back to contact when the skip-contact update fails", async () => {
    vi.mocked(updateAppointment).mockResolvedValueOnce({
      errorCode: "invalidTelephone",
      errorMessage: "invalid",
      lastModified: 0,
    });
    const handleUpdateError = vi.fn();
    await setRebookData({
      appointment: { ...reservedAppointment },
      rebookedAppointment: { ...rebookedAppointment },
      customerData: new CustomerData("", "", "", "", "", ""),
      globalState,
      prepareUpdate: vi.fn(),
      goToContact: vi.fn(),
      goToOverview: vi.fn(),
      handleUpdateError,
    });
    expect(handleUpdateError).toHaveBeenCalledTimes(1);
  });
});
