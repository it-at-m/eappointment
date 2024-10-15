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
   *
   * @type {Array<Array<string>>}
   * @memberof Service
   */
  combinable?: Array<Array<string>>;
}
