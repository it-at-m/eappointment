/**
 * Privacy-safe booking analytics. Emits English, PII-free events that cross
 * Shadow DOM via document + composed CustomEvent. If etracker is present on
 * the host page, also forwards object, category, and action. The Events
 * report lists the object, so a reserved slot is new_calendar, new_list,
 * rebooking_calendar, or rebooking_list, and a confirmation is
 * new_confirmed or rebooking_confirmed. One visit in ten is kept, and that
 * visit emits every event in the chain. Never throws.
 */

export const APPOINTMENT_TRACK_EVENT = "zms-appointment-track";

export const APPOINTMENT_TRACK_CATEGORY = "appointment";

/** One visit in this many is tracked for its whole chain. */
export const APPOINTMENT_TRACK_SAMPLE_INTERVAL = 10;

const APPOINTMENT_TRACK_SAMPLE_KEY = "zms-appointment-track-sample";

let memorySample: boolean | undefined;

export function resetAppointmentTrackSample(): void {
  memorySample = undefined;
  try {
    sessionStorage.removeItem(APPOINTMENT_TRACK_SAMPLE_KEY);
  } catch {
    // Storage can be blocked. The in-memory decision is already cleared.
  }
}

export type AppointmentTrackObject =
  | "appointment"
  | "appointment_detail"
  | "appointment_slider"
  | "appointment_overview"
  | "service_finder"
  | "service_combination"
  | "timestamp_selection"
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
  1: "timestamp_selection",
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

function rollVisitSample(): boolean {
  return Math.floor(Math.random() * APPOINTMENT_TRACK_SAMPLE_INTERVAL) === 0;
}

function visitIsTracked(): boolean {
  try {
    const stored = sessionStorage.getItem(APPOINTMENT_TRACK_SAMPLE_KEY);
    if (stored === "1" || stored === "0") {
      return stored === "1";
    }
    const keep = rollVisitSample();
    sessionStorage.setItem(APPOINTMENT_TRACK_SAMPLE_KEY, keep ? "1" : "0");
    return keep;
  } catch {
    if (memorySample === undefined) {
      memorySample = rollVisitSample();
    }
    return memorySample;
  }
}

export function trackAppointmentEvent(payload: AppointmentTrackPayload): void {
  try {
    if (!visitIsTracked()) {
      return;
    }
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
      etrackerObject(payload),
      APPOINTMENT_TRACK_CATEGORY,
      payload.action
    )
  );
}

/**
 * The etracker Events report shows this string in the object column.
 * Reserved slots and confirmations are named so flow and view are visible there.
 */
function etrackerObject(payload: AppointmentTrackPayload): string {
  if (payload.object === "reserved" && payload.flow && payload.slot_ui) {
    return `${payload.flow}_${payload.slot_ui}`;
  }
  if (payload.object === "confirmed" && payload.flow) {
    return `${payload.flow}_confirmed`;
  }
  return payload.object;
}
