import { Scope } from "@/api/models/Scope";
import { SubRequestCount } from "@/api/models/SubRequestCount";

/**
 *
 * @export
 * @interface AppointmentDTO
 */
export interface AppointmentDTO {
  /**
   *
   * @type {string}
   * @memberof AppointmentDTO
   */
  processId: string;
  /**
   *
   * @type {number}
   * @memberof AppointmentDTO
   */
  timestamp: number;
  /**
   *
   * @type {string}
   * @memberof AppointmentDTO
   */
  authKey: string;
  /**
   *
   * @type {string}
   * @memberof AppointmentDTO
   */
  familyName: string;
  /**
   *
   * @type {string}
   * @memberof AppointmentDTO
   */
  email: string;
  /**
   *
   * @type {string}
   * @memberof AppointmentDTO
   */
  telephone?: string;
  /**
   *
   * @type {string}
   * @memberof AppointmentDTO
   */
  customTextfield?: string;
  /**
   *
   * @type {string}
   * @memberof AppointmentDTO
   */
  officeId: string;
  /**
   *
   * @type {Scope}
   * @memberof AppointmentDTO
   */
  scope: Scope;
  /**
   *
   * @type {any[]}
   * @memberof AppointmentDTO
   */
  subRequestCounts: SubRequestCount[];
  /**
   *
   * @type {string}
   * @memberof AppointmentDTO
   */
  serviceId: string;
  /**
   *
   * @type {number}
   * @memberof AppointmentDTO
   */
  serviceCount: number;
}
