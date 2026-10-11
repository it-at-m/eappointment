import { Combinable } from "@/api/models/Combinable";
import { Service } from "@/api/models/Service";
import { OfficeImpl } from "@/types/OfficeImpl";
import { ServiceImpl } from "@/types/ServiceImpl";
import { SubService } from "@/types/SubService";

/**
 * Restore main + subservice counts/providers onto a catalog service after login
 * resume. selectedServiceMap survives OAuth; combinable subServices do not, so the
 * calendar would otherwise keep both serviceIds while peer/office checks only see
 * the main service (invalidLocationAndServiceCombination).
 */
export function hydrateServiceFromSelectedMap(
  service: ServiceImpl,
  selectedServiceMap: Map<string, number>,
  catalogServices: Service[],
  getProvidersForService: (
    serviceId: string,
    providerIds: string[] | null
  ) => OfficeImpl[]
): void {
  const mainId = String(service.id);
  const mainCount = selectedServiceMap.get(mainId);
  if (mainCount != undefined) {
    service.count = mainCount;
  }
  service.providers = getProvidersForService(mainId, null);

  const selectionOrder: string[] = [];
  for (const [id, count] of selectedServiceMap) {
    if (String(id) !== mainId && count > 0) {
      selectionOrder.push(String(id));
    }
  }
  service.subServiceSelectionOrder = selectionOrder;

  if (!service.combinable) {
    service.subServices = buildSubServicesFromMapOnly(
      selectionOrder,
      selectedServiceMap,
      catalogServices,
      getProvidersForService
    );
    return;
  }

  const combinable = { ...(service.combinable as Combinable) };
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

  // Keep map-only subs that combinable omitted (e.g. after catalog shape drift).
  const known = new Set(fromCombinable.map((sub) => String(sub.id)));
  for (const id of selectionOrder) {
    if (known.has(id)) {
      continue;
    }
    const catalog = catalogServices.find(
      (candidate) => String(candidate.id) === id
    );
    if (!catalog) {
      continue;
    }
    fromCombinable.push(
      new SubService(
        id,
        catalog.name,
        catalog.maxQuantity,
        getProvidersForService(id, null),
        selectedServiceMap.get(id) ?? 0
      )
    );
  }

  service.subServices = fromCombinable;
}

function buildSubServicesFromMapOnly(
  selectionOrder: string[],
  selectedServiceMap: Map<string, number>,
  catalogServices: Service[],
  getProvidersForService: (
    serviceId: string,
    providerIds: string[] | null
  ) => OfficeImpl[]
): SubService[] {
  const subs: SubService[] = [];
  for (const id of selectionOrder) {
    const catalog = catalogServices.find(
      (candidate) => String(candidate.id) === id
    );
    if (!catalog) {
      continue;
    }
    subs.push(
      new SubService(
        id,
        catalog.name,
        catalog.maxQuantity,
        getProvidersForService(id, null),
        selectedServiceMap.get(id) ?? 0
      )
    );
  }
  return subs;
}
