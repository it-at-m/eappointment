import { Scope } from "@/api/models/Scope";
import { APPOINTMENT_ACTION_TYPE } from "@/utils/Constants";

export interface AppointmentHash {
  id: string;
  authKey: string;
  scope?: Scope;
  action?: APPOINTMENT_ACTION_TYPE;
}
