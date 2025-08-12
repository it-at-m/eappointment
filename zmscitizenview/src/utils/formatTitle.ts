import {AppointmentDTO} from "@/api/models/AppointmentDTO";

/**
 * Creates a formatted multiline string.
 * @param appointment
 * @returns Formatted multiline string
 */
export function formatTitle(appointment: AppointmentDTO): string  {
  const serviceTitle =
    appointment.serviceCount + "x " + appointment.serviceName;
  const subserviceTitle = appointment.subRequestCounts
    .map((subCount) => subCount.count + "x " + subCount.name)
    .join("\n");
  return serviceTitle + "\n" + subserviceTitle;
}
