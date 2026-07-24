import { OfficeAvailableTimeSlotsDTO } from "@/api/models/OfficeAvailableTimeSlotsDTO";

export interface AvailableCalendarDayDTO {
  date: string;
  providerIDs: string;
  offices: OfficeAvailableTimeSlotsDTO[];
}

export interface AvailableCalendarDTO {
  startDate: string;
  endDate: string;
  slotsStartDate?: string;
  slotsEndDate?: string;
  prevBookableDate?: string | null;
  nextBookableDate?: string | null;
  availableDays: AvailableCalendarDayDTO[];
}
