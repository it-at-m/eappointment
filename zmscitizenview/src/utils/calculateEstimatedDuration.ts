import { OfficeImpl } from "@/types/OfficeImpl";
import { ServiceImpl } from "@/types/ServiceImpl";
import { SubService } from "@/types/SubService";

/**
 * Calculates the total estimated duration for the selected service and subservices for a given provider.
 * @param service The main service (with subServices and counts)
 * @param provider The selected provider/office
 * @returns The total estimated duration in minutes
 */
export function calculateEstimatedDuration(
  service: ServiceImpl | undefined,
  provider: OfficeImpl | undefined
): number | null {
  if (!service || !provider) return null;

  let total = 0;
  const mainProvider = service.providers?.find((p) => p.id == provider.id);

  if (
    !mainProvider ||
    !service.count ||
    !mainProvider.slots ||
    !mainProvider.slotTimeInMinutes
  ) {
    return null;
  }

  total +=
    service.count *
    mainProvider.slots *
    mainProvider.slotTimeInMinutes;

  if (service.subServices) {
    for (const sub of service.subServices) {
      if (sub.count > 0) {
        const subProvider = sub.providers?.find((p) => p.id == provider.id);

        if (
          !subProvider ||
          !subProvider.slots ||
          !subProvider.slotTimeInMinutes
        ) {
          return null;
        }

        total +=
          sub.count *
          subProvider.slots *
          subProvider.slotTimeInMinutes;
      }
    }
  }

  return total;
}
