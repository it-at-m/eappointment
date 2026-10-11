import type { AppointmentTrackPayload } from "@/utils/trackAppointmentEvent";

import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

import {
  APPOINTMENT_TRACK_CATEGORY,
  APPOINTMENT_TRACK_EVENT,
  bookingFlow,
  resetAppointmentTrackSample,
  trackAppointmentEvent,
  trackAppointmentScreenFromView,
  trackWidgetView,
} from "@/utils/trackAppointmentEvent";

const reserved: AppointmentTrackPayload = {
  object: "reserved",
  action: "success",
  flow: "new",
  slot_ui: "calendar",
};

function keepThisVisit(): void {
  vi.spyOn(Math, "random").mockReturnValue(0);
}

function dropThisVisit(): void {
  vi.spyOn(Math, "random").mockReturnValue(0.5);
}

describe("trackAppointmentEvent", () => {
  beforeEach(() => {
    resetAppointmentTrackSample();
    delete window._etracker;
    delete window.et_UserDefinedEvent;
  });

  afterEach(() => {
    vi.restoreAllMocks();
    resetAppointmentTrackSample();
    delete window._etracker;
    delete window.et_UserDefinedEvent;
  });

  it("dispatches a composed custom event with the payload", () => {
    keepThisVisit();
    const handler = vi.fn();
    document.addEventListener(APPOINTMENT_TRACK_EVENT, handler);

    trackAppointmentEvent(reserved);

    expect(handler).toHaveBeenCalledTimes(1);
    const event = handler.mock.calls[0][0] as CustomEvent;
    expect(event.bubbles).toBe(true);
    expect(event.composed).toBe(true);
    expect(event.detail).toEqual(reserved);

    document.removeEventListener(APPOINTMENT_TRACK_EVENT, handler);
  });

  it("names reserve and confirm events for the etracker events report", () => {
    keepThisVisit();
    const sendEvent = vi.fn();
    class FakeUserDefinedEvent {
      objectName: string;
      category: string;
      action?: string;
      constructor(objectName: string, category: string, action?: string) {
        this.objectName = objectName;
        this.category = category;
        this.action = action;
      }
    }
    window._etracker = { sendEvent };
    window.et_UserDefinedEvent = FakeUserDefinedEvent;

    trackAppointmentEvent(reserved);
    trackAppointmentEvent({
      object: "reserved",
      action: "success",
      flow: "new",
      slot_ui: "list",
    });
    trackAppointmentEvent({
      object: "reserved",
      action: "success",
      flow: "rebooking",
      slot_ui: "calendar",
    });
    trackAppointmentEvent({
      object: "reserved",
      action: "success",
      flow: "rebooking",
      slot_ui: "list",
    });
    trackAppointmentEvent({
      object: "confirmed",
      action: "success",
      flow: "new",
    });
    trackAppointmentEvent({
      object: "confirmed",
      action: "success",
      flow: "rebooking",
    });
    trackAppointmentEvent({ object: "rebooking", action: "started" });

    const forwarded = sendEvent.mock.calls.map(
      (call) => call[0] as FakeUserDefinedEvent
    );
    expect(forwarded.map((event) => event.objectName)).toEqual([
      "new_calendar",
      "new_list",
      "rebooking_calendar",
      "rebooking_list",
      "new_confirmed",
      "rebooking_confirmed",
      "rebooking",
    ]);
    expect(forwarded[0].category).toBe(APPOINTMENT_TRACK_CATEGORY);
    expect(forwarded[0].action).toBe("success");
  });

  it("does not throw when etracker is missing", () => {
    keepThisVisit();
    expect(() =>
      trackAppointmentEvent({ object: "confirmed", action: "success" })
    ).not.toThrow();
  });

  it("sends nothing for a visit that was left out", () => {
    const handler = vi.fn();
    document.addEventListener(APPOINTMENT_TRACK_EVENT, handler);
    const login = {
      object: "login" as const,
      action: "click" as const,
      widget: "appointment" as const,
    };
    const contact = { object: "contact" as const, action: "view" as const };

    dropThisVisit();
    trackAppointmentEvent(login);
    trackAppointmentEvent(contact);
    expect(handler).not.toHaveBeenCalled();

    vi.spyOn(Math, "random").mockReturnValue(0);
    trackAppointmentEvent(reserved);
    expect(handler).not.toHaveBeenCalled();

    document.removeEventListener(APPOINTMENT_TRACK_EVENT, handler);
  });

  it("keeps the whole chain once a visit is selected", () => {
    keepThisVisit();
    const handler = vi.fn();
    document.addEventListener(APPOINTMENT_TRACK_EVENT, handler);

    trackAppointmentEvent({ object: "appointment", action: "view" });
    vi.spyOn(Math, "random").mockReturnValue(0.5);
    trackAppointmentEvent({ object: "contact", action: "view" });
    trackAppointmentEvent(reserved);

    expect(handler.mock.calls.map((call) => call[0].detail)).toEqual([
      { object: "appointment", action: "view" },
      { object: "contact", action: "view" },
      reserved,
    ]);

    document.removeEventListener(APPOINTMENT_TRACK_EVENT, handler);
  });

  it("does not throw when etracker sendEvent fails", () => {
    keepThisVisit();
    window._etracker = {
      sendEvent: () => {
        throw new Error("blocked");
      },
    };
    window.et_UserDefinedEvent = class {
      constructor(
        _objectName: string,
        _category: string,
        _action?: string,
        _type?: string
      ) {}
    };

    expect(() =>
      trackAppointmentEvent({ object: "cancelled", action: "success" })
    ).not.toThrow();
  });
});

