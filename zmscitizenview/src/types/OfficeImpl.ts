import { Office } from "@/api/models/Office";
import { Scope } from "@/api/models/Scope";
import { Address } from "@/api/models/Address";

export class OfficeImpl implements Office {
  id: string;

  name: string;

  address: Address;

  displayNameAlternatives: string[];

  organization: string;

  organizationUnit?: string;

  slotTimeInMinutes: number;

  scope?: Scope;

  maxSlotsPerAppointment?: string;

  slots?: number;

  constructor(
    id: string,
    name: string,
    address: Address,
    displayNameAlternatives: string[],
    organization: string,
    organizationUnit: string | undefined,
    slotTimeInMinutes: number,
    scope: Scope | undefined,
    maxSlotsPerAppointment: string | undefined,
    slots: number | undefined
  ) {
    this.id = id;
    this.name = name;
    this.address = address;
    this.displayNameAlternatives = displayNameAlternatives;
    this.organization = organization;
    this.organizationUnit = organizationUnit;
    this.slotTimeInMinutes = slotTimeInMinutes;
    this.scope = scope;
    this.maxSlotsPerAppointment = maxSlotsPerAppointment;
    this.slots = slots;
  }
}
