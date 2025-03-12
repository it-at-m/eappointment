export interface AltchaStateEventDetail {
  payload?: string;
  state: "pending" | "verified" | "failed";
}

export interface AltchaStateEvent extends CustomEvent<AltchaStateEventDetail> {}

export type AltchaWidget = HTMLElement & {
  addEventListener(
    type: "statechange",
    listener: (event: AltchaStateEvent) => void
  ): void;
  removeEventListener(
    type: "statechange",
    listener: (event: AltchaStateEvent) => void
  ): void;
};
