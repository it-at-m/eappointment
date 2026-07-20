export const officesForProviderHourSnap = [
  { officeId: 1, appointments: [1750165200] }, // 15:00
  {
    officeId: 2,
    appointments: [1750154400, 1750158000, 1750161600], // 12, 13, 14
  },
];

export const officesForProviderHourSnapEqualDistance = [
  { officeId: 1, appointments: [1750158000] }, // 13:00
  { officeId: 2, appointments: [1750154400, 1750161600] }, // 12, 14
];

const JULY_2_2025_BASE_TIMESTAMP = 1751450400;

const generateAppointmentTimestamps = (
  count: number,
  startTimestamp = JULY_2_2025_BASE_TIMESTAMP,
  intervalSeconds = 300
): number[] =>
  Array.from(
    { length: count },
    (_, index) => startTimestamp + index * intervalSeconds
  );

export const officesForHourlyViewTest = [
  {
    officeId: 1,
    appointments: generateAppointmentTimestamps(20),
  },
  {
    officeId: 2,
    appointments: generateAppointmentTimestamps(
      20,
      JULY_2_2025_BASE_TIMESTAMP + 3600
    ),
  },
];

export const officesForDayPartViewTest = [
  {
    officeId: 1,
    appointments: generateAppointmentTimestamps(9),
  },
  {
    officeId: 2,
    appointments: generateAppointmentTimestamps(9, 1750165200),
  },
];

export const listViewHourNavigationSlots = [
  { officeId: 1, appointments: [1749560400, 1749564000] }, // 15:00, 16:00
];

export const listViewDayPartNavigationSlots = [
  { officeId: 1, appointments: [1749535200, 1749560400] }, // 08:00 am, 15:00 pm
];

export const offices10351880And10470 = [
  { officeId: 10351880, appointments: [1750118400] },
  { officeId: 10470, appointments: [1750118400] },
];

export const defaultMultiProviderOffices = [
  {
    officeId: 102522,
    appointments: [
      1747202400, 1747223100, 1747223400, 1747223700, 1747224000, 1747224300,
    ],
  },
  {
    officeId: 54261,
    appointments: [1747223100, 1747223400, 1747223700, 1747224000, 1747224300],
  },
  {
    officeId: 10489,
    appointments: [1747223100, 1747223400, 1747223700, 1747224000, 1747224300],
  },
];

export const officeOneMorningSlots = [
  { officeId: 1, appointments: [1747224600, 1747224900, 1747225200] },
];

export const officeOneAndTwoSlots = [
  { officeId: 1, appointments: [1747224600, 1747224900, 1747225200] },
  { officeId: 2, appointments: [1747223100, 1747223400, 1747223700] },
];

export const officeTwoAndThreeSlots = [
  {
    officeId: 2,
    appointments: [1747223100, 1747223400, 1747223700, 1747224000, 1747224300],
  },
  {
    officeId: 3,
    appointments: [1747223100, 1747223400, 1747223700, 1747224000, 1747224300],
  },
];

export type CalendarDayMock = {
  time: string;
  providerIDs: string;
  offices?: Array<{ officeId: number | string; appointments: number[] }>;
};

export function calendarResponse(
  days: CalendarDayMock[],
  defaultOffices = defaultMultiProviderOffices
) {
  const startDate = days[0]?.time ?? "2025-01-01";
  const endDate = days[days.length - 1]?.time ?? "2025-12-31";
  return {
    startDate,
    endDate,
    slotsStartDate: startDate,
    slotsEndDate: endDate,
    prevBookableDate: null,
    nextBookableDate: null,
    availableDays: days.map((day) => ({
      time: day.time,
      providerIDs: day.providerIDs,
      offices: day.offices ?? defaultOffices,
    })),
  };
}

export function setAvailableDays(
  wrapper: { vm: any },
  days: Array<{ time: string; providerIDs: string }>,
  offices: Array<{
    officeId: number | string;
    appointments: number[];
  }> = defaultMultiProviderOffices
) {
  wrapper.vm.availableDays = days;
  const map = new Map<
    string,
    Array<{ officeId: number | string; appointments: number[] }>
  >();
  for (const day of days) {
    map.set(day.time.slice(0, 10), offices);
  }
  wrapper.vm.appointmentsByDay = map;
}

export function setAppointmentsByDay(
  wrapper: { vm: any },
  entries: Array<
    [string, Array<{ officeId: number | string; appointments: number[] }>]
  >
) {
  wrapper.vm.appointmentsByDay = new Map(entries);
}
