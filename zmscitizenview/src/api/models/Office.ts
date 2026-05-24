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
   * Group of office IDs; JumpIn with one auto-selects equivalent in group.
   * Legacy: boolean true = participates in mix (no group for cross-office preselection).
   *
   * @type {boolean | number[]}
   * @memberof Office
   */
  allowDisabledServicesMix?: boolean | number[];
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
  slotsPerAppointment?: string;
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
