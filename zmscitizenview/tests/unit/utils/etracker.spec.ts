import { afterEach, describe, expect, it, vi } from "vitest";

import {
  bookingPageName,
  ETRACKER_AREAS,
  ETRACKER_CATEGORY,
  ETRACKER_LOADER_ID,
  ETRACKER_PAGES,
  getEtrackerAccountKey,
  trackAppointmentPage,
  trackCitizenEvent,
} from "@/utils/etracker";

class FakeUserDefinedEvent {
  constructor(
    public objectName: string,
    public category: string,
    public action?: string,
    public type?: string
  ) {}
}

type EtrackerTestWindow = Window & {
  _etracker?: { sendEvent: (event: unknown) => void };
  _etrackerOnReady?: Array<() => void>;
  et_eC_Wrapper?: (params: unknown) => void;
  et_UserDefinedEvent?: typeof FakeUserDefinedEvent;
};

function testWindow(): EtrackerTestWindow {
  return window as EtrackerTestWindow;
}

function clearEtrackerGlobals(): void {
  const w = testWindow();
  delete w._etracker;
  delete w._etrackerOnReady;
  delete w.et_eC_Wrapper;
  delete w.et_UserDefinedEvent;
  document.getElementById(ETRACKER_LOADER_ID)?.remove();
}

describe("etracker", () => {
  afterEach(() => {
    clearEtrackerGlobals();
  });

  describe("bookingPageName", () => {
    it("maps wizard steps to stable pagenames", () => {
      expect(bookingPageName(0, false)).toBe(ETRACKER_PAGES.service);
      expect(bookingPageName(1, false)).toBe(ETRACKER_PAGES.appointment);
      expect(bookingPageName(2, false)).toBe(ETRACKER_PAGES.contact);
      expect(bookingPageName(3, false)).toBe(ETRACKER_PAGES.overview);
      expect(bookingPageName(5, false)).toBe(ETRACKER_PAGES.confirmation);
      expect(bookingPageName(99, false)).toBeUndefined();
    });

    it("distinguishes preconfirm and cancel on view 4", () => {
      expect(bookingPageName(4, false)).toBe(ETRACKER_PAGES.preconfirm);
      expect(bookingPageName(4, true)).toBe(ETRACKER_PAGES.cancel);
    });
  });

  describe("trackAppointmentPage", () => {
    it("does nothing when etracker is not on the page", () => {
      expect(() => trackAppointmentPage(ETRACKER_PAGES.service)).not.toThrow();
      expect(testWindow()._etrackerOnReady).toEqual([expect.any(Function)]);
    });

    it("sends a wrapper page view when etracker is ready", () => {
      const wrapper = vi.fn();
      testWindow()._etracker = { sendEvent: vi.fn() };
      testWindow().et_eC_Wrapper = wrapper;

      trackAppointmentPage(ETRACKER_PAGES.service);

      expect(wrapper).toHaveBeenCalledWith({
        et_pagename: ETRACKER_PAGES.service,
        et_areas: ETRACKER_AREAS,
      });
    });

    it("includes the account key from the Magnolia loader script", () => {
      const loader = document.createElement("script");
      loader.id = ETRACKER_LOADER_ID;
      loader.setAttribute("data-secure-code", "account-from-host");
      document.body.appendChild(loader);

      expect(getEtrackerAccountKey()).toBe("account-from-host");

      const wrapper = vi.fn();
      testWindow()._etracker = { sendEvent: vi.fn() };
      testWindow().et_eC_Wrapper = wrapper;

      trackAppointmentPage(ETRACKER_PAGES.contact);

      expect(wrapper).toHaveBeenCalledWith({
        et_et: "account-from-host",
        et_pagename: ETRACKER_PAGES.contact,
        et_areas: ETRACKER_AREAS,
      });
    });

    it("flushes a queued page view when etracker becomes ready", () => {
      const wrapper = vi.fn();
      trackAppointmentPage(ETRACKER_PAGES.appointment);

      testWindow()._etracker = { sendEvent: vi.fn() };
      testWindow().et_eC_Wrapper = wrapper;
      testWindow()._etrackerOnReady?.forEach((fn) => fn());

      expect(wrapper).toHaveBeenCalledWith({
        et_pagename: ETRACKER_PAGES.appointment,
        et_areas: ETRACKER_AREAS,
      });
    });
  });

  describe("trackCitizenEvent", () => {
    it("does nothing when etracker is not on the page", () => {
      expect(() =>
        trackCitizenEvent({
          object: "Buchung",
          action: "success",
          type: "zms-appointment",
        })
      ).not.toThrow();
    });

    it("sends a user-defined event when etracker is ready", () => {
      const sendEvent = vi.fn();
      testWindow()._etracker = { sendEvent };
      testWindow().et_UserDefinedEvent = FakeUserDefinedEvent;

      trackCitizenEvent({
        object: "Buchung",
        action: "success",
        type: "zms-appointment",
      });

      expect(sendEvent).toHaveBeenCalledTimes(1);
      const event = sendEvent.mock.calls[0][0] as FakeUserDefinedEvent;
      expect(event).toMatchObject({
        objectName: "Buchung",
        category: ETRACKER_CATEGORY,
        action: "success",
        type: "zms-appointment",
      });
    });

    it("does not send when the event constructor is missing", () => {
      const sendEvent = vi.fn();
      testWindow()._etracker = { sendEvent };

      trackCitizenEvent({
        object: "Buchung",
        action: "success",
        type: "zms-appointment",
      });

      expect(sendEvent).not.toHaveBeenCalled();
    });
  });
});
