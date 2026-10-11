import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { Service } from "@/api/models/Service";
import { OfficeImpl } from "@/types/OfficeImpl";
import { ServiceImpl } from "@/types/ServiceImpl";
import { SubService } from "@/types/SubService";

/**
 * Resolve the booked service for an appointment overview/detail view.
 * Prefer the public catalog (variants, rootParentId). Fall back to
 * appointment.serviceName so internal/unpublished bookings still show Leistung.
 */
export function serviceFromAppointment(
  appointment: AppointmentDTO | undefined | null,
  catalogServices: Service[] | undefined | null,
  getProvidersForService: (serviceId: string) => OfficeImpl[],
  fallbackProvider?: OfficeImpl
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
    const selected = new ServiceImpl(
      serviceId,
      appointment.serviceName || fromCatalog.name,
      fromCatalog.maxQuantity,
      fromCatalog.combinable,
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
      getProvidersForService
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
    getProvidersForService
  );
  return selected;
}

function attachSubServices(
  selected: ServiceImpl,
  appointment: AppointmentDTO,
  catalogServices: Service[],
  getProvidersForService: (serviceId: string) => OfficeImpl[]
): void {
  const subRequestCounts = appointment.subRequestCounts ?? [];
  if (subRequestCounts.length === 0) {
    return;
  }
  selected.subServices = [];
  for (const subRequestCount of subRequestCounts) {
    const subId = String(subRequestCount.id);
    const fromCatalog = catalogServices.find(
      (service) => String(service.id) === subId
    );
    const name = fromCatalog?.name ?? subRequestCount.name;
    if (!name) {
      continue;
    }
    selected.subServices.push(
      new SubService(
        subId,
        name,
        fromCatalog?.maxQuantity ?? subRequestCount.count ?? 1,
        getProvidersForService(subId),
        subRequestCount.count
      )
    );
  }
}
