import type { SelectedAppointmentProvider } from "@/types/ProvideInjectTypes";
import type { Ref } from "vue";

import { computed, inject, onBeforeUnmount, onMounted, ref } from "vue";

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

  const nowMs = ref<number>(Date.now());
  let timer: number | undefined;

  onMounted(() => {
    timer = window.setInterval(() => {
      nowMs.value = Date.now();
    }, 1000);
  });

  onBeforeUnmount(() => {
    if (timer) window.clearInterval(timer);
  });

  const remainingMs = computed<number | null>(() =>
    deadlineMs.value == null
      ? null
      : Math.max(0, deadlineMs.value - nowMs.value)
  );

  const isExpired = computed<boolean>(
    () => remainingMs.value !== null && remainingMs.value <= 0
  );

  const timeLeftString = computed<string>(() => {
    if (remainingMs.value == null) return "";
    const total = Math.floor(remainingMs.value / 1000);
    const m = Math.floor(total / 60);
    const s = total % 60;
    return `${m}:${s.toString().padStart(2, "0")}`;
  });

  return {
    isExpired,
    remainingMs,
    deadlineMs,
    nowMs,
    timeLeftString
  };
}
