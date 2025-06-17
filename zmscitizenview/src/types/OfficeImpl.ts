import { Address } from "@/api/models/Address";
import { Office } from "@/api/models/Office";
import { Scope } from "@/api/models/Scope";

export class OfficeImpl implements Office {
  id: string;

  name: string;

  address: Address;

  showAlternativeLocations: boolean;

  displayNameAlternatives: string[];

  organization: string;

  organizationUnit?: string;

  slotTimeInMinutes: number;

  disabledByServices?: string[];

  scope?: Scope;

  maxSlotsPerAppointment?: string;

  slots?: number;

  priority: number;

  constructor(
    id: string,
    name: string,
    address: Address,
    showAlternativeLocations: boolean,
    displayNameAlternatives: string[],
    organization: string,
    organizationUnit: string | undefined,
    slotTimeInMinutes: number,
    disabledByServices: string[] | undefined,
    scope: Scope | undefined,
    maxSlotsPerAppointment: string | undefined,
    slots: number | undefined,
    priority: number = 1
  ) {
    this.id = id;
    this.name = name;
    this.address = address;
    this.showAlternativeLocations = showAlternativeLocations;
    this.displayNameAlternatives = displayNameAlternatives;
    this.organization = organization;
    this.organizationUnit = organizationUnit;
    this.slotTimeInMinutes = slotTimeInMinutes;
    this.disabledByServices = disabledByServices;
    this.scope = scope;
    this.maxSlotsPerAppointment = maxSlotsPerAppointment;
    this.slots = slots;
    this.priority = priority;
  }
}
