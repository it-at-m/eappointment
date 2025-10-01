import { useDBSLoginWebcomponentPlugin } from "@/components/DBSLoginWebcomponentPlugin";
import AuthorizationEventDetails from "@/types/AuthorizationEventDetails";

const ACCESS_TOKEN_NAME = "appointment-access-token";

export function isAuthenticated(): boolean {
  return localStorage.getItem(ACCESS_TOKEN_NAME) !== null;
}

export function getAccessToken(): string | null {
  return localStorage.getItem(ACCESS_TOKEN_NAME);
}

export function getTokenData(accessToken: string): {
  email: string;
  given_name: string;
  family_name: string;
} {
  const accessTokenParts = accessToken.split(".");
  if (accessTokenParts.length !== 3) throw new Error("Invalid access token.");
  return JSON.parse(atob(accessTokenParts[1]));
}

export function registerAuthenticationHook(
  loginCallback: (accessToken: string) => void = () => {},
  logoutCallback: () => void = () => {}
): void {
  const currentAccessToken = localStorage.getItem(ACCESS_TOKEN_NAME);
  if (currentAccessToken) {
    loginCallback(currentAccessToken);
  }
  useDBSLoginWebcomponentPlugin(
    (authEventDetails: AuthorizationEventDetails) => {
      localStorage.setItem(ACCESS_TOKEN_NAME, authEventDetails.accessToken);
      loginCallback(authEventDetails.accessToken);
    },
    () => {
      localStorage.removeItem(ACCESS_TOKEN_NAME);
      logoutCallback();
    }
  );
}
