import { Provider } from "@/api/models/Provider";

/**
 *
 * @export
 * @interface Scope
 */
export interface Scope {
  /**
   *
   * @type {string}
   * @memberof Scope
   */
  id: string;
  /**
   *
   * @type {Provider}
   * @memberof Scope
   */
  provider: Provider;
  /**
   *
   * @type {string}
   * @memberof Scope
   */
  shortName: string;
  /**
   *
   * @type {boolean}
   * @memberof Scope
   */
  telephoneActivated?: boolean;
  /**
   *
   * @type {boolean}
   * @memberof Scope
   */
  telephoneRequired?: boolean;
  /**
   *
   * @type {boolean}
   * @memberof Scope
   */
  customTextfieldActivated?: boolean;
  /**
   *
   * @type {boolean}
   * @memberof Scope
   */
  customTextfieldRequired?: boolean;
  /**
   *
   * @type {string}
   * @memberof Scope
   */
  customTextfieldLabel?: string;
  /**
   *
   * @type {boolean}
   * @memberof Scope
   */
  captchaActivatedRequired?: boolean;
  /**
   *
   * @type {string}
   * @memberof Scope
   */
  displayInfo?: string;
}
