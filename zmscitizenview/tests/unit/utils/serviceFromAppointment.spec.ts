import { describe, expect, it } from "vitest";

import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { Service } from "@/api/models/Service";
import { OfficeImpl } from "@/types/OfficeImpl";
import { serviceFromAppointment } from "@/utils/serviceFromAppointment";

const emptyAddress = {
  street: "",
  house_number: "",
  postal_code: "",
  city: "",
  hint: false as const,
};

describe("serviceFromAppointment", () => {
  const fallbackProvider = new OfficeImpl(
    "10446",
    "SZE",
    emptyAddress,
    false,
    [],
    "",
    undefined,
    15
  );

  it("falls back to appointment.serviceName when the service is not in the catalog", () => {
    const appointment = {
      serviceId: 1080784,
      serviceName: "Aufenthaltserlaubnis – Ausbildung oder Weiterbildung",
      serviceCount: 1,
      subRequestCounts: [],
      officeId: 10446,
    } as AppointmentDTO;

    const selected = serviceFromAppointment(
      appointment,
      [],
      () => [],
      fallbackProvider
    );

    expect(selected).toBeDefined();
    expect(selected?.id).toBe("1080784");
    expect(selected?.name).toBe(
      "Aufenthaltserlaubnis – Ausbildung oder Weiterbildung"
    );
    expect(selected?.count).toBe(1);
    expect(selected?.providers?.[0]?.id).toBe("10446");
  });

  it("prefers appointment.serviceName when the service is in the catalog", () => {
    const catalog: Service[] = [
      {
        id: "1063453",
        name: "Catalog Reisepass",
        maxQuantity: 5,
        parentId: null,
        variantId: null,
        rootParentId: 1063453,
        showOnStartPage: true,
      },
    ];
    const appointment = {
      serviceId: 1063453,
      serviceName: "Booked Reisepass name",
      serviceCount: 2,
      subRequestCounts: [],
      officeId: 10502,
    } as AppointmentDTO;

    const selected = serviceFromAppointment(appointment, catalog, () => []);

    expect(selected?.name).toBe("Booked Reisepass name");
    expect(selected?.count).toBe(2);
    expect(selected?.rootParentId).toBe(1063453);
  });
});
