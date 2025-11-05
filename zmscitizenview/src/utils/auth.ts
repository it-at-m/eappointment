import { ref } from "vue";

import { useDBSLoginWebcomponentPlugin } from "@/components/DBSLoginWebcomponentPlugin";
import AuthorizationEventDetails from "@/types/AuthorizationEventDetails";

export function getTokenData(accessToken: string): {
  email: string;
  given_name: string;
  family_name: string;
} {
  const accessTokenParts = accessToken.split(".");
  if (accessTokenParts.length !== 3) throw new Error("Invalid access token.");
  return JSON.parse(atob(accessTokenParts[1]));
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
