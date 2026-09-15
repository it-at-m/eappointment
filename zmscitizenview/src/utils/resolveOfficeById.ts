import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { Office } from "@/api/models/Office";
import { OfficeImpl } from "@/types/OfficeImpl";

const EMPTY_ADDRESS = {
  street: "",
  house_number: "",
  postal_code: "",
  city: "",
  hint: false,
};

export function toOfficeImpl(office: Office): OfficeImpl {
  return new OfficeImpl(
    String(office.id),
    office.name,
    office.address ?? EMPTY_ADDRESS,
    office.showAlternativeLocations,
    office.displayNameAlternatives ?? [],
    office.organization,
    office.organizationUnit,
    office.slotTimeInMinutes,
    office.disabledByServices,
    office.allowDisabledServicesMix,
    office.scope,
    office.slotsPerAppointment,
    office.slots,
    office.priority || 1,
    office.parentId,
    office.sharedBookingOfficeIds
  );
}

/**
 * Resolve the office that owns an appointment.
 * Shared-booking peers are collapsed to one Ort in the catalog, so the real
 * officeId (e.g. Ausbildung 10503) may be missing from `offices` while a
 * display peer (10489) still lists it in sharedBookingOfficeIds.
 */
export function resolveOfficeById(
  officeId: string | number | undefined | null,
  options: {
    offices?: Office[];
    providers?: Office[];
    appointment?: AppointmentDTO | null;
  } = {}
): OfficeImpl | undefined {
  if (officeId == null || officeId === "") {
    return undefined;
  }
  const id = String(officeId);

  const fromProviders = options.providers?.find(
    (office) => String(office.id) === id
  );
  if (fromProviders) {
    return toOfficeImpl(fromProviders);
  }

  const fromOffices = options.offices?.find(
    (office) => String(office.id) === id
  );
  if (fromOffices) {
    return toOfficeImpl(fromOffices);
  }

  const peer = (options.offices ?? []).find(
    (office) =>
      Array.isArray(office.sharedBookingOfficeIds) &&
      office.sharedBookingOfficeIds.map(Number).includes(Number(id))
  );
  if (peer) {
    const cloned = toOfficeImpl(peer);
    cloned.id = id;
    if (options.appointment?.scope) {
      cloned.scope = options.appointment.scope;
    }
    const provider = options.appointment?.scope?.provider as
      { displayName?: string; name?: string } | null | undefined;
    if (provider?.displayName || provider?.name) {
      cloned.name = provider.displayName || provider.name || cloned.name;
    }
    return cloned;
  }

  return officeFromAppointmentScope(id, options.appointment);
}

function officeFromAppointmentScope(
  id: string,
  appointment?: AppointmentDTO | null
): OfficeImpl | undefined {
  const provider = appointment?.scope?.provider as
    | {
        id?: string | number;
        displayName?: string;
        name?: string;
        contact?: {
          street?: string;
          streetNumber?: string;
          postalCode?: string;
          city?: string;
        };
      }
    | null
    | undefined;
  if (!provider || String(provider.id) !== id) {
    return undefined;
  }
  const contact = provider.contact ?? {};
  return new OfficeImpl(
    id,
    provider.displayName || provider.name || "",
    {
      street: contact.street ?? "",
      house_number: contact.streetNumber ?? "",
      postal_code: contact.postalCode ?? "",
      city: contact.city ?? "",
      hint: false,
    },
    false,
    [],
    "",
    undefined,
    0,
    undefined,
    undefined,
    appointment?.scope,
    undefined,
    undefined,
    1
  );
}
