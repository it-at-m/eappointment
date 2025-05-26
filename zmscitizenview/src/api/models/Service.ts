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
   * @type {{ [key: string]: { [serviceId: string]: number[] } }}
   * @memberof Service
   */
  combinable?: { [key: string]: { [serviceId: string]: number[] } };
}
