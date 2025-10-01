export const KEYCLOAK_AUTH_LEVEL1 = "level1";
export const KEYCLOAK_AUTH_LEVEL3 = "level3";
export const KEYCLOAK_AUTH_LEVEL4 = "level4";

export type KEYCLOAK_AUTH_LEVEL =
  | typeof KEYCLOAK_AUTH_LEVEL1
  | typeof KEYCLOAK_AUTH_LEVEL3
  | typeof KEYCLOAK_AUTH_LEVEL4;

export default class AuthorizationEventDetails {
  constructor(
    buergerName: string,
    buergerMail: string,
    loginProvider: string,
    trustLevel: KEYCLOAK_AUTH_LEVEL,
    accessToken: string
  ) {
    this.buergerName = buergerName;
    this.buergerMail = buergerMail;
    this.loginProvider = loginProvider;
    this.trustLevel = trustLevel;
    this.accessToken = accessToken;
  }

  buergerName: string;
  buergerMail: string;
  loginProvider: string;
  trustLevel: KEYCLOAK_AUTH_LEVEL;
  accessToken: string;
}