describe("trackAppointmentScreenFromView", () => {
  beforeEach(() => {
    resetAppointmentTrackSample();
    keepThisVisit();
  });

  afterEach(() => {
    vi.restoreAllMocks();
    resetAppointmentTrackSample();
  });

  it("maps stepper views after service finder", () => {
    const handler = vi.fn();
    document.addEventListener(APPOINTMENT_TRACK_EVENT, handler);

    trackAppointmentScreenFromView(0);
    trackAppointmentScreenFromView(1);
    trackAppointmentScreenFromView(2);
    trackAppointmentScreenFromView(3);

    expect(handler.mock.calls.map((call) => call[0].detail)).toEqual([
      { object: "timestamp_selection", action: "view" },
      { object: "contact", action: "view" },
      { object: "summary", action: "view" },
    ]);

    document.removeEventListener(APPOINTMENT_TRACK_EVENT, handler);
  });

  it("ignores result screens that are not stepper steps", () => {
    const handler = vi.fn();
    document.addEventListener(APPOINTMENT_TRACK_EVENT, handler);

    trackAppointmentScreenFromView(4);
    trackAppointmentScreenFromView(5);

    expect(handler).not.toHaveBeenCalled();
    document.removeEventListener(APPOINTMENT_TRACK_EVENT, handler);
  });
});

describe("trackWidgetView", () => {
  beforeEach(() => {
    resetAppointmentTrackSample();
    keepThisVisit();
  });

  afterEach(() => {
    vi.restoreAllMocks();
    resetAppointmentTrackSample();
  });

  it("emits a view for the web component", () => {
    const handler = vi.fn();
    document.addEventListener(APPOINTMENT_TRACK_EVENT, handler);

    trackWidgetView("appointment_detail");

    expect(handler.mock.calls[0][0].detail).toEqual({
      object: "appointment_detail",
      action: "view",
    });

    document.removeEventListener(APPOINTMENT_TRACK_EVENT, handler);
  });
});

describe("bookingFlow", () => {
  it("maps rebooking flag to flow", () => {
    expect(bookingFlow(false)).toBe("new");
    expect(bookingFlow(true)).toBe("rebooking");
  });
});
