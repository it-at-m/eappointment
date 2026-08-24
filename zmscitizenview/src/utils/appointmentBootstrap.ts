import type { AppointmentDTO } from "@/api/models/AppointmentDTO";
import type { Office } from "@/api/models/Office";
import type { Relation } from "@/api/models/Relation";
import type { Service } from "@/api/models/Service";
import type { AppointmentHash } from "@/types/AppointmentHashTypes";
import type { CustomerData } from "@/types/CustomerData";
import type { GlobalState } from "@/types/GlobalState";
import type { LocalStorageUiData } from "@/types/LocalStorageAppointmentData";
import type { OfficeImpl } from "@/types/OfficeImpl";
import type { ServiceImpl } from "@/types/ServiceImpl";
import type { Ref } from "vue";

import {
  cancelAppointment,
  confirmAppointment,
  fetchAppointment,
} from "@/api/ZMSAppointmentAPI";
import { SubService } from "@/types/SubService";
import { loadOfficesAndServicesCatalog } from "@/utils/appointmentCatalog";
import {
  clearAppointmentAuthHashSession,
  clearAppointmentLocalStorage,
  getFreshLocalStorageUiData,
  parseAppointmentHash,
  resolveAppointmentAuthHash,
} from "@/utils/appointmentLoginStorage";
import { APPOINTMENT_ACTION_TYPE } from "@/utils/Constants";
import {
  clearContextErrors,
  createErrorStates,
  handleApiError,
  handleApiResponse,
} from "@/utils/errorHandler";
import {
  officeFromCatalog,
  getProviders as providersForService,
} from "@/utils/getProviders";
import { applyAppointmentContactToCustomerData } from "@/utils/rebookingContact";

export type AppointmentBootstrapProps = {
  globalState: GlobalState;
  serviceId?: string;
  locationId?: string;
  appointmentHash?: string;
  confirmAppointmentHash?: string;
};

export type AppointmentBootstrapContext = {
  props: AppointmentBootstrapProps;
  services: Ref<Service[]>;
  relations: Ref<Relation[]>;
  offices: Ref<Office[]>;
  selectedService: Ref<ServiceImpl | undefined>;
  selectedServiceMap: Ref<Map<string, number>>;
  selectedProvider: Ref<OfficeImpl | undefined>;
  selectedTimeslot: Ref<number>;
  currentView: Ref<number>;
  appointment: Ref<AppointmentDTO | undefined>;
  rebookedAppointment: Ref<AppointmentDTO | undefined>;
  customerData: Ref<CustomerData>;
  captchaToken: Ref<string | undefined>;
  reservationStartMs: Ref<number | null>;
  preselectedLocationId: Ref<string | undefined>;
  loadedAppointmentHash: Ref<string | null>;
  isLoadingAppointmentFromHash: Ref<boolean>;
  rebookOrCancelDialog: Ref<boolean>;
  confirmAppointmentSuccess: Ref<boolean>;
  appointmentAlreadyActivated: Ref<boolean>;
  confirmedAppointmentHash: Ref<string | null>;
  isBookingAppointment: Ref<boolean>;
  isRebooking: Ref<boolean>;
  currentContext: Ref<string>;
  isAppointmentInPast: Ref<boolean> | { value: boolean };
  errorStates: ReturnType<typeof createErrorStates>;
  updateServiceLinkId: (id: string | null) => void;
  nextRescheduleAppointment: () => void;
  nextCancelAppointment: () => void;
  clearAllErrors: () => void;
  focusActiveStepperItem: () => void;
};

