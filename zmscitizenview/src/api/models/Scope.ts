import { Provider } from "@/api/models/Provider";

/**
 *
 * @export
 * @interface Scope
 */
export interface Scope {
  /**
   *
   * @type {string | null}
   * @memberof Scope
   */
  id: string | null;
  /**
   *
   * @type {Provider | null}
   * @memberof Scope
   */
  provider: Provider | null;
  /**
   *
   * @type {string | null}
   * @memberof Scope
   */
  shortName: string | null;
  /**
   *
   * @type {boolean | null}
   * @memberof Scope
   */
  telephoneActivated?: boolean | null;
  /**
   *
   * @type {boolean | null}
   * @memberof Scope
   */
  telephoneRequired?: boolean | null;
  /**
   *
   * @type {boolean | null}
   * @memberof Scope
   */
  customTextfieldActivated?: boolean | null;
  /**
   *
   * @type {boolean | null}
   * @memberof Scope
   */
  customTextfieldRequired?: boolean | null;
  /**
   *
   * @type {string | null}
   * @memberof Scope
   */
  customTextfieldLabel?: string | null;
  /**
   *
   * @type {boolean | null}
   * @memberof Scope
   */
  customTextfield2Activated?: boolean | null;
  /**
   *
   * @type {boolean | null}
   * @memberof Scope
   */
  customTextfield2Required?: boolean | null;
  /**
   *
   * @type {string | null}
   * @memberof Scope
   */
  customTextfield2Label?: string | null;
  /**
   *
   * @type {boolean | null}
   * @memberof Scope
   */
  captchaActivatedRequired?: boolean | null;
  /**
   *
   * @type {string | null}
   * @memberof Scope
   */
  infoForAppointment?: string | null;
  /**
   *
   * @type {string | null}
   * @memberof Scope
   */
  infoForAllAppointments?: string | null;
  /**
   *
   * @type {string | null}
   * @memberof Scope
   */
  hint?: string | null;
}
