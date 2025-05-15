import { OfficeAvailableTimeSlotsDTO } from "@/api/models/OfficeAvailableTimeSlotsDTO";

/**
 *
 * @export
 * @interface AvailableTimeSlotsByOfficeDTO
 */
export interface AvailableTimeSlotsByOfficeDTO {
  /**
   *
   * @type {Array<number>}
   * @memberof AvailableTimeSlotsDTO
   */
  offices: OfficeAvailableTimeSlotsDTO[];
  /**
   *
   * @type {number}
   * @memberof AvailableTimeSlotsDTO
   */
  lastModified: number;
}
