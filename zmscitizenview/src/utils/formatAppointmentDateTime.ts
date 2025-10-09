const formatterDate = new Intl.DateTimeFormat("de-DE", {
  timeZone: "Europe/Berlin",
  weekday: "long",
  year: "numeric",
  month: "2-digit",
  day: "2-digit",
});

const formatterTime = new Intl.DateTimeFormat("de-DE", {
  timeZone: "Europe/Berlin",
  hour: "2-digit",
  minute: "2-digit",
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

/**
 * Formats a Date to a localized day string (e.g., Dienstag, 18.10.2024).
 */
export function formatDayFromDate(date: Date | undefined): string {
  if (!date) return "";
  if (isNaN(date.getTime())) return "";
  return formatterDate.format(date);
}

/**
 * Formats a UNIX timestamp (seconds) to a localized time string (e.g., 15:30).
 */
export function formatTimeFromUnix(time: number): string {
  const date = new Date(time * 1000);
  if (isNaN(date.getTime())) return "";
  return formatterTime.format(date);
}

export function convertDateToString(date: Date) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
}

export const berlinHourFormatter = new Intl.DateTimeFormat("de-DE", {
  timeZone: "Europe/Berlin",
  hour: "numeric",
  hour12: false,
});

export const formatterWeekday = new Intl.DateTimeFormat("de-DE", {
  weekday: "long",
});
