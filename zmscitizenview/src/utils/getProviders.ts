import { Office } from "@/api/models/Office";
import { Relation } from "@/api/models/Relation";
import { OfficeImpl } from "@/types/OfficeImpl";

export type OfficeFromCatalogOverrides = {
  disabledByServices?: string[];
  slots?: number;
};

export function officeFromCatalog(
  office: Office,
  overrides?: OfficeFromCatalogOverrides
): OfficeImpl {
  return new OfficeImpl(
    office.id,
    office.name,
    office.address,
    office.showAlternativeLocations,
    office.displayNameAlternatives,
    office.organization,
    office.organizationUnit,
    office.slotTimeInMinutes,
    overrides && "disabledByServices" in overrides
      ? overrides.disabledByServices
      : office.disabledByServices,
    office.allowDisabledServicesMix,
    office.scope,
    office.slotsPerAppointment,
    overrides && "slots" in overrides ? overrides.slots : office.slots,
    office.priority || 1,
    office.parentId,
    office.sharedBookingOfficeIds
  );
}

/**
 * Creates a list of possible providers for a service.
 * @param serviceId The id of the service
 * @param providers Optinal list of allowed providers
 * @param relations List of all releations between services and providers
 * @param offices List of all providers
 * @returns List of all possible providers for a service
 */
export function getProviders(
  serviceId: string,
  providers: string[] | null,
  relations: Relation[],
  offices: Office[]
): OfficeImpl[] {
  const officesAtService = new Array<OfficeImpl>();
  relations.forEach((relation) => {
    if (relation.serviceId == serviceId) {
      const office = offices.find((office) => office.id == relation.officeId);
      if (office) {
        const foundOffice = officeFromCatalog(office);
        if (!providers || providers.includes(foundOffice.id.toString())) {
          foundOffice.slots = relation.slots;
          officesAtService.push(foundOffice);
        }
      }
    }
  });

  return officesAtService;
}
