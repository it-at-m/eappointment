import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { GlobalState } from "@/types/GlobalState";
import { VUE_APP_ZMS_API_MYAPPOINTMENTS_ENDPOINT } from "@/utils/Constants";
import { request } from "./ZMSAppointmentAPI";

export function getMyAppointments(
  globalState: GlobalState
): Promise<AppointmentDTO[]> {
  return request({
    globalState,
    method: "GET",
    path: VUE_APP_ZMS_API_MYAPPOINTMENTS_ENDPOINT,
    forceAuth: true,
  });
}

export async function getAppointmentDetails(
  globalState: GlobalState,
  processId: string
): Promise<AppointmentDTO> {
  const responseData: AppointmentDTO[] = await request({
    globalState,
    method: "GET",
    path: VUE_APP_ZMS_API_MYAPPOINTMENTS_ENDPOINT,
    params: {
      filterId: processId,
    },
    forceAuth: true,
  });
  return responseData[0];
}
