import { Combinable } from "@/api/models/Combinable";
import { Service } from "@/api/models/Service";
import { OfficeImpl } from "@/types/OfficeImpl";
import { ServiceImpl } from "@/types/ServiceImpl";
import { SubService } from "@/types/SubService";

/**
 * Rebuild the full combinable peer list on a service that only has selected
 * subServices (e.g. login resume via serviceFromAppointment). Counts come from
 * selectedServiceMap; peers stay at 0 until the citizen increases them.
 */
export function expandSubServicesFromCombinable(
  service: ServiceImpl,
  selectedServiceMap: Map<string, number>,
  catalogServices: Service[],
  getProvidersForService: (
    serviceId: string,
    providerIds: string[] | null
  ) => OfficeImpl[]
): void {
  if (!service.combinable) {
    return;
  }

  const mainId = String(service.id);
  // Clone so deleting the self-entry does not mutate the shared catalog map.
  const combinable = JSON.parse(
    JSON.stringify(service.combinable)
  ) as Combinable;

  for (const key of Object.keys(combinable)) {
    const serviceObj = combinable[key];
    const serviceId = Object.keys(serviceObj)[0];
    if (serviceId === mainId) {
      delete combinable[key];
    }
  }

  const fromCombinable = Object.entries(combinable)
    .map(([, serviceObj]) => {
      const [[subServiceId, providers]] = Object.entries(serviceObj);
      const catalog = catalogServices.find(
        (candidate) => String(candidate.id) === String(subServiceId)
      );
      if (!catalog) {
        return undefined;
      }
      const count = selectedServiceMap.get(String(subServiceId)) ?? 0;
      return new SubService(
        String(subServiceId),
        catalog.name,
        catalog.maxQuantity,
        getProvidersForService(String(subServiceId), providers.map(String)),
        count
      );
    })
    .filter((entry): entry is SubService => entry !== undefined);

  const known = new Set(fromCombinable.map((sub) => String(sub.id)));
  for (const [id, count] of selectedServiceMap) {
    if (String(id) === mainId || count <= 0 || known.has(String(id))) {
      continue;
    }
    const catalog = catalogServices.find(
      (candidate) => String(candidate.id) === String(id)
    );
    if (!catalog) {
      continue;
    }
    fromCombinable.push(
      new SubService(
        String(id),
        catalog.name,
        catalog.maxQuantity,
        getProvidersForService(String(id), null),
        count
      )
    );
  }

  service.subServices = fromCombinable;
  service.combinable = combinable;
}
