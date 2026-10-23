import type { SelectedAppointmentProvider } from "@/types/ProvideInjectTypes";
import type { Ref } from "vue";

import { computed, inject } from "vue";

import {
  isInTimeoutWarningWindow,
  remainingMsUntil,
  useNowTicker,
} from "@/utils/useTimeoutWarning";

export function useReservationTimer() {
  const { appointment } = inject<SelectedAppointmentProvider>("appointment")!;

  const reservationStartMs = inject<Ref<number | null>>("reservationStartMs")!;

  const reservationDurationMinutes = computed<number | undefined>(() => {
    const raw: unknown = (appointment.value as any)?.scope?.reservationDuration;
    const n = raw as number | undefined;
    return Number.isFinite(n) ? n : undefined;
  });

  const deadlineMs = computed<number | null>(() => {
    if (
      reservationStartMs.value == null ||
      reservationDurationMinutes.value == null
    )
      return null;
    return reservationStartMs.value + reservationDurationMinutes.value * 60_000;
  });

  const nowMs = useNowTicker();

  const remainingMs = computed<number | null>(() =>
    remainingMsUntil(deadlineMs.value, nowMs.value)
  );

  const isReservationExpired = computed<boolean>(
    () => remainingMs.value !== null && remainingMs.value <= 0
  );

  /** ZMSKVR-501: last minute of the reservation on Kontakt / Übersicht. */
  const showReservationTimeoutWarning = computed(() =>
    isInTimeoutWarningWindow(remainingMs.value)
  );

  const remainingSeconds = computed(() => {
    if (remainingMs.value == null) return 0;
    return Math.max(0, Math.ceil(remainingMs.value / 1000));
  });

  const timeLeftString = computed<string>(() => {
    if (remainingMs.value == null) return "";
    return `${remainingSeconds.value} Sekunden`;
  });

  return {
    isReservationExpired,
    remainingMs,
    remainingSeconds,
    deadlineMs,
    nowMs,
    timeLeftString,
    showReservationTimeoutWarning,
  };
}
