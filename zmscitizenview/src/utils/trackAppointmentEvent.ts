/**
 * Privacy-safe booking analytics. Emits English, PII-free events that cross
 * Shadow DOM via document + composed CustomEvent. If etracker is present on
 * the host page, also forwards the same payload. Never throws.
 */

export const APPOINTMENT_TRACK_EVENT = "zms-appointment-track";

export const APPOINTMENT_TRACK_CATEGORY = "appointment_booking";

export type AppointmentTrackStep =
  "service" | "timeslot" | "contact" | "summary";

export type AppointmentTrackPayload =
  | { event: "appointment_step"; step: AppointmentTrackStep }
  | { event: "appointment_booked" }
  | { event: "appointment_cancelled" }
  | { event: "appointment_confirmed" };

const BOOKING_STEPS: Record<number, AppointmentTrackStep> = {
  0: "service",
  1: "timeslot",
  2: "contact",
  3: "summary",
};

declare global {
  interface Window {
    _etracker?: {
      sendEvent?: (event: unknown) => void;
    };
    et_UserDefinedEvent?: new (
      objectName: string,
      category: string,
      action?: string,
      type?: string
    ) => unknown;
  }
}

export function trackAppointmentStepFromView(view: number): void {
  const step = BOOKING_STEPS[view];
  if (!step) {
    return;
  }
  trackAppointmentEvent({ event: "appointment_step", step });
}

export function trackAppointmentEvent(payload: AppointmentTrackPayload): void {
  try {
    document.dispatchEvent(
      new CustomEvent(APPOINTMENT_TRACK_EVENT, {
        detail: payload,
        bubbles: true,
        composed: true,
      })
    );
    sendToEtracker(payload);
  } catch {
    // Tracking must never break the booking flow.
  }
}

function sendToEtracker(payload: AppointmentTrackPayload): void {
  const sendEvent = window._etracker?.sendEvent;
  const UserDefinedEvent = window.et_UserDefinedEvent;
  if (!sendEvent || !UserDefinedEvent) {
    return;
  }

  const [objectName, action] = toEtrackerArgs(payload);
  sendEvent(
    new UserDefinedEvent(objectName, APPOINTMENT_TRACK_CATEGORY, action)
  );
}

function toEtrackerArgs(
  payload: AppointmentTrackPayload
): [objectName: string, action: string] {
  if (payload.event === "appointment_step") {
    return [payload.step, "step"];
  }
  if (payload.event === "appointment_booked") {
    return ["appointment", "booked"];
  }
  if (payload.event === "appointment_cancelled") {
    return ["appointment", "cancelled"];
  }
  return ["appointment", "confirmed"];
}
