import { OfficeImpl } from "@/types/OfficeImpl";
import { MAX_SLOTS } from "@/utils/Constants";

/** Subservice type used in slot calculations */
export interface SlotSubservice {
  id?: string;
  count: number;
  providers: OfficeImpl[];
}

/**
 * Gets the maximum slots required for a service across all providers.
 * We use MAX (not min) because we need to ensure the booking works at
 * providers with higher slot requirements. If a service takes 3 slots
 * at most providers but 1 slot at one provider, we count 3 slots to be
 * conservative with the slot limit.
 */
export const getMaxSlotOfProvider = (providers: OfficeImpl[]): number => {
  let maxSlot = 1; // Default to 1 slot minimum
  providers.forEach((provider) => {
    if (provider.slots && provider.slots > maxSlot) {
      maxSlot = provider.slots;
    }
  });
  return maxSlot;
};

/**
 * Gets the minimum slotsPerAppointment across all providers.
 * We use MIN (not max) because we need to ensure the booking works at
 * ALL providers that offer this service. If Provider A allows 10 slots
 * and Provider B allows 3 slots, we must limit to 3 to work everywhere.
 */
export const getMinSlotsPerAppointmentOfProvider = (
  providers: OfficeImpl[]
): number => {
  let minSlot = 0;
  providers.forEach((provider) => {
    if (
      provider.slotsPerAppointment &&
      parseInt(provider.slotsPerAppointment) > 0
    ) {
      const providerSlots = parseInt(provider.slotsPerAppointment);
      if (minSlot === 0 || providerSlots < minSlot) {
        minSlot = providerSlots;
      }
    }
  });
  return minSlot;
};

/**
 * Calculates the effective minSlotsPerAppointment, applying MAX_SLOTS as fallback.
 */
export const getEffectiveMinSlotsPerAppointment = (
  providers: OfficeImpl[]
): number => {
  const minSlotsOfProvider = getMinSlotsPerAppointmentOfProvider(providers);
  return minSlotsOfProvider > 0
    ? Math.min(minSlotsOfProvider, MAX_SLOTS)
    : MAX_SLOTS;
};

/**
 * Calculates the total slots used by a list of subservices.
 */
export const calculateSubserviceSlots = (
  subServices: SlotSubservice[] | undefined
): number => {
  if (!subServices) return 0;
  let slots = 0;
  subServices.forEach((subservice) => {
    if (subservice.count > 0) {
      slots += getMaxSlotOfProvider(subservice.providers) * subservice.count;
    }
  });
  return slots;
};

/**
 * Calculates the total slots for an appointment (main service + all subservices).
 */
export const calculateTotalSlots = (
  mainServiceProviders: OfficeImpl[],
  mainServiceCount: number,
  subServices: SlotSubservice[] | undefined
): number => {
  const mainSlots =
    getMaxSlotOfProvider(mainServiceProviders) * mainServiceCount;
  const subSlots = calculateSubserviceSlots(subServices);
  return mainSlots + subSlots;
};

/**
 * Calculates the maximum allowed count for a service based on slot constraints.
 *
 * @param serviceSlots - Slots required per unit of this service
 * @param maxQuantity - The service's own quantity limit
 * @param minSlotsPerAppointment - Total slots allowed for the appointment
 * @param otherSlotsUsed - Slots already used by other services
 * @returns The maximum count allowed (at least 0)
 */
export const calculateMaxCountBySlots = (
  serviceSlots: number,
  maxQuantity: number,
  minSlotsPerAppointment: number,
  otherSlotsUsed: number
): number => {
  if (serviceSlots <= 0) return maxQuantity;

  const availableSlots = minSlotsPerAppointment - otherSlotsUsed;
  const maxCountBySlots = Math.floor(availableSlots / serviceSlots);

  return Math.max(0, Math.min(maxQuantity, maxCountBySlots));
};

/**
 * Checks if total slots exceed the limit.
 */
export const exceedsSlotLimit = (
  totalSlots: number,
  minSlotsPerAppointment: number
): boolean => {
  return minSlotsPerAppointment > 0 && totalSlots > minSlotsPerAppointment;
};

/**
 * Adjusts the main service count to fit within slot limits.
 * Returns the adjusted count and updated total slots.
 */
export const adjustMainServiceCount = (
  requestedCount: number,
  mainServiceProviders: OfficeImpl[],
  subServiceSlots: number,
  minSlotsPerAppointment: number
): { adjustedCount: number; totalSlots: number } => {
  const mainServiceSlots = getMaxSlotOfProvider(mainServiceProviders);
  const totalSlots = mainServiceSlots * requestedCount + subServiceSlots;

  if (!exceedsSlotLimit(totalSlots, minSlotsPerAppointment)) {
    return { adjustedCount: requestedCount, totalSlots };
  }

  const maxMainCount = Math.floor(
    (minSlotsPerAppointment - subServiceSlots) / mainServiceSlots
  );
  const adjustedCount = Math.max(1, Math.min(requestedCount, maxMainCount));
  const adjustedTotalSlots = mainServiceSlots * adjustedCount + subServiceSlots;

  return { adjustedCount, totalSlots: adjustedTotalSlots };
};

/**
 * Adjusts a subservice count to fit within slot limits.
 * Returns the adjusted count and updated total slots.
 */
export const adjustSubserviceCount = (
  requestedCount: number,
  subserviceProviders: OfficeImpl[],
  mainServiceSlots: number,
  otherSubserviceSlots: number,
  minSlotsPerAppointment: number
): { adjustedCount: number; totalSlots: number } => {
  const subServiceSlots = getMaxSlotOfProvider(subserviceProviders);
  const totalSlots =
    mainServiceSlots + otherSubserviceSlots + subServiceSlots * requestedCount;

  if (!exceedsSlotLimit(totalSlots, minSlotsPerAppointment)) {
    return { adjustedCount: requestedCount, totalSlots };
  }

  const maxSubCount = Math.floor(
    (minSlotsPerAppointment - mainServiceSlots - otherSubserviceSlots) /
      subServiceSlots
  );
  const adjustedCount = Math.max(0, Math.min(requestedCount, maxSubCount));
  const adjustedTotalSlots =
    mainServiceSlots + otherSubserviceSlots + subServiceSlots * adjustedCount;

  return { adjustedCount, totalSlots: adjustedTotalSlots };
};

/**
 * Calculates slots used by other subservices (excluding a specific one).
 */
export const calculateOtherSubserviceSlots = (
  subServices: SlotSubservice[] | undefined,
  excludeId: string
): number => {
  if (!subServices) return 0;
  let slots = 0;
  subServices.forEach((s) => {
    if (s.id !== excludeId && s.count > 0) {
      slots += getMaxSlotOfProvider(s.providers) * s.count;
    }
  });
  return slots;
};
