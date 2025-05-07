export interface AltchaStateEventDetail {
  state: "verifying" | "verified" | "error";
}

export interface AltchaStateEvent extends CustomEvent<AltchaStateEventDetail> {}

export type AltchaWidget = HTMLElement & {
  challengeurl: string;
  verifyurl?: string;
  expire?: string;
  delay?: string;
  debug?: boolean;
  configure(options: { strings: Record<string, string> }): void;
  addEventListener(
    type: "statechange",
    listener: (event: AltchaStateEvent) => void
  ): void;
  removeEventListener(
    type: "statechange",
    listener: (event: AltchaStateEvent) => void
  ): void;
};
