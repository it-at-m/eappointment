import { Office } from "@/api/models/Office";
import { Relation } from "@/api/models/Relation";
import { OfficeImpl } from "@/types/OfficeImpl";

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
        const foundOffice: OfficeImpl = new OfficeImpl(
          office.id,
          office.name,
          office.address,
          office.showAlternativeLocations,
          office.displayNameAlternatives,
          office.organization,
          office.organizationUnit,
          office.slotTimeInMinutes,
          office.disabledByServices,
          office.scope,
          office.maxSlotsPerAppointment,
          office.slots,
          office.priority || 1
        );

        if (!providers || providers.includes(foundOffice.id.toString())) {
          foundOffice.slots = relation.slots;
          officesAtService.push(foundOffice);
        }
      }
    }
  });

  return officesAtService;
}
