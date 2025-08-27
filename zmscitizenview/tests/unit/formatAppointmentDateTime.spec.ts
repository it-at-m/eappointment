import { describe, it, expect } from "vitest";
import { formatAppointmentDateTime } from "../../src/utils/formatAppointmentDateTime";

const formatterDate = new Intl.DateTimeFormat("de-DE", {
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

describe("calculateEstimatedDuration", () => {
  it("returns empty string if number is not a date", () => {
    expect(formatAppointmentDateTime(9999999999999)).toBe("");
  });

  it("returns formated date string if number is a date", () => {
    expect(formatAppointmentDateTime(1)).toBe("Donnerstag, 1.1.1970, 01:00");
  });

  it("returns formated date string of now", () => {
    const date = new Date(2025,8,1,12,0);
    const dateAsNumber = Math.floor(date.getTime() / 1000);
    expect(formatAppointmentDateTime(dateAsNumber)).toBe(formatterDate.format(date) + ", " + formatterTime.format(date));
  });
});
