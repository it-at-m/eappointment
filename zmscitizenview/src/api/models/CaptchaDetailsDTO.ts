/**
 *
 * @export
 * @interface CaptchaDetailsDTO
 */
export interface CaptchaDetailsDTO {
  /**
   *
   * @type {string}
   * @memberof CaptchaDetailsDTO
   */
  siteKey: string;

  /**
   *
   * @type {string}
   * @memberof CaptchaDetailsDTO
   */
  captchaChallenge: string;

  /**
   *
   * @type {string}
   * @memberof CaptchaDetailsDTO
   */
  captchaVerify: string;

  /**
   *
   * @type {boolean}
   * @memberof CaptchaDetailsDTO
   */
  captchaEnabled: boolean;
}
