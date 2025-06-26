import { Address } from "@/api/models/Address";
import { Scope } from "@/api/models/Scope";

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
   * @type {Address}
   * @memberof Office
   */
  address: Address;
  /**
   *
   * @type {boolean}
   * @memberof Office
   */
  showAlternativeLocations: boolean;
  /**
   *
   * @type {string[]}
   * @memberof Office
   */
  displayNameAlternatives: string[];
  /**
   *
   * @type {string}
   * @memberof Office
   */
  organization: string;
  /**
   *
   * @type {string}
   * @memberof Office
   */
  organizationUnit?: string;
  /**
   *
   * @type {number}
   * @memberof Office
   */
  slotTimeInMinutes: number;
  /**
   *
   * @type {string[]}
   * @memberof Office
   */
  disabledByServices?: string[];
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
  /**
   *
   * @type {number}
   * @memberof Office
   */
  slots?: number;
  /**
   *
   * @type {number}
   * @memberof Office
   */
  priority?: number;
}
