/**
 *
 * @export
 * @interface AvailableTimeSlotsDTO
 */
export interface AvailableTimeSlotsDTO {
  /**
   *
   * @type {Array<number>}
   * @memberof AvailableTimeSlotsDTO
   */
  appointmentTimestamps: Array<number>;
  /**
   *
   * @type {number}
   * @memberof AvailableTimeSlotsDTO
   */
  lastModified: number;
}
