import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

import {
  APPOINTMENT_CLICK_TRACK_INTERVAL,
  APPOINTMENT_TRACK_CATEGORY,
  APPOINTMENT_TRACK_EVENT,
  bookingFlow,
  resetAppointmentClickCount,
  trackAppointmentEvent,
  trackAppointmentScreenFromView,
  trackWidgetView,
} from "@/utils/trackAppointmentEvent";

describe("trackAppointmentEvent", () => {
  beforeEach(() => {
    delete window._etracker;
    delete window.et_UserDefinedEvent;
  });

  afterEach(() => {
    vi.restoreAllMocks();
    delete window._etracker;
    delete window.et_UserDefinedEvent;
  });

  it("dispatches a composed custom event with the payload", () => {
    const handler = vi.fn();
    document.addEventListener(APPOINTMENT_TRACK_EVENT, handler);

    trackAppointmentEvent({
      object: "reserved",
      action: "success",
      flow: "new",
      slot_ui: "calendar",
    });

    expect(handler).toHaveBeenCalledTimes(1);
    const event = handler.mock.calls[0][0] as CustomEvent;
    expect(event.bubbles).toBe(true);
    expect(event.composed).toBe(true);
    expect(event.detail).toEqual({
      object: "reserved",
      action: "success",
      flow: "new",
      slot_ui: "calendar",
    });

    document.removeEventListener(APPOINTMENT_TRACK_EVENT, handler);
  });

  it("forwards object, category, and action to etracker", () => {
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

    trackAppointmentEvent({
      object: "preconfirmed",
      action: "success",
      flow: "rebooking",
    });

    expect(sendEvent).toHaveBeenCalledTimes(1);
    const forwarded = sendEvent.mock.calls[0][0] as FakeUserDefinedEvent;
    expect(forwarded).toBeInstanceOf(FakeUserDefinedEvent);
    expect(forwarded.objectName).toBe("preconfirmed");
    expect(forwarded.category).toBe(APPOINTMENT_TRACK_CATEGORY);
    expect(forwarded.action).toBe("success");
  });

  it("does not throw when etracker is missing", () => {
    expect(() =>
      trackAppointmentEvent({ object: "confirmed", action: "success" })
    ).not.toThrow();
  });

  it("emits a click on every 10th click and still emits other actions", () => {
    resetAppointmentClickCount();
    const handler = vi.fn();
    const sendEvent = vi.fn();
    class FakeUserDefinedEvent {
      objectName: string;
      action?: string;
      constructor(objectName: string, _category: string, action?: string) {
        this.objectName = objectName;
        this.action = action;
      }
    }
    window._etracker = { sendEvent };
    window.et_UserDefinedEvent = FakeUserDefinedEvent;
    document.addEventListener(APPOINTMENT_TRACK_EVENT, handler);

    const click = {
      object: "login" as const,
      action: "click" as const,
      widget: "appointment" as const,
    };
    for (let n = 1; n < APPOINTMENT_CLICK_TRACK_INTERVAL; n += 1) {
      trackAppointmentEvent(click);
    }
    expect(handler).not.toHaveBeenCalled();
    expect(sendEvent).not.toHaveBeenCalled();

    trackAppointmentEvent({ object: "contact", action: "view" });
    expect(handler).toHaveBeenCalledTimes(1);
    expect(handler.mock.calls[0][0].detail).toEqual({
      object: "contact",
      action: "view",
    });

    trackAppointmentEvent(click);
    expect(handler).toHaveBeenCalledTimes(2);
    expect(handler.mock.calls[1][0].detail).toEqual(click);
    expect(sendEvent).toHaveBeenCalledTimes(2);
    const forwarded = sendEvent.mock.calls[1][0] as FakeUserDefinedEvent;
    expect(forwarded.objectName).toBe("login");
    expect(forwarded.action).toBe("click");

    for (let n = 1; n < APPOINTMENT_CLICK_TRACK_INTERVAL; n += 1) {
      trackAppointmentEvent(click);
    }
    expect(handler).toHaveBeenCalledTimes(2);

    trackAppointmentEvent({
      object: "login",
      action: "click",
      widget: "appointment_detail",
    });
    expect(handler).toHaveBeenCalledTimes(3);
    expect(handler.mock.calls[2][0].detail).toEqual({
      object: "login",
      action: "click",
      widget: "appointment_detail",
    });

    document.removeEventListener(APPOINTMENT_TRACK_EVENT, handler);
  });

  it("does not throw when etracker sendEvent fails", () => {
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
  it("maps stepper views after service finder", () => {
    const handler = vi.fn();
    document.addEventListener(APPOINTMENT_TRACK_EVENT, handler);

    trackAppointmentScreenFromView(0);
    trackAppointmentScreenFromView(1);
    trackAppointmentScreenFromView(2);
    trackAppointmentScreenFromView(3);

    expect(handler.mock.calls.map((call) => call[0].detail)).toEqual([
      { object: "appointment_selection", action: "view" },
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
