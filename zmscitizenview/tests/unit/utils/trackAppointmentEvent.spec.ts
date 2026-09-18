import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";

import {
  APPOINTMENT_TRACK_CATEGORY,
  APPOINTMENT_TRACK_EVENT,
  trackAppointmentEvent,
  trackAppointmentStepFromView,
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

    trackAppointmentEvent({ event: "appointment_step", step: "service" });

    expect(handler).toHaveBeenCalledTimes(1);
    const event = handler.mock.calls[0][0] as CustomEvent;
    expect(event.bubbles).toBe(true);
    expect(event.composed).toBe(true);
    expect(event.detail).toEqual({
      event: "appointment_step",
      step: "service",
    });

    document.removeEventListener(APPOINTMENT_TRACK_EVENT, handler);
  });

  it("forwards to etracker when the host API is present", () => {
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

    trackAppointmentEvent({ event: "appointment_booked" });

    expect(sendEvent).toHaveBeenCalledTimes(1);
    const forwarded = sendEvent.mock.calls[0][0] as FakeUserDefinedEvent;
    expect(forwarded).toBeInstanceOf(FakeUserDefinedEvent);
    expect(forwarded.objectName).toBe("appointment");
    expect(forwarded.category).toBe(APPOINTMENT_TRACK_CATEGORY);
    expect(forwarded.action).toBe("booked");
  });

  it("does not throw when etracker is missing", () => {
    expect(() =>
      trackAppointmentEvent({ event: "appointment_confirmed" })
    ).not.toThrow();
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
      trackAppointmentEvent({ event: "appointment_cancelled" })
    ).not.toThrow();
  });
});

describe("trackAppointmentStepFromView", () => {
  it("maps stepper views to English step names", () => {
    const handler = vi.fn();
    document.addEventListener(APPOINTMENT_TRACK_EVENT, handler);

    trackAppointmentStepFromView(0);
    trackAppointmentStepFromView(1);
    trackAppointmentStepFromView(2);
    trackAppointmentStepFromView(3);

    expect(handler.mock.calls.map((call) => call[0].detail)).toEqual([
      { event: "appointment_step", step: "service" },
      { event: "appointment_step", step: "timeslot" },
      { event: "appointment_step", step: "contact" },
      { event: "appointment_step", step: "summary" },
    ]);

    document.removeEventListener(APPOINTMENT_TRACK_EVENT, handler);
  });

  it("ignores result screens that are not stepper steps", () => {
    const handler = vi.fn();
    document.addEventListener(APPOINTMENT_TRACK_EVENT, handler);

    trackAppointmentStepFromView(4);
    trackAppointmentStepFromView(5);

    expect(handler).not.toHaveBeenCalled();
    document.removeEventListener(APPOINTMENT_TRACK_EVENT, handler);
  });
});
