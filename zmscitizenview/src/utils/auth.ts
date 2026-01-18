import { ref } from "vue";

import { useDBSLoginWebcomponentPlugin } from "@/components/DBSLoginWebcomponentPlugin";
import AuthorizationEventDetails from "@/types/AuthorizationEventDetails";

function parseJwt(token: string) {
  const base64Url = token.split(".")[1];
  const base64 = base64Url.replace(/-/g, "+").replace(/_/g, "/");
  const jsonPayload = decodeURIComponent(
    window
      .atob(base64)
      .split("")
      .map(function (c) {
        return "%" + ("00" + c.charCodeAt(0).toString(16)).slice(-2);
      })
      .join("")
  );
  return JSON.parse(jsonPayload);
}

export function getTokenData(accessToken: string): {
  email?: string;
  given_name?: string;
  family_name?: string;
} {
  return parseJwt(accessToken);
}

export function useLogin() {
  const accessToken = ref<string | null>(null);
  const { loggedIn, loading } = useDBSLoginWebcomponentPlugin(
    (authEventDetails: AuthorizationEventDetails) => {
      accessToken.value = authEventDetails.accessToken;
    },
    () => {
      accessToken.value = null;
    }
  );
  return {
    isLoggedIn: loggedIn,
    isLoadingAuthentication: loading,
    accessToken,
  };
}
