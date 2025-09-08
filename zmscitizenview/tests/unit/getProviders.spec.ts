import { describe, it, expect } from "vitest";
// @ts-expect-error: Vue SFC import for test
import { getProviders } from "@/utils/getProviders";

const relations = [
  {
    officeId: "1",
    serviceId: "1",
    slots: 1
  },
  {
    officeId: "2",
    serviceId: "1",
    slots: 1
  },
  {
    officeId: "3",
    serviceId: "1",
    slots: 2
  },
  {
    officeId: "1",
    serviceId: "2",
    slots: 2
  },
  {
    officeId: "2",
    serviceId: "3",
    slots: 1
  },
]

const offices = [
  {
    id: "1",
    name: "Office 1",
    address: {
      house_number: "1",
      city: "Munich",
      postal_code: "11111",
      street: "Street 1",
      hint: false
    },
    showAlternativeLocations: false,
    displayNameAlternatives: [],
    organization: "organization 1",
    slotTimeInMinutes: 4
  },
  {
    id: "2",
    name: "Office 2",
    address: {
      house_number: "2",
      city: "Munich",
      postal_code: "22222",
      street: "Street 2",
      hint: false
    },
    showAlternativeLocations: false,
    displayNameAlternatives: [],
    organization: "organization 2",
    slotTimeInMinutes: 3
  },
  {
    id: "3",
    name: "Office 3",
    address: {
      house_number: "3",
      city: "Munich",
      postal_code: "33333",
      street: "Street 3",
      hint: false
    },
    showAlternativeLocations: false,
    displayNameAlternatives: [],
    organization: "organization 3",
    slotTimeInMinutes: 2
  }
]

describe("calculateEstimatedDuration", () => {
  it("returns list of providers with no given providers", () => {
    const providers = getProviders("1",null, relations, offices);
    expect(providers.length).toBe(offices.length);
    expect(providers[0].id).toBe("1");
    expect(providers[0].slots).toBe(1);
    expect(providers[1].id).toBe("2");
    expect(providers[1].slots).toBe(1);
    expect(providers[2].id).toBe("3");
    expect(providers[2].slots).toBe(2);
  });
  it("returns list of providers with given providers", () => {
    const providers = getProviders("1",["1","3"], relations, offices);
    expect(providers.length).toBe(2);
    expect(providers[0].id).toBe("1");
    expect(providers[0].slots).toBe(1);
    expect(providers[1].id).toBe("3");
    expect(providers[1].slots).toBe(2);
  });

  it("returns empty list with given providers", () => {
    const providers = getProviders("3",["1","3"], relations, offices);
    expect(providers.length).toBe(0);
  });
});
