const formatterDate = new Intl.DateTimeFormat("de-DE", {
  timeZone: "Europe/Berlin",
  weekday: "long",
  year: "numeric",
  month: "numeric",
  day: "numeric",
});

const formatterTime = new Intl.DateTimeFormat("de-DE", {
  timeZone: "Europe/Berlin",
  hour: "numeric",
  minute: "numeric",
  hour12: false,
});

/**
 * Creates a formatted date string (Dienstag, 18.10.24, 15:30 Uhr).
 * @param time Timestamp
 * @returns Formatted date with time
 */
export function formatAppointmentDateTime(time: number): string {
  const date = new Date(time * 1000);
  if (isNaN(date.getTime())) return "";
  return formatterDate.format(date) + ", " + formatterTime.format(date);
}
