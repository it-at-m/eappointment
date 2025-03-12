/**
 *
 * @export
 * @interface CaptchaDetails
 */
export interface CaptchaDetails {
  /**
   *
   * @type {string}
   * @memberof CaptchaDetails
   */
  algorithm: string;

  /**
   *
   * @type {string}
   * @memberof CaptchaDetails
   */
  challenge: string;

  /**
   *
   * @type {number}
   * @memberof CaptchaDetails
   */
  maxnumber?: number;

  /**
   *
   * @type {string}
   * @memberof CaptchaDetails
   */
  salt: string;

  /**
   *
   * @type {string}
   * @memberof CaptchaDetails
   */
  signature: string;
}
