import type { Ref } from "vue";

import { computed, onBeforeUnmount, onMounted, ref } from "vue";

/** Show the warning banner in the last minute before timeout (ZMSKVR-501). */
export const TIMEOUT_WARNING_WINDOW_MS = 60_000;

/** Ticks once per second so countdown text updates without a page reload. */
export function useNowTicker(intervalMs = 1000): Ref<number> {
  const nowMs = ref(Date.now());
  let timer: number | undefined;

  onMounted(() => {
    timer = window.setInterval(() => {
      nowMs.value = Date.now();
    }, intervalMs);
  });

  onBeforeUnmount(() => {
    if (timer !== undefined) {
      window.clearInterval(timer);
    }
  });

  return nowMs;
}

export function remainingMsUntil(
  deadlineMs: number | null | undefined,
  nowMs: number
): number | null {
  if (deadlineMs == null || !Number.isFinite(deadlineMs)) {
    return null;
  }
  return Math.max(0, deadlineMs - nowMs);
}

/** True while time is left and within the last 60 seconds. */
export function isInTimeoutWarningWindow(
  remainingMs: number | null | undefined
): boolean {
  return (
    remainingMs != null &&
    remainingMs > 0 &&
    remainingMs <= TIMEOUT_WARNING_WINDOW_MS
  );
}

/**
 * Active deadline for the timeout warning banner:
 * - Termin (view 1): captcha JWT expiry
 * - Kontakt / Übersicht (views 2–3): reservation end
 * Banner is hidden on Leistung and after booking confirmation.
 */
export function useTimeoutWarning(options: {
  currentView: Ref<number>;
  captchaDeadlineMs: Ref<number | null>;
  reservationDeadlineMs: Ref<number | null>;
}) {
  const nowMs = useNowTicker();

  const activeDeadlineMs = computed<number | null>(() => {
    const view = options.currentView.value;
    if (view === 1) {
      return options.captchaDeadlineMs.value;
    }
    if (view === 2 || view === 3) {
      return options.reservationDeadlineMs.value;
    }
    return null;
  });

  const remainingMs = computed(() =>
    remainingMsUntil(activeDeadlineMs.value, nowMs.value)
  );

  const showTimeoutWarning = computed(() =>
    isInTimeoutWarningWindow(remainingMs.value)
  );

  const remainingSeconds = computed(() => {
    if (remainingMs.value == null) return 0;
    return Math.max(1, Math.ceil(remainingMs.value / 1000));
  });

  return {
    nowMs,
    activeDeadlineMs,
    remainingMs,
    remainingSeconds,
    showTimeoutWarning,
  };
}
