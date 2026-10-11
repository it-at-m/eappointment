import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { Combinable } from "@/api/models/Combinable";
import { Service } from "@/api/models/Service";
import { OfficeImpl } from "@/types/OfficeImpl";
import { ServiceImpl } from "@/types/ServiceImpl";
import { SubService } from "@/types/SubService";

export type ServiceFromAppointmentOptions = {
  /**
   * Login resume: build every combinable peer (selected counts kept, others 0)
   * so Leistung does not wait on ServiceFinder's second catalog fetch.
   */
  expandCombinablePeers?: boolean;
};

/**
 * Resolve the booked service for an appointment overview/detail view.
 * Prefer the public catalog (variants, rootParentId). Fall back to
 * appointment.serviceName so internal/unpublished bookings still show Leistung.
 */
export function serviceFromAppointment(
  appointment: AppointmentDTO | undefined | null,
  catalogServices: Service[] | undefined | null,
  getProvidersForService: (serviceId: string) => OfficeImpl[],
  fallbackProvider?: OfficeImpl,
  options?: ServiceFromAppointmentOptions
): ServiceImpl | undefined {
  if (!appointment?.serviceId) {
    return undefined;
  }

  const serviceId = String(appointment.serviceId);
  const fromCatalog = (catalogServices ?? []).find(
    (service) => String(service.id) === serviceId
  );

  if (fromCatalog) {
    const providers = getProvidersForService(serviceId);
    if (providers.length === 0 && fallbackProvider) {
      providers.push(fallbackProvider);
    }
    const combinable =
      options?.expandCombinablePeers && fromCatalog.combinable
        ? (JSON.parse(JSON.stringify(fromCatalog.combinable)) as Combinable)
        : fromCatalog.combinable;
    const selected = new ServiceImpl(
      serviceId,
      appointment.serviceName || fromCatalog.name,
      fromCatalog.maxQuantity,
      combinable,
      providers,
      [],
      appointment.serviceCount,
      fromCatalog.parentId ?? null,
      fromCatalog.variantId ?? null,
      fromCatalog.showOnStartPage,
      fromCatalog.variantOverwrite
    );
    selected.rootParentId = fromCatalog.rootParentId ?? fromCatalog.id;
    attachSubServices(
      selected,
      appointment,
      catalogServices ?? [],
      getProvidersForService,
      options?.expandCombinablePeers === true
    );
    return selected;
  }

  if (!appointment.serviceName) {
    return undefined;
  }

  const providers = getProvidersForService(serviceId);
  if (providers.length === 0 && fallbackProvider) {
    providers.push(fallbackProvider);
  }

  const selected = new ServiceImpl(
    serviceId,
    appointment.serviceName,
    appointment.serviceCount ?? 1,
    undefined,
    providers,
    [],
    appointment.serviceCount ?? 1,
    null,
    null,
    false
  );
  attachSubServices(
    selected,
    appointment,
    catalogServices ?? [],
    getProvidersForService,
    false
  );
  return selected;
}

function attachSubServices(
  selected: ServiceImpl,
  appointment: AppointmentDTO,
  catalogServices: Service[],
  getProvidersForService: (serviceId: string) => OfficeImpl[],
  expandCombinablePeers: boolean
): void {
  const counts = new Map(
    (appointment.subRequestCounts ?? []).map((entry) => [
      String(entry.id),
      entry.count ?? 0,
    ])
  );

  if (expandCombinablePeers && selected.combinable) {
    const mainId = String(selected.id);
    const combinable = selected.combinable;
    for (const key of Object.keys(combinable)) {
      if (Object.keys(combinable[key])[0] === mainId) {
        delete combinable[key];
      }
    }
    selected.subServices = Object.entries(combinable)
      .map(([, serviceObj]) => {
        const subId = Object.keys(serviceObj)[0];
        const catalog = catalogServices.find(
          (service) => String(service.id) === String(subId)
        );
        if (!catalog) {
          return undefined;
        }
        return new SubService(
          String(subId),
          catalog.name,
          catalog.maxQuantity,
          getProvidersForService(String(subId)),
          counts.get(String(subId)) ?? 0
        );
      })
      .filter((entry): entry is SubService => entry !== undefined);
    return;
  }

  if (counts.size === 0) {
    return;
  }
  selected.subServices = [];
  for (const [subId, count] of counts) {
    const fromCatalog = catalogServices.find(
      (service) => String(service.id) === subId
    );
    const name =
      fromCatalog?.name ??
      appointment.subRequestCounts?.find((entry) => String(entry.id) === subId)
        ?.name;
    if (!name) {
      continue;
    }
    selected.subServices.push(
      new SubService(
        subId,
        name,
        fromCatalog?.maxQuantity ?? count ?? 1,
        getProvidersForService(subId),
        count
      )
    );
  }
}
