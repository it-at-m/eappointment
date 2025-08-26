import { describe, it, expect } from "vitest";
// @ts-expect-error: Vue SFC import for test
import { formatTime } from "@/utils/formatTime";

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
    expect(formatTime(9999999999999)).toBe("");
  });

  it("returns formated date string if number is a date", () => {
    expect(formatTime(1)).toBe("Donnerstag, 1.1.1970, 01:00");
  });

  it("returns formated date string of now", () => {
    const date = new Date(2025,8,1,12,0);
    const dateAsNumber = date.getTime() / 1000;
    expect(formatTime(dateAsNumber)).toBe(formatterDate.format(date) + ", " + formatterTime.format(date));
  });
});
