import { describe, it, expect } from "vitest";
import { formatAppointmentDateTime, formatDayFromDate } from "../../../src/utils/formatAppointmentDateTime";

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

describe("formatDayFromDate", () => {
  it("formats dates correctly", () => {
    const date = new Date('2025-05-15');
    expect(formatDayFromDate(date)).toBe('Donnerstag, 15.05.2025');
  });
});

describe("calculateEstimatedDuration", () => {
  it("returns empty string if number is not a date", () => {
    expect(formatAppointmentDateTime(9999999999999)).toBe("");
  });

  it("returns formated date string if number is a date", () => {
    expect(formatAppointmentDateTime(1)).toBe("Donnerstag, 01.01.1970, 01:00");
  });

  it("returns formated date string of now", () => {
    const dateAsNumber = Date.now();
    const date = new Date(dateAsNumber * 1000);
    expect(formatAppointmentDateTime(dateAsNumber)).toBe(formatterDate.format(date) + ", " + formatterTime.format(date));
  });
});
