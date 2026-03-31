import { describe, it, expect } from "vitest";
// @ts-expect-error: Vue SFC import for test
import { calculateEstimatedDuration } from "@/utils/calculateEstimatedDuration";
// @ts-expect-error: Vue SFC import for test
import { ServiceImpl } from "@/types/ServiceImpl";
// @ts-expect-error: Vue SFC import for test
import { OfficeImpl } from "@/types/OfficeImpl";
// @ts-expect-error: Vue SFC import for test
import { SubService } from "@/types/SubService";

function makeProvider(id: string, slotTimeInMinutes = 10, slots = 1): OfficeImpl {
  // Minimal OfficeImpl mock
  return {
    id,
    name: `Provider ${id}`,
    address: { street: "Test", house_number: "1", postal_code: "12345", city: "Teststadt" },
    showAlternativeLocations: false,
    displayNameAlternatives: [],
    organization: "Org",
    slotTimeInMinutes,
    priority: 1,
    slots,
  } as OfficeImpl;
}

describe("calculateEstimatedDuration", () => {
  it("returns 0 if service or provider is undefined", () => {
    expect(calculateEstimatedDuration(undefined, undefined)).toBe(null);
    expect(calculateEstimatedDuration({} as ServiceImpl, undefined)).toBe(null);
    expect(calculateEstimatedDuration(undefined, makeProvider("1"))).toBe(null);
  });

  it("calculates duration for main service only", () => {
    const provider = makeProvider("1", 15, 2);
    const service = {
      id: "s1",
      name: "Main",
      maxQuantity: 5,
      providers: [provider],
      count: 2,
    } as ServiceImpl;
    expect(calculateEstimatedDuration(service, provider)).toBe(2 * 2 * 15);
  });

  it("calculates duration for main service and subservices", () => {
    const provider = makeProvider("1", 10, 1);
    const subProvider = makeProvider("1", 5, 3);
    const subService = {
      id: "sub1",
      name: "Sub",
      maxQuantity: 3,
      providers: [subProvider],
      count: 2,
    } as SubService;
    const service = {
      id: "s1",
      name: "Main",
      maxQuantity: 5,
      providers: [provider],
      count: 1,
      subServices: [subService],
    } as ServiceImpl;
    // Main: 1*1*10 = 10, Sub: 2*3*5 = 30
    expect(calculateEstimatedDuration(service, provider)).toBe(40);
  });

  it("returns null if provider is not in service.providers", () => {
    const provider = makeProvider("1");
    const otherProvider = makeProvider("2");
    const service = {
      id: "s1",
      name: "Main",
      maxQuantity: 5,
      providers: [otherProvider],
      count: 1,
    } as ServiceImpl;
    expect(calculateEstimatedDuration(service, provider)).toBe(null);
  });

  it("returns null if counts or slots are zero or missing", () => {
    const provider = makeProvider("1", 10, 0);
    const service = {
      id: "s1",
      name: "Main",
      maxQuantity: 5,
      providers: [provider],
      count: 0,
    } as ServiceImpl;
    expect(calculateEstimatedDuration(service, provider)).toBe(null);
  });

  it("sums durations for multiple subservices", () => {
    const provider = makeProvider("1", 10, 1);
    const sub1 = { id: "sub1", name: "A", maxQuantity: 2, providers: [provider], count: 1 } as SubService;
    const sub2 = { id: "sub2", name: "B", maxQuantity: 2, providers: [provider], count: 2 } as SubService;
    const service = {
      id: "s1",
      name: "Main",
      maxQuantity: 5,
      providers: [provider],
      count: 1,
      subServices: [sub1, sub2],
    } as ServiceImpl;
    // Main: 1*1*10 = 10, Sub1: 1*1*10 = 10, Sub2: 2*1*10 = 20
    expect(calculateEstimatedDuration(service, provider)).toBe(40);
  });

  it("ignores subservices with no providers", () => {
    const provider = makeProvider("1", 10, 1);
    const sub = { id: "sub1", name: "A", maxQuantity: 2, providers: [], count: 2 } as SubService;
    const service = {
      id: "s1",
      name: "Main",
      maxQuantity: 5,
      providers: [provider],
      count: 1,
      subServices: [sub],
    } as ServiceImpl;
    expect(calculateEstimatedDuration(service, provider)).toBe(null);
  });

  it("ignores subservices whose provider does not match", () => {
    const provider = makeProvider("1", 10, 1);
    const otherProvider = makeProvider("2", 5, 2);
    const sub = { id: "sub1", name: "A", maxQuantity: 2, providers: [otherProvider], count: 2 } as SubService;
    const service = {
      id: "s1",
      name: "Main",
      maxQuantity: 5,
      providers: [provider],
      count: 1,
      subServices: [sub],
    } as ServiceImpl;
    expect(calculateEstimatedDuration(service, provider)).toBe(null);
  });

  it("returns null if slotTimeInMinutes is missing", () => {
    const provider = { ...makeProvider("1"), slotTimeInMinutes: undefined } as OfficeImpl;
    const service = {
      id: "s1",
      name: "Main",
      maxQuantity: 5,
      providers: [provider],
      count: 1,
    } as ServiceImpl;
    expect(calculateEstimatedDuration(service, provider)).toBe(null);
  });

  it("returns null if slots is missing", () => {
    const provider = { ...makeProvider("1"), slots: undefined } as OfficeImpl;
    const service = {
      id: "s1",
      name: "Main",
      maxQuantity: 5,
      providers: [provider],
      count: 1,
    } as ServiceImpl;
    expect(calculateEstimatedDuration(service, provider)).toBe(null);
  });

  it("ignores subservices with count 0", () => {
    const provider = makeProvider("1", 10, 1);
    const sub = { id: "sub1", name: "A", maxQuantity: 2, providers: [provider], count: 0 } as SubService;
    const service = {
      id: "s1",
      name: "Main",
      maxQuantity: 5,
      providers: [provider],
      count: 1,
      subServices: [sub],
    } as ServiceImpl;
    expect(calculateEstimatedDuration(service, provider)).toBe(10);
  });

  it("returns null if service.providers is empty", () => {
    const provider = makeProvider("1", 10, 1);
    const service = {
      id: "s1",
      name: "Main",
      maxQuantity: 5,
      providers: [],
      count: 1,
    } as ServiceImpl;
    expect(calculateEstimatedDuration(service, provider)).toBe(null);
  });

  it("uses only matching provider if subservice has multiple providers", () => {
    const provider = makeProvider("1", 10, 1);
    const otherProvider = makeProvider("2", 5, 2);
    const sub = { id: "sub1", name: "A", maxQuantity: 2, providers: [provider, otherProvider], count: 2 } as SubService;
    const service = {
      id: "s1",
      name: "Main",
      maxQuantity: 5,
      providers: [provider],
      count: 1,
      subServices: [sub],
    } as ServiceImpl;
    // Main: 1*1*10 = 10, Sub: 2*1*10 = 20
    expect(calculateEstimatedDuration(service, provider)).toBe(30);
  });

  it("matches provider IDs as numbers and strings", () => {
    const provider = makeProvider(1 as any, 10, 1); // id as number
    const service = {
      id: "s1",
      name: "Main",
      maxQuantity: 5,
      providers: [provider],
      count: 1,
    } as ServiceImpl;
    // Pass id as string
    expect(calculateEstimatedDuration(service, { ...provider, id: "1" } as OfficeImpl)).toBe(10);
    // Pass id as number
    expect(calculateEstimatedDuration(service, { ...provider, id: 1 } as OfficeImpl)).toBe(10);
  });

  it("correctly sums durations with differing slotTimeInMinutes for service and subservices", () => {
    const provider = makeProvider("1", 10, 1); // main service: 10min per slot
    const subProvider1 = makeProvider("1", 5, 2); // subservice1: 5min per slot, 2 slots
    const subProvider2 = makeProvider("1", 20, 1); // subservice2: 20min per slot, 1 slot

    const subService1 = {
      id: "sub1",
      name: "Sub1",
      maxQuantity: 2,
      providers: [subProvider1],
      count: 2,
    } as SubService;

    const subService2 = {
      id: "sub2",
      name: "Sub2",
      maxQuantity: 2,
      providers: [subProvider2],
      count: 1,
    } as SubService;

    const service = {
      id: "s1",
      name: "Main",
      maxQuantity: 5,
      providers: [provider],
      count: 3,
      subServices: [subService1, subService2],
    } as ServiceImpl;

    // Main: 3*1*10 = 30
    // Sub1: 2*2*5 = 20
    // Sub2: 1*1*20 = 20
    // Total: 30 + 20 + 20 = 70
    expect(calculateEstimatedDuration(service, provider)).toBe(70);
  });
});
