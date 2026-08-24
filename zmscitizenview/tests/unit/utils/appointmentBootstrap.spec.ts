import { beforeEach, describe, expect, it, vi } from "vitest";
import { ref } from "vue";

import { confirmAppointment } from "@/api/ZMSAppointmentAPI";
import { CustomerData } from "@/types/CustomerData";
import { createAppointmentBootstrap } from "@/utils/appointmentBootstrap";
import { createErrorStates } from "@/utils/errorHandler";

vi.mock("@/api/ZMSAppointmentAPI", () => ({
  cancelAppointment: vi.fn(),
  confirmAppointment: vi.fn(),
  fetchAppointment: vi.fn(),
}));

const globalState = {
  baseUrl: "https://www.muenchen.de",
  accessToken: null,
  isLoggedIn: false,
  isLoadingAuthentication: false,
};

function createCtx() {
  return {
    props: {
      globalState,
      confirmAppointmentHash: undefined as string | undefined,
      appointmentHash: undefined as string | undefined,
    },
    services: ref([]),
    relations: ref([]),
    offices: ref([]),
    selectedService: ref(undefined),
    selectedServiceMap: ref(new Map<string, number>()),
    selectedProvider: ref(undefined),
    selectedTimeslot: ref(0),
    currentView: ref(0),
    appointment: ref(undefined),
    rebookedAppointment: ref(undefined),
    customerData: ref(new CustomerData("", "", "", "", "", "")),
    captchaToken: ref<string | undefined>(undefined),
    reservationStartMs: ref<number | null>(null),
    preselectedLocationId: ref<string | undefined>(undefined),
    loadedAppointmentHash: ref<string | null>(null),
    isLoadingAppointmentFromHash: ref(false),
    rebookOrCancelDialog: ref(false),
    confirmAppointmentSuccess: ref(false),
    appointmentAlreadyActivated: ref(false),
    confirmedAppointmentHash: ref<string | null>(null),
    isBookingAppointment: ref(false),
    isRebooking: ref(false),
    currentContext: ref("update"),
    isAppointmentInPast: ref(false),
    errorStates: createErrorStates(),
    updateServiceLinkId: vi.fn(),
    nextRescheduleAppointment: vi.fn(),
    nextCancelAppointment: vi.fn(),
    clearAllErrors: vi.fn(),
    focusActiveStepperItem: vi.fn(),
  };
}

describe("appointmentBootstrap", () => {
  beforeEach(() => {
    vi.mocked(confirmAppointment).mockReset();
    vi.mocked(confirmAppointment).mockResolvedValue({
      processId: "1",
    } as any);
  });

  it("applyLocalStorageUiData restores stepper IDs and the current view", () => {
    const ctx = createCtx();
    ctx.services.value = [
      { id: "123", name: "Service", maxQuantity: 1 },
    ] as any;
    ctx.offices.value = [
      {
        id: "789",
        name: "Office",
        address: {
          street: "S",
          house_number: "1",
          postal_code: "1",
          city: "M",
        },
        showAlternativeLocations: false,
        displayNameAlternatives: [],
        organization: "o",
        slotTimeInMinutes: 15,
      },
    ] as any;
    const bootstrap = createAppointmentBootstrap(ctx);
    bootstrap.applyLocalStorageUiData({
      timestamp: Date.now(),
      currentView: 2,
      selectedServiceId: "123",
      selectedProviderId: "789",
      selectedServiceMap: { "123": 1 },
      selectedTimeslot: 42,
    });
    expect(ctx.selectedService.value?.id).toBe("123");
    expect(ctx.selectedProvider.value?.id).toBe("789");
    expect(ctx.selectedTimeslot.value).toBe(42);
    expect(ctx.currentView.value).toBe(2);
  });

  it("runConfirmFromHash does not confirm the same hash twice", () => {
    const ctx = createCtx();
    const bootstrap = createAppointmentBootstrap(ctx);
    const hash = btoa(JSON.stringify({ id: "1", authKey: "k" }));
    bootstrap.runConfirmFromHash(hash);
    bootstrap.runConfirmFromHash(hash);
    expect(confirmAppointment).toHaveBeenCalledTimes(1);
  });
});