export function createAppointmentBootstrap(ctx: AppointmentBootstrapContext) {
  const errorStateMap = () => ctx.errorStates.errorStateMap;
  const currentErrorData = () => ctx.errorStates.currentErrorData;

  const getProviders = (serviceId: string, providers: string[] | null) =>
    providersForService(
      serviceId,
      providers,
      ctx.relations.value,
      ctx.offices.value
    );

  const applyCatalog = (catalog: {
    services: Service[];
    relations: Relation[];
    offices: Office[];
  }) => {
    ctx.services.value = catalog.services;
    ctx.relations.value = catalog.relations;
    ctx.offices.value = catalog.offices;
  };

  const loadCatalog = () =>
    loadOfficesAndServicesCatalog(
      ctx.props.serviceId ?? undefined,
      ctx.props.locationId ?? undefined,
      ctx.props.globalState?.baseUrl ?? undefined,
      errorStateMap(),
      currentErrorData()
    );

  const applyLocalStorageUiData = (uiData: LocalStorageUiData) => {
    ctx.selectedServiceMap.value = new Map(
      Object.entries(uiData.selectedServiceMap ?? {})
    );

    const foundService = ctx.services.value.find(
      (service) => String(service.id) === String(uiData.selectedServiceId)
    );
    if (foundService) {
      ctx.selectedService.value = foundService as ServiceImpl;
      const count = ctx.selectedServiceMap.value.get(String(foundService.id));
      if (count != undefined) {
        ctx.selectedService.value.count = count;
      }
      ctx.selectedService.value.providers = getProviders(
        ctx.selectedService.value.id,
        null
      );
    }

    const foundOffice = ctx.offices.value.find(
      (office) => String(office.id) === String(uiData.selectedProviderId)
    );
    if (foundOffice) {
      ctx.selectedProvider.value = officeFromCatalog(foundOffice);
    }

    ctx.selectedTimeslot.value = uiData.selectedTimeslot;
    ctx.currentView.value = ctx.isAppointmentInPast.value
      ? 3
      : uiData.currentView;
    if (typeof uiData.reservationStartMs === "number") {
      ctx.reservationStartMs.value = uiData.reservationStartMs;
    }
  };

  const hydrateWizardFromAppointment = (booked: AppointmentDTO) => {
    ctx.selectedService.value = ctx.services.value.find(
      (service) => service.id == booked.serviceId
    ) as ServiceImpl | undefined;
    if (!ctx.selectedService.value) {
      return false;
    }

    ctx.selectedService.value.count = booked.serviceCount;
    ctx.selectedService.value.providers = getProviders(
      ctx.selectedService.value.id,
      null
    );
    ctx.updateServiceLinkId(
      String(
        ctx.selectedService.value.rootParentId ??
          ctx.selectedService.value.id ??
          ""
      )
    );
    ctx.preselectedLocationId.value = booked.officeId;
    const foundOffice = ctx.offices.value.find(
      (office) => office.id == booked.officeId
    );
    if (foundOffice) {
      ctx.selectedProvider.value = officeFromCatalog(foundOffice, {
        disabledByServices: undefined,
        slots: undefined,
      });
    }

    if (booked.subRequestCounts.length > 0) {
      booked.subRequestCounts.forEach((subRequestCount) => {
        const subRequest = ctx.services.value.find(
          (service) => service.id == subRequestCount.id
        ) as Service;
        const subService = new SubService(
          subRequest.id,
          subRequest.name,
          subRequest.maxQuantity,
          getProviders(subRequest.id, null),
          subRequestCount.count
        );
        if (
          ctx.selectedService.value &&
          !ctx.selectedService.value.subServices
        ) {
          ctx.selectedService.value.subServices = [];
        }
        ctx.selectedService.value?.subServices?.push(subService);
      });
    }
    return true;
  };

  const resetConfirmRouteState = (): void => {
    ctx.confirmAppointmentSuccess.value = false;
    ctx.appointmentAlreadyActivated.value = false;
    ctx.confirmedAppointmentHash.value = null;
    ctx.isBookingAppointment.value = false;
  };

  const runLoginResumeFromHashAndLocalStorage = (
    hash: string,
    uiData: LocalStorageUiData
  ): void => {
    const appointmentData = parseAppointmentHash(hash);
    if (!appointmentData) {
      handleApiError(
        "appointmentNotFound",
        errorStateMap(),
        currentErrorData()
      );
      clearAppointmentLocalStorage();
      clearAppointmentAuthHashSession();
      return;
    }

    ctx.loadedAppointmentHash.value = hash;
    clearContextErrors(errorStateMap());

    loadCatalog()
      .then((catalog) => {
        if (!catalog) {
          return;
        }
        applyCatalog(catalog);
        applyLocalStorageUiData(uiData);

        return fetchAppointment(ctx.props.globalState, appointmentData).then(
          (response) => {
            if ((response as AppointmentDTO).processId != undefined) {
              ctx.appointment.value = response as AppointmentDTO;
              applyAppointmentContactToCustomerData(
                ctx.customerData.value,
                ctx.appointment.value
              );
              if (ctx.reservationStartMs.value == null) {
                ctx.reservationStartMs.value = Date.now();
              }
              if (
                "captchaToken" in response &&
                (response as AppointmentDTO & { captchaToken?: string })
                  .captchaToken
              ) {
                ctx.captchaToken.value =
                  ctx.captchaToken.value ||
                  (response as AppointmentDTO & { captchaToken?: string })
                    .captchaToken;
              }
              ctx.currentView.value = ctx.isAppointmentInPast.value
                ? 3
                : uiData.currentView;
              clearAppointmentLocalStorage();
              clearAppointmentAuthHashSession();
            } else {
              handleApiResponse(response, errorStateMap(), currentErrorData());
            }
          }
        );
      })
      .catch(() => {
        handleApiError(
          "appointmentNotFound",
          errorStateMap(),
          currentErrorData()
        );
      });
  };

  const runAppointmentFromHash = (hash: string | undefined): void => {
    if (
      !hash ||
      hash === ctx.loadedAppointmentHash.value ||
      ctx.isLoadingAppointmentFromHash.value
    ) {
      return;
    }

    const appointmentData = parseAppointmentHash(hash);
    if (!appointmentData) {
      handleApiError(
        "appointmentNotFound",
        errorStateMap(),
        currentErrorData()
      );
      return;
    }

    resetConfirmRouteState();
    ctx.loadedAppointmentHash.value = hash;
    ctx.isLoadingAppointmentFromHash.value = true;
    clearContextErrors(errorStateMap());
    ctx.rebookOrCancelDialog.value = true;

    loadCatalog()
      .then((catalog) => {
        if (!catalog) {
          return;
        }
        applyCatalog(catalog);

        return fetchAppointment(ctx.props.globalState, appointmentData).then(
          (data) => {
            if ((data as AppointmentDTO).processId != undefined) {
              if ("captchaToken" in data && data.captchaToken) {
                ctx.captchaToken.value = data.captchaToken as string;
              }
              ctx.appointment.value = data as AppointmentDTO;
              if (!hydrateWizardFromAppointment(ctx.appointment.value)) {
                return;
              }
              if (!appointmentData.action || ctx.isAppointmentInPast.value) {
                ctx.currentView.value = 3;
              } else if (
                appointmentData.action === APPOINTMENT_ACTION_TYPE.RESCHEDULE
              ) {
                ctx.nextRescheduleAppointment();
              } else {
                ctx.nextCancelAppointment();
              }
            } else {
              handleApiError(
                "appointmentNotFound",
                errorStateMap(),
                currentErrorData()
              );
            }
          }
        );
      })
      .finally(() => {
        ctx.isLoadingAppointmentFromHash.value = false;
      });
  };

  const showAlreadyActivatedAppointment = (hash: string): void => {
    clearContextErrors(errorStateMap());
    runAppointmentFromHash(hash);
    ctx.appointmentAlreadyActivated.value = true;
    ctx.confirmedAppointmentHash.value = hash;
  };

  const nextConfirmAppointment = (
    appointmentData: AppointmentHash,
    hash?: string
  ) => {
    confirmAppointment(ctx.props.globalState, appointmentData)
      .then((data) => {
        ctx.currentView.value = 5;

        if ((data as AppointmentDTO).processId != undefined) {
          ctx.confirmAppointmentSuccess.value = true;
          ctx.appointment.value = data as AppointmentDTO;
          clearContextErrors(errorStateMap());
          if (ctx.isRebooking.value && ctx.rebookedAppointment.value) {
            ctx.currentContext.value = "cancel";
            cancelAppointment(
              ctx.props.globalState,
              ctx.rebookedAppointment.value
            );
          }
        } else {
          const firstErrorCode =
            (data as { errors?: { errorCode?: string }[] }).errors?.[0]
              ?.errorCode ?? "";

          if (firstErrorCode === "processNotPreconfirmedAnymore") {
            Promise.resolve(
              fetchAppointment(ctx.props.globalState, appointmentData)
            ).then((fetched) => {
              if ((fetched as AppointmentDTO)?.processId != undefined && hash) {
                showAlreadyActivatedAppointment(hash);
                return;
              }
              handleApiError(
                "preconfirmationExpired",
                errorStateMap(),
                currentErrorData()
              );
            });
          } else if (firstErrorCode === "appointmentNotFound") {
            handleApiError(
              "preconfirmationExpired",
              errorStateMap(),
              currentErrorData()
            );
          } else {
            handleApiResponse(data, errorStateMap(), currentErrorData());
          }
        }
      })
      .finally(() => {
        ctx.isBookingAppointment.value = false;
      });
  };

  const runConfirmFromHash = (hash: string | undefined): void => {
    if (!hash || ctx.isBookingAppointment.value) {
      return;
    }

    if (
      ctx.appointmentAlreadyActivated.value &&
      hash === ctx.confirmedAppointmentHash.value
    ) {
      return;
    }

    if (
      ctx.confirmAppointmentSuccess.value &&
      hash === ctx.confirmedAppointmentHash.value
    ) {
      showAlreadyActivatedAppointment(hash);
      return;
    }

    if (hash === ctx.confirmedAppointmentHash.value) {
      return;
    }

    const appointmentData = parseAppointmentHash(hash);
    if (!appointmentData) {
      handleApiError(
        "appointmentNotFound",
        errorStateMap(),
        currentErrorData()
      );
      return;
    }

    ctx.confirmedAppointmentHash.value = hash;
    ctx.loadedAppointmentHash.value = null;
    ctx.clearAllErrors();
    ctx.currentView.value = 5;
    ctx.isBookingAppointment.value = true;
    nextConfirmAppointment(appointmentData, hash);
  };

  const onAppointmentHashChange = (hash: string | undefined): void => {
    if (!hash) {
      ctx.loadedAppointmentHash.value = null;
      return;
    }
    const uiData = getFreshLocalStorageUiData();
    if (uiData) {
      runLoginResumeFromHashAndLocalStorage(hash, uiData);
      return;
    }
    runAppointmentFromHash(hash);
  };

  const bootstrapOnMounted = (): void => {
    runConfirmFromHash(ctx.props.confirmAppointmentHash);

    if (ctx.props.confirmAppointmentHash) {
      clearAppointmentLocalStorage();
      clearAppointmentAuthHashSession();
      ctx.focusActiveStepperItem();
      return;
    }

    const authHash = resolveAppointmentAuthHash(ctx.props.appointmentHash);
    const uiData = getFreshLocalStorageUiData();

    if (authHash && uiData) {
      runLoginResumeFromHashAndLocalStorage(authHash, uiData);
      ctx.focusActiveStepperItem();
      return;
    }

    if (authHash) {
      runAppointmentFromHash(authHash);
      clearAppointmentLocalStorage();
      clearAppointmentAuthHashSession();
      ctx.focusActiveStepperItem();
      return;
    }

    if (uiData) {
      clearContextErrors(errorStateMap());

      loadCatalog()
        .then((catalog) => {
          if (!catalog) {
            return;
          }
          applyCatalog(catalog);
          applyLocalStorageUiData(uiData);
          clearAppointmentLocalStorage();
          clearAppointmentAuthHashSession();
        })
        .catch(() => {
          handleApiError(
            "appointmentNotFound",
            errorStateMap(),
            currentErrorData()
          );
        });

      ctx.focusActiveStepperItem();
      return;
    }

    clearAppointmentLocalStorage();
    clearAppointmentAuthHashSession();
    ctx.focusActiveStepperItem();
  };

  return {
    applyLocalStorageUiData,
    nextConfirmAppointment,
    runConfirmFromHash,
    runAppointmentFromHash,
    runLoginResumeFromHashAndLocalStorage,
    onAppointmentHashChange,
    bootstrapOnMounted,
  };
}
