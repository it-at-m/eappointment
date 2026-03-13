import { ref, Ref } from "vue";

/** Last offices-and-services (or status) fetch — for DEV callout screenshots */
export const lastApiFailureDebug: Ref<string> = ref("");

const DEBUG_MAX_LEN = 3500;

export function recordApiFailureDebug(info: string): void {
  lastApiFailureDebug.value = info.slice(0, DEBUG_MAX_LEN);
}

export function clearApiFailureDebug(): void {
  lastApiFailureDebug.value = "";
}
