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
   * @type {string}
   * @memberof Scope
   */
  telephoneActivated: string;
  /**
   *
   * @type {any}
   * @memberof Scope
   */
  telephoneRequired: any;
  /**
   *
   * @type {string}
   * @memberof Scope
   */
  customTextfieldActivated: string;
  /**
   *
   * @type {any}
   * @memberof Scope
   */
  customTextfieldRequired: any;
  /**
   *
   * @type {string}
   * @memberof Scope
   */
  customTextfieldLabel: string;
  /**
   *
   * @type {string}
   * @memberof Scope
   */
  captchaActivatedRequired: string;
}
