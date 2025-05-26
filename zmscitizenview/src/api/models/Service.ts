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
   * Object where keys are order numbers ("1", "2", etc.) and values are objects
   * with a single key-value pair of serviceId => providerIds[]
   * @type {{ [key: string]: { [serviceId: string]: number[] } }}
   * @memberof Service
   */
  combinable?: { [key: string]: { [serviceId: string]: number[] } };
}
