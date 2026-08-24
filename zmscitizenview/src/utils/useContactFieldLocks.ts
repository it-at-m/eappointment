import type { AppointmentDTO } from "@/api/models/AppointmentDTO";
import type { MaybeRefOrGetter, Ref } from "vue";

import { computed, nextTick, onUpdated, toValue, watch } from "vue";

import {
  applyContactLocksToNativeControls,
  getContactFieldLocks,
} from "@/utils/contactFieldLocks";

export function useContactFieldLocks(options: {
  isRebooking: MaybeRefOrGetter<boolean>;
  appointment: MaybeRefOrGetter<AppointmentDTO | undefined>;
  rebookedAppointment: MaybeRefOrGetter<AppointmentDTO | undefined>;
  form: Ref<HTMLFormElement | null>;
}) {
  const previousAppointment = computed(
    () => toValue(options.rebookedAppointment) ?? toValue(options.appointment)
  );

  const locks = computed(() =>
    getContactFieldLocks(
      toValue(options.isRebooking),
      previousAppointment.value
    )
  );

  const syncNativeControls = async () => {
    await nextTick();
    applyContactLocksToNativeControls(options.form.value);
  };

  watch(
    locks,
    () => {
      void syncNativeControls();
    },
    { immediate: true, flush: "post", deep: true }
  );

  onUpdated(() => {
    void syncNativeControls();
  });

  return locks;
}
