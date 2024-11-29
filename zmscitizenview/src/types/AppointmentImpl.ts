import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { Scope } from "@/api/models/Scope";
import { SubRequestCount } from "@/api/models/SubRequestCount";

export class AppointmentImpl implements AppointmentDTO {
  processId: string;

  timestamp: number;

  authKey: string;

  firstName?: string;

  familyName: string;

  email: string;

  telephone?: string;

  customTextfield?: string;

  officeId: string;

  scope: Scope;

  subRequestCounts: SubRequestCount[];

  serviceId: string;

  serviceCount: number;

  constructor(
    processId: string,
    timestamp: number,
    authKey: string,
    firstName: string | undefined,
    familyName: string,
    email: string,
    telephone: string | undefined,
    customTextfield: string | undefined,
    officeId: string,
    scope: Scope,
    subRequestCounts: any[],
    serviceId: string,
    serviceCount: number
  ) {
    this.processId = processId;
    this.timestamp = timestamp;
    this.authKey = authKey;
    this.firstName = firstName;
    this.familyName = familyName;
    this.email = email;
    this.telephone = telephone;
    this.customTextfield = customTextfield;
    this.officeId = officeId;
    this.scope = scope;
    this.subRequestCounts = subRequestCounts;
    this.serviceId = serviceId;
    this.serviceCount = serviceCount;
  }
}
