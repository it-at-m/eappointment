import { describe, expect, it } from "vitest";

import { ServiceImpl } from "@/types/ServiceImpl";
import { SubService } from "@/types/SubService";
import { expandSubServicesFromCombinable } from "@/utils/expandSubServicesFromCombinable";

describe("expandSubServicesFromCombinable", () => {
  it("rebuilds every combinable peer and keeps selected counts", () => {
    const service = new ServiceImpl(
      "123",
      "Main",
      2,
      {
        a: { "123": [1] },
        b: { "456": [1] },
        c: { "789": [1] },
      },
      [],
      [new SubService("456", "Selected", 2, [], 1)],
      1,
      null,
      null,
      true
    );

    expandSubServicesFromCombinable(
      service,
      new Map([
        ["123", 1],
        ["456", 1],
      ]),
      [
        { id: "123", name: "Main", maxQuantity: 2 },
        { id: "456", name: "Selected", maxQuantity: 2 },
        { id: "789", name: "Other", maxQuantity: 2 },
      ] as any,
      () => []
    );

    expect(service.subServices?.map((sub) => sub.id)).toEqual(["456", "789"]);
    expect(service.subServices?.find((sub) => sub.id === "456")?.count).toBe(1);
    expect(service.subServices?.find((sub) => sub.id === "789")?.count).toBe(0);
    // Self-entry removed on the service copy; catalog object is not passed in.
    expect(Object.values(service.combinable ?? {})).toEqual([
      { "456": [1] },
      { "789": [1] },
    ]);
  });
});
