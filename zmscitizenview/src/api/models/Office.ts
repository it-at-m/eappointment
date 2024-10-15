import {Scope} from "@/api/models/Scope";

/**
 *
 * @export
 * @interface Office
 */
export interface Office {
  /**
   *
   * @type {string}
   * @memberof Office
   */
  id: string;
  /**
   *
   * @type {string}
   * @memberof Office
   */
  name: string;
  /**
   *
   * @type {Scope}
   * @memberof Office
   */
  scope?: Scope;
  /**
   *
   * @type {string}
   * @memberof Office
   */
  maxSlotsPerAppointment?: string;
}
