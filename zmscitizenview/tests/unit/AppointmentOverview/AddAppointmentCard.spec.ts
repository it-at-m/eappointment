import { mount } from "@vue/test-utils";
import { describe, expect, it, vi } from "vitest";

import AddAppointmentCard from "@/components/AppointmentOverview/AddAppointmentCard.vue";
import { trackCitizenEvent } from "@/utils/etracker";

vi.mock("@/utils/etracker", async () => {
  const actual = await vi.importActual<typeof import("@/utils/etracker")>(
    "@/utils/etracker"
  );
  return {
    ...actual,
    trackCitizenEvent: vi.fn(),
  };
});

describe("AddAppointmentCard", () => {
  it("tracks a new-appointment click", async () => {
    const wrapper = mount(AddAppointmentCard, {
      props: {
        title: "Neuen Termin vereinbaren",
        newAppointmentUrl: "https://www.muenchen.de/termin",
        t: (key: string) => key,
        etrackerType: "zms-appointment-slider",
      },
      global: {
        stubs: {
          "muc-button": { template: "<button><slot /></button>" },
        },
      },
    });

    await wrapper.find("a").trigger("click");

    expect(trackCitizenEvent).toHaveBeenCalledWith({
      object: "Neuer-Termin",
      action: "click",
      type: "zms-appointment-slider",
    });
  });
});
