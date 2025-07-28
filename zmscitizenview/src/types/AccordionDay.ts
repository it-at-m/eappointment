export type HourRow = {
  hour: number;
  times: number[];
  officeId: number;
};

export type DayPartRow = {
  part: "am" | "pm";
  times: number[];
  officeId: number;
};

export interface AccordionDay {
  date: Date;
  dateString: string;
  label: string;
  appointmentsCount: number;
  hourRows: HourRow[];
  dayPartRows: DayPartRow[];
}
