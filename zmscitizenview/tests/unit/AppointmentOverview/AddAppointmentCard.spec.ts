import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";

import AddAppointmentCard from "@/components/AppointmentOverview/AddAppointmentCard.vue";

describe("AddAppointmentCard", () => {
  it("marks the new-appointment link for etracker click tracking", () => {
    const wrapper = mount(AddAppointmentCard, {
      props: {
        title: "Neuen Termin vereinbaren",
        newAppointmentUrl: "https://www.muenchen.de/termin",
        t: (key: string) => key,
      },
      global: {
        stubs: {
          "muc-button": { template: "<button><slot /></button>" },
        },
      },
    });

    expect(wrapper.find("a").attributes("data-etracker")).toBe("Neuer-Termin");
  });
});
