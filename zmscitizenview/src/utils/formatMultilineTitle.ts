import { AppointmentDTO } from "@/api/models/AppointmentDTO";

/**
 * Creates a formatted multiline string.
 * @param appointment
 * @returns Formatted multiline string
 */
export function formatMultilineTitle(appointment: AppointmentDTO): string {
  const serviceTitle =
    appointment.serviceCount + "x " + appointment.serviceName;
  const subserviceTitle = appointment.subRequestCounts
    .sort((a, b) => a.name.localeCompare(b.name, "de"))
    .map((subCount) => subCount.count + "x " + subCount.name)
    .join("\n");
  return [serviceTitle, subserviceTitle].filter(Boolean).join("\n");
}
