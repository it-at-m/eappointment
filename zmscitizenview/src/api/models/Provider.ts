import {Contact} from "@/api/models/Contact";

/**
 *
 * @export
 * @interface Provider
 */
export interface Provider {
  /**
   *
   * @type {string}
   * @memberof Provider
   */
  id: string;
  /**
   *
   * @type {string}
   * @memberof Provider
   */
  source: string;
  /**
   *
   * @type {Contact}
   * @memberof Provider
   */
  contact?: Contact;
}
