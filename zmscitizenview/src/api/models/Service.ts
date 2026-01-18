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
  /**
   * @type {(string | number | null)}
   * @memberof Service
   */
  parentId: string | number | null;
  /**
   * @type {(number | null)}
   * @memberof Service
   */
  variantId: number | null;
  /**
   * @type {(boolean)}
   * @memberof Service
   */
  showOnStartPage?: boolean;
}
