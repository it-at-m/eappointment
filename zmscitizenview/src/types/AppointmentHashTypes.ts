import { Scope } from "@/api/models/Scope";

export interface AppointmentHash {
  id: string;
  authKey: string;
  scope?: Scope;
}
