import type { OfficesAndServicesDTO } from "@/api/models/OfficesAndServicesDTO";
import type { ApiErrorData, ErrorStateMap } from "@/utils/errorHandler";
import type { Ref } from "vue";

import { fetchServicesAndProviders } from "@/api/ZMSAppointmentAPI";
import { handleApiResponseForDownTime } from "@/utils/apiStatusService";
import { handleApiResponse } from "@/utils/errorHandler";

function hasResponseErrors(data: unknown): boolean {
  const errors = (data as { errors?: unknown[] } | null)?.errors;
  return Array.isArray(errors) && errors.length > 0;
}

export function catalogFromResponse(
  data: unknown,
  errorStateMap: ErrorStateMap,
  currentErrorData: Ref<ApiErrorData | null>,
  baseUrl?: string
): OfficesAndServicesDTO | null {
  handleApiResponse(data, errorStateMap, currentErrorData);
  if (handleApiResponseForDownTime(data, baseUrl)) {
    return null;
  }
  if (hasResponseErrors(data)) {
    return null;
  }
  const catalog = data as OfficesAndServicesDTO;
  if (!catalog?.services || !catalog?.relations || !catalog?.offices) {
    return null;
  }
  return catalog;
}

export function loadOfficesAndServicesCatalog(
  serviceId: string | undefined,
  locationId: string | undefined,
  baseUrl: string | undefined,
  errorStateMap: ErrorStateMap,
  currentErrorData: Ref<ApiErrorData | null>
): Promise<OfficesAndServicesDTO | null> {
  return fetchServicesAndProviders(serviceId, locationId, baseUrl).then(
    (data) =>
      catalogFromResponse(data, errorStateMap, currentErrorData, baseUrl)
  );
}
