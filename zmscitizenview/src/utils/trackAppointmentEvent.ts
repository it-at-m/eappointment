/**
 * Privacy-safe booking analytics. Emits English, PII-free events that cross
 * Shadow DOM via document + composed CustomEvent. If etracker is present on
 * the host page, also forwards object/category/action. Never throws.
 */

export const APPOINTMENT_TRACK_EVENT = "zms-appointment-track";

export const APPOINTMENT_TRACK_CATEGORY = "appointment";

export type AppointmentTrackObject =
  | "appointment"
  | "appointment_detail"
  | "appointment_slider"
  | "appointment_overview"
  | "service_finder"
  | "service_combination"
  | "appointment_selection"
  | "contact"
  | "summary"
  | "reserved"
  | "updated"
  | "preconfirmed"
  | "confirmed"
  | "cancelled"
  | "rebooking"
  | "login";

export type AppointmentTrackAction =
  "view" | "click" | "success" | "started" | "abandoned";

export type AppointmentTrackFlow = "new" | "rebooking";

export type AppointmentTrackSlotUi = "list" | "calendar";

export type AppointmentTrackWidget =
  | "appointment"
  | "appointment_detail"
  | "appointment_slider"
  | "appointment_overview";

export type AppointmentTrackPayload = {
  object: AppointmentTrackObject;
  action: AppointmentTrackAction;
  flow?: AppointmentTrackFlow;
  slot_ui?: AppointmentTrackSlotUi;
  widget?: AppointmentTrackWidget;
};

const BOOKING_SCREENS: Record<number, AppointmentTrackObject> = {
  1: "appointment_selection",
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

export function bookingFlow(isRebooking: boolean): AppointmentTrackFlow {
  return isRebooking ? "rebooking" : "new";
}

export function trackAppointmentScreenFromView(view: number): void {
  const object = BOOKING_SCREENS[view];
  if (!object) {
    return;
  }
  trackAppointmentEvent({ object, action: "view" });
}

export function trackWidgetView(widget: AppointmentTrackWidget): void {
  trackAppointmentEvent({ object: widget, action: "view" });
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

  sendEvent(
    new UserDefinedEvent(
      payload.object,
      APPOINTMENT_TRACK_CATEGORY,
      payload.action
    )
  );
}
