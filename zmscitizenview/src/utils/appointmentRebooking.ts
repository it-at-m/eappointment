import type { AppointmentDTO } from "@/api/models/AppointmentDTO";
import type { Scope } from "@/api/models/Scope";
import type { CustomerData } from "@/types/CustomerData";
import type { GlobalState } from "@/types/GlobalState";

import { updateAppointment } from "@/api/ZMSAppointmentAPI";
import {
  applyAppointmentContactToCustomerData,
  copyAppointmentContact,
  hasMissingRequiredContact,
} from "@/utils/rebookingContact";

export function copyRebookedContactOntoAppointment(
  appointment: AppointmentDTO | undefined,
  rebookedAppointment: AppointmentDTO | undefined
): void {
  if (!appointment || !rebookedAppointment) {
    return;
  }
  copyAppointmentContact(appointment, rebookedAppointment);
}

export function fillCustomerDataFromRebookedAppointment(
  customerData: CustomerData,
  rebookedAppointment: AppointmentDTO | undefined
): void {
  if (!rebookedAppointment) {
    return;
  }
  applyAppointmentContactToCustomerData(customerData, rebookedAppointment);
}

export function targetScopeForRebooking(
  selectedProvider?: { scope?: Scope | null } | null,
  appointment?: { scope?: Scope | null } | null
): Scope | null | undefined {
  return selectedProvider?.scope ?? appointment?.scope;
}

export type RebookingAfterReserveHandlers = {
  prepareUpdate: () => void;
  goToContact: () => void;
  goToOverview: (appointment: AppointmentDTO) => void;
  handleUpdateError: (data: unknown) => void;
};

export type RebookingAfterReserveContext = RebookingAfterReserveHandlers & {
  appointment: AppointmentDTO | undefined;
  rebookedAppointment: AppointmentDTO | undefined;
  customerData: CustomerData;
  targetScope?: Scope | null;
  globalState: GlobalState;
};

export function setRebookData(
  ctx: RebookingAfterReserveContext
): Promise<void> | void {
  if (!ctx.appointment || !ctx.rebookedAppointment) {
    return;
  }
  copyRebookedContactOntoAppointment(ctx.appointment, ctx.rebookedAppointment);
  ctx.prepareUpdate();
  return updateAppointment(ctx.globalState, ctx.appointment).then((data) => {
    if ((data as AppointmentDTO).processId != undefined) {
      ctx.goToOverview(data as AppointmentDTO);
    } else {
      ctx.handleUpdateError(data);
    }
  });
}

export function continueRebookingAfterReserve(
  ctx: RebookingAfterReserveContext
): Promise<void> | void {
  fillCustomerDataFromRebookedAppointment(
    ctx.customerData,
    ctx.rebookedAppointment
  );
  copyRebookedContactOntoAppointment(ctx.appointment, ctx.rebookedAppointment);
  if (
    ctx.rebookedAppointment &&
    hasMissingRequiredContact(ctx.rebookedAppointment, ctx.targetScope)
  ) {
    ctx.goToContact();
    return;
  }
  return setRebookData(ctx);
}
