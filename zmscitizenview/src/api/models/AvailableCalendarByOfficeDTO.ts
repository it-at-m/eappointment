import { OfficeAvailableTimeSlotsDTO } from "@/api/models/OfficeAvailableTimeSlotsDTO";

export interface AvailableCalendarDayDTO {
  time: string;
  providerIDs: string;
  offices: OfficeAvailableTimeSlotsDTO[];
}

export interface AvailableCalendarByOfficeDTO {
  startDate: string;
  endDate: string;
  slotsStartDate?: string;
  slotsEndDate?: string;
  prevBookableDate?: string | null;
  nextBookableDate?: string | null;
  availableDays: AvailableCalendarDayDTO[];
}
