import type { AppointmentDTO } from "@/api/models/AppointmentDTO";
import type { GlobalState } from "@/types/GlobalState";

import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

import { updateAppointment } from "@/api/ZMSAppointmentAPI";
import { CustomerData } from "@/types/CustomerData";
import {
  persistUiIdsForLogin,
  requestLogin,
  startOidcLogin,
} from "@/utils/appointmentOidcLogin";
import {
  LOCALSTORAGE_PARAM_APPOINTMENT_DATA,
  SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH,
} from "@/utils/Constants";

vi.mock("@/api/ZMSAppointmentAPI", () => ({
  updateAppointment: vi.fn(),
}));

const globalState: GlobalState = {
  baseUrl: "https://www.muenchen.de",
  accessToken: null,
  isLoggedIn: false,
  isLoadingAuthentication: false,
};

const snapshot = {
  currentView: 2,
  selectedServiceId: "123",
  selectedProviderId: "789",
  selectedServiceMap: { "123": 1 },
  selectedTimeslot: 1640995200,
};

const appointment = {
  processId: "proc-1",
  authKey: "secret-key",
  timestamp: 1,
  familyName: "Mustermann",
  email: "max@example.com",
  officeId: "789",
  scope: {},
  subRequestCounts: [],
  serviceId: "123",
  serviceName: "Test Service",
  serviceCount: 1,
} as AppointmentDTO;

describe("appointmentOidcLogin", () => {
  beforeEach(() => {
    localStorage.clear();
    sessionStorage.clear();
    history.replaceState(null, "", "/");
    vi.mocked(updateAppointment).mockReset();
    vi.mocked(updateAppointment).mockResolvedValue(appointment);
  });

  afterEach(() => {
    localStorage.clear();
    sessionStorage.clear();
  });

  it("persistUiIdsForLogin stores stepper IDs without PII", () => {
    persistUiIdsForLogin(snapshot);
    const stored = JSON.parse(
      localStorage.getItem(LOCALSTORAGE_PARAM_APPOINTMENT_DATA) as string
    );
    expect(stored.selectedServiceId).toBe("123");
    expect(stored.selectedProviderId).toBe("789");
    expect(stored.currentView).toBe(2);
    expect(stored.selectedTimeslot).toBe(1640995200);
    expect(JSON.stringify(stored)).not.toContain("secret-key");
    expect(JSON.stringify(stored)).not.toContain("max@example.com");
  });

  it("persistUiIdsForLogin skips storage when service or office is missing", () => {
    persistUiIdsForLogin({
      ...snapshot,
      selectedServiceId: undefined,
    });
    expect(
      localStorage.getItem(LOCALSTORAGE_PARAM_APPOINTMENT_DATA)
    ).toBeNull();
  });

  it("startOidcLogin writes the auth hash and dispatches authorization-request", () => {
    const events: Event[] = [];
    const onAuth = (event: Event) => events.push(event);
    document.addEventListener("authorization-request", onAuth);

    startOidcLogin(snapshot, appointment);

    expect(
      sessionStorage.getItem(SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH)
    ).toBe(btoa(JSON.stringify({ id: "proc-1", authKey: "secret-key" })));
    expect(events).toHaveLength(1);
    document.removeEventListener("authorization-request", onAuth);
  });

  it("requestLogin copies contact onto the reserved appointment then starts OIDC", async () => {
    const customerData = new CustomerData(
      "Max",
      "Mustermann",
      "max@example.com",
      "089123456",
      "secret-note",
      ""
    );

    await requestLogin(snapshot, appointment, customerData, globalState);

    expect(updateAppointment).toHaveBeenCalledTimes(1);
    const updated = vi.mocked(updateAppointment).mock.calls[0][1];
    expect(updated.telephone).toBe("089123456");
    expect(updated.customTextfield).toBe("secret-note");
    expect(
      sessionStorage.getItem(SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH)
    ).toBe(btoa(JSON.stringify({ id: "proc-1", authKey: "secret-key" })));
    const stored = localStorage.getItem(LOCALSTORAGE_PARAM_APPOINTMENT_DATA);
    expect(stored).toBeTruthy();
    expect(stored).not.toContain("089123456");
    expect(stored).not.toContain("secret-note");
  });

  it("requestLogin still starts OIDC when the contact update fails", async () => {
    vi.mocked(updateAppointment).mockRejectedValue(new Error("network"));
    const customerData = new CustomerData("", "", "", "089123456", "", "");

    await requestLogin(snapshot, appointment, customerData, globalState);

    expect(
      sessionStorage.getItem(SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH)
    ).toBe(btoa(JSON.stringify({ id: "proc-1", authKey: "secret-key" })));
    expect(
      localStorage.getItem(LOCALSTORAGE_PARAM_APPOINTMENT_DATA)
    ).toBeTruthy();
  });

  it("requestLogin starts OIDC without an update when there is no reservation", async () => {
    const customerData = new CustomerData("", "", "", "", "", "");
    await requestLogin(snapshot, undefined, customerData, globalState);

    expect(updateAppointment).not.toHaveBeenCalled();
    expect(
      sessionStorage.getItem(SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH)
    ).toBeNull();
    expect(
      localStorage.getItem(LOCALSTORAGE_PARAM_APPOINTMENT_DATA)
    ).toBeTruthy();
  });
});
