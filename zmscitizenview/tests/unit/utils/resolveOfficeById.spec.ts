import { describe, expect, it } from "vitest";

import { resolveOfficeById } from "@/utils/resolveOfficeById";

const haupt = {
  id: 10489,
  name: "Bürgerbüro Ruppertstraße",
  address: {
    street: "Ruppertstraße",
    house_number: "19",
    postal_code: "80337",
    city: "München",
    hint: false,
  },
  showAlternativeLocations: true,
  displayNameAlternatives: [],
  organization: "KVR",
  slotTimeInMinutes: 15,
  sharedBookingOfficeIds: [10489, 10503],
};

const ausbildung = {
  ...haupt,
  id: 10503,
  name: "Bürgerbüro Ruppertstraße (Ausbildung)",
};

describe("resolveOfficeById", () => {
  it("returns the catalog office when the real id is present", () => {
    const found = resolveOfficeById(10503, { offices: [haupt, ausbildung] });
    expect(found?.id).toBe("10503");
    expect(found?.name).toBe("Bürgerbüro Ruppertstraße (Ausbildung)");
  });

  it("clones the shared-booking display peer and keeps the appointment officeId", () => {
    const found = resolveOfficeById("10503", {
      offices: [haupt],
      appointment: {
        scope: {
          hint: "Eingang B - EG - Wartebereich 04",
          provider: {
            id: 10503,
            displayName: "Bürgerbüro Ruppertstraße (Ausbildung)",
          },
        },
      } as any,
    });
    expect(found?.id).toBe("10503");
    expect(found?.name).toBe("Bürgerbüro Ruppertstraße (Ausbildung)");
    expect(found?.scope?.hint).toBe("Eingang B - EG - Wartebereich 04");
  });

  it("prefers the service providers list over the collapsed Ort catalog", () => {
    const found = resolveOfficeById(10503, {
      offices: [haupt],
      providers: [ausbildung as any],
    });
    expect(found?.id).toBe("10503");
    expect(found?.name).toBe("Bürgerbüro Ruppertstraße (Ausbildung)");
  });

  it("builds an office from appointment.scope.provider when the catalog has no peer", () => {
    const found = resolveOfficeById(10503, {
      offices: [],
      appointment: {
        scope: {
          hint: "Wartebereich 04",
          provider: {
            id: 10503,
            displayName: "Bürgerbüro Ruppertstraße (Ausbildung)",
            contact: {
              street: "Ruppertstraße",
              streetNumber: "19",
              postalCode: "80337",
              city: "München",
            },
          },
        },
      } as any,
    });
    expect(found?.id).toBe("10503");
    expect(found?.address.street).toBe("Ruppertstraße");
    expect(found?.address.house_number).toBe("19");
  });

  it("returns undefined when nothing matches", () => {
    const unrelated = {
      ...haupt,
      id: 10470,
      sharedBookingOfficeIds: undefined,
    };
    expect(resolveOfficeById(10503, { offices: [unrelated] })).toBeUndefined();
  });
});
