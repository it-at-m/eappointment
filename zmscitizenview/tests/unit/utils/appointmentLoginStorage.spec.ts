import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

import {
  clearAppointmentAuthHashSession,
  clearAppointmentLocalStorage,
  encodeAppointmentAuthHash,
  getFreshLocalStorageUiData,
  LOCALSTORAGE_UI_TTL_MS,
  parseAppointmentHash,
  parseUiLocalStorage,
  resolveAppointmentAuthHash,
  saveUiToLocalStorage,
  setAppointmentAuthHashForLogin,
} from "@/utils/appointmentLoginStorage";
import {
  LOCALSTORAGE_PARAM_APPOINTMENT_DATA,
  SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH,
} from "@/utils/Constants";

describe("appointmentLoginStorage", () => {
  beforeEach(() => {
    localStorage.clear();
    sessionStorage.clear();
    history.replaceState(null, "", "/");
  });

  afterEach(() => {
    localStorage.clear();
    sessionStorage.clear();
  });

  describe("encodeAppointmentAuthHash / parseAppointmentHash", () => {
    it("round-trips processId and authKey", () => {
      const encoded = encodeAppointmentAuthHash("12345", "secret");
      expect(parseAppointmentHash(encoded)).toEqual({
        id: "12345",
        authKey: "secret",
      });
    });

    it("accepts base64 without padding", () => {
      const encoded = encodeAppointmentAuthHash("1", "ab").replace(/=+$/, "");
      expect(parseAppointmentHash(encoded)).toEqual({ id: "1", authKey: "ab" });
    });

    it("returns null for invalid hash", () => {
      expect(parseAppointmentHash("not-valid-base64!!!")).toBeNull();
      expect(
        parseAppointmentHash(btoa(JSON.stringify({ id: "1" })))
      ).toBeNull();
    });
  });

  describe("setAppointmentAuthHashForLogin", () => {
    it("writes sessionStorage and replaces hash without navigating", () => {
      const replaceStateSpy = vi.spyOn(history, "replaceState");

      setAppointmentAuthHashForLogin("99", "key");

      const encoded = encodeAppointmentAuthHash("99", "key");
      expect(
        sessionStorage.getItem(SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH)
      ).toBe(encoded);
      expect(replaceStateSpy).toHaveBeenCalledWith(
        null,
        "",
        `#/appointment/${encoded}`
      );
    });

    it("no-ops when credentials are missing", () => {
      setAppointmentAuthHashForLogin(undefined, "key");
      setAppointmentAuthHashForLogin("99", undefined);
      expect(
        sessionStorage.getItem(SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH)
      ).toBeNull();
    });
  });

  describe("UI localStorage", () => {
    const uiData = {
      timestamp: Date.now(),
      currentView: 2,
      selectedServiceId: "10",
      selectedServiceMap: { "10": 1 },
      selectedProviderId: "20",
      selectedTimeslot: 1700000000,
    };

    it("saves and reads fresh UI data", () => {
      saveUiToLocalStorage(uiData);
      expect(getFreshLocalStorageUiData()).toEqual(uiData);
    });

    it("returns null when TTL expired", () => {
      saveUiToLocalStorage({
        ...uiData,
        timestamp: Date.now() - LOCALSTORAGE_UI_TTL_MS - 1,
      });
      expect(getFreshLocalStorageUiData()).toBeNull();
      expect(
        localStorage.getItem(LOCALSTORAGE_PARAM_APPOINTMENT_DATA)
      ).toBeNull();
    });

    it("parses legacy selectedService/selectedProvider objects as IDs only", () => {
      const parsed = parseUiLocalStorage(
        JSON.stringify({
          timestamp: 1,
          currentView: 3,
          selectedService: { id: "legacy-svc", name: "Secret name" },
          selectedProvider: { id: "legacy-off", slotsPerAppointment: "1" },
          selectedServiceMap: { "legacy-svc": 2 },
          appointment: { authKey: "must-not-leak" },
          customerData: { email: "x@y.z" },
          captchaToken: "tok",
          telephoneNumber: "+491234567890",
          customTextfield: "Bemerkung",
        })
      );
      expect(parsed).toEqual({
        timestamp: 1,
        currentView: 3,
        selectedServiceId: "legacy-svc",
        selectedServiceMap: { "legacy-svc": 2 },
        selectedProviderId: "legacy-off",
        selectedTimeslot: 0,
      });
      expect(parsed).not.toHaveProperty("appointment");
      expect(parsed).not.toHaveProperty("customerData");
      expect(parsed).not.toHaveProperty("captchaToken");
      expect(parsed).not.toHaveProperty("telephoneNumber");
      expect(parsed).not.toHaveProperty("customTextfield");
    });

    it("round-trips reservationStartMs", () => {
      const withTimer = { ...uiData, reservationStartMs: 1_700_000_000_000 };
      saveUiToLocalStorage(withTimer);
      expect(getFreshLocalStorageUiData()).toEqual(withTimer);
    });

    it("clears localStorage", () => {
      saveUiToLocalStorage(uiData);
      clearAppointmentLocalStorage();
      expect(
        localStorage.getItem(LOCALSTORAGE_PARAM_APPOINTMENT_DATA)
      ).toBeNull();
    });
  });

  describe("resolveAppointmentAuthHash", () => {
    it("prefers the prop hash over sessionStorage", () => {
      sessionStorage.setItem(
        SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH,
        "from-session"
      );
      expect(resolveAppointmentAuthHash("from-prop")).toBe("from-prop");
    });

    it("restores hash from sessionStorage into the URL", () => {
      const encoded = encodeAppointmentAuthHash("1", "k");
      sessionStorage.setItem(
        SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH,
        encoded
      );
      const replaceStateSpy = vi.spyOn(history, "replaceState");

      expect(resolveAppointmentAuthHash()).toBe(encoded);
      expect(replaceStateSpy).toHaveBeenCalledWith(
        null,
        "",
        `#/appointment/${encoded}`
      );
    });
  });

  describe("clearAppointmentAuthHashSession", () => {
    it("removes the session bridge key", () => {
      sessionStorage.setItem(SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH, "x");
      clearAppointmentAuthHashSession();
      expect(
        sessionStorage.getItem(SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH)
      ).toBeNull();
    });
  });
});
