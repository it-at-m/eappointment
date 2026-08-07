import { LocalStorageAppointmentData } from "@/types/LocalStorageAppointmentData";
import { LOCALSTORAGE_PARAM_APPOINTMENT_DATA } from "@/utils/Constants";

/** Opaque booking-resume IDs only — never pass Appointment/Office/Customer graphs. */
export type BookingResumeInput = Omit<LocalStorageAppointmentData, "timestamp">;

/**
 * Build a plain resume DTO from primitives. Keeps storage free of Scope /
 * telephone* / customer PII fields that live on AppointmentDTO / Office.
 */
export function buildBookingResume(
  input: BookingResumeInput
): LocalStorageAppointmentData {
  return {
    timestamp: Date.now(),
    currentView: input.currentView,
    selectedServiceId: input.selectedServiceId,
    selectedServiceMap: { ...input.selectedServiceMap },
    selectedProviderId: input.selectedProviderId,
    selectedTimeslot: input.selectedTimeslot,
    appointmentProcessId: input.appointmentProcessId,
    appointmentDisplayNumber: input.appointmentDisplayNumber ?? null,
    appointmentTimestamp: input.appointmentTimestamp,
    appointmentAuthKey: input.appointmentAuthKey,
    appointmentOfficeId: input.appointmentOfficeId,
    appointmentServiceId: input.appointmentServiceId,
    appointmentServiceName: input.appointmentServiceName,
    appointmentServiceCount: input.appointmentServiceCount,
    captchaToken: input.captchaToken,
  };
}

/**
 * Client-side resume payload after optional citizen login.
 * Callers must pass opaque booking IDs only — never Appointment/Office graphs
 * or customer PII (name, mail, telephone).
 */
export function persistBookingResume(data: LocalStorageAppointmentData): void {
  localStorage.setItem(
    LOCALSTORAGE_PARAM_APPOINTMENT_DATA,
    JSON.stringify(data)
  );
}

export function readBookingResume(): LocalStorageAppointmentData | null {
  const raw = localStorage.getItem(LOCALSTORAGE_PARAM_APPOINTMENT_DATA);
  if (!raw) {
    return null;
  }
  try {
    const data = JSON.parse(raw) as LocalStorageAppointmentData;
    if (
      data.timestamp == undefined ||
      data.currentView == undefined ||
      data.selectedServiceId == undefined ||
      data.selectedProviderId == undefined ||
      data.appointmentProcessId == undefined ||
      data.appointmentAuthKey == undefined ||
      data.appointmentTimestamp == undefined ||
      data.appointmentOfficeId == undefined ||
      data.appointmentServiceId == undefined ||
      data.appointmentServiceName == undefined ||
      data.appointmentServiceCount == undefined
    ) {
      return null;
    }
    return data;
  } catch {
    return null;
  }
}

export function clearBookingResume(): void {
  localStorage.removeItem(LOCALSTORAGE_PARAM_APPOINTMENT_DATA);
}
