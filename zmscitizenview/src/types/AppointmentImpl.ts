import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { Scope } from "@/api/models/Scope";
import { SubRequestCount } from "@/api/models/SubRequestCount";

export class AppointmentImpl implements AppointmentDTO {
  processId: string;
  displayNumber?: string | null;

  timestamp: number;

  authKey: string;

  firstName?: string;

  familyName: string;

  email: string;

  telephone?: string;

  customTextfield?: string;

  customTextfield2?: string;

  officeId: string;

  scope: Scope;

  subRequestCounts: SubRequestCount[];

  serviceId: string;

  serviceName: string;

  serviceCount: number;

  icsContent?: string;

  constructor(
    processId: string,
    displayNumber: string | null | undefined,
    timestamp: number,
    authKey: string,
    firstName: string | undefined,
    familyName: string,
    email: string,
    telephone: string | undefined,
    customTextfield: string | undefined,
    customTextfield2: string | undefined,
    officeId: string,
    scope: Scope,
    subRequestCounts: any[],
    serviceId: string,
    serviceName: string,
    serviceCount: number,
    icsContent?: string
  ) {
    this.processId = processId;
    this.displayNumber = displayNumber ?? undefined;
    this.timestamp = timestamp;
    this.authKey = authKey;
    this.firstName = firstName;
    this.familyName = familyName;
    this.email = email;
    this.telephone = telephone;
    this.customTextfield = customTextfield;
    this.customTextfield2 = customTextfield2;
    this.officeId = officeId;
    this.scope = scope;
    this.subRequestCounts = subRequestCounts;
    this.serviceId = serviceId;
    this.serviceName = serviceName;
    this.serviceCount = serviceCount;
    this.icsContent = icsContent;
  }
}
