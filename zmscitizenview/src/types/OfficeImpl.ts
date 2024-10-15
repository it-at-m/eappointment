import { Office } from "@/api/models/Office";
import { Scope } from "@/api/models/Scope";

export class OfficeImpl implements Office {
  id: string;

  name: string;

  scope?: Scope;

  maxSlotsPerAppointment?: string;

  slots?: number;

  constructor(
    id: string,
    name: string,
    scope: Scope | undefined,
    maxSlotsPerAppointment: string | undefined,
    slots: number | undefined
  ) {
    this.id = id;
    this.name = name;
    this.scope = scope;
    this.maxSlotsPerAppointment = maxSlotsPerAppointment;
    this.slots = slots;
  }
}
