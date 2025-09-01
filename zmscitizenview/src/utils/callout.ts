/**
 * Shared utility for callout type handling
 */

export type CalloutType = "error" | "warning" | "success" | "info";

/**
 * Converts a string error type to a valid callout type
 */
export const toCalloutType = (errorType?: string): CalloutType => {
  switch (errorType) {
    case "warning":
      return "warning";
    case "info":
      return "info";
    case "success":
      return "success";
    case "error":
    default:
      return "error";
  }
};
