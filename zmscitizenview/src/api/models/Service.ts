import { Combinable } from "./Combinable";

/**
 *
 * @export
 * @interface Service
 */
export interface Service {
  /**
   *
   * @type {string}
   * @memberof Service
   */
  id: string;
  /**
   *
   * @type {string}
   * @memberof Service
   */
  name: string;
  /**
   *
   * @type {number}
   * @memberof Service
   */
  maxQuantity: number;
  /**
   * @type {Combinable}
   * @memberof Service
   */
  combinable?: Combinable;
}
