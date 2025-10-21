import { computed, ComputedRef } from "vue";

import { GlobalState } from "@/types/GlobalState";
import { useLogin } from "./auth";

export function useGlobalState(props: {
  baseUrl?: string | undefined;
  [key: string]: any;
}): ComputedRef<GlobalState> {
  const { isLoggedIn, accessToken, isLoadingAuthentication } = useLogin();
  return computed<GlobalState>(() => ({
    baseUrl: props.baseUrl,
    isLoggedIn: isLoggedIn.value,
    accessToken: accessToken.value,
    isLoadingAuthentication: isLoadingAuthentication.value,
  }));
}
