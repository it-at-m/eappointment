export type GlobalState = {
  baseUrl: string | undefined;
  accessToken: string | null;
  isLoggedIn: boolean;
  isLoadingAuthentication: boolean;
};
