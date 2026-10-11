import { describe, expect, it } from "vitest";
import { Combinable } from "@/api/models/Combinable";
import { Service } from "@/api/models/Service";
import { OfficeImpl } from "@/types/OfficeImpl";
import { ServiceImpl } from "@/types/ServiceImpl";
import { hydrateServiceFromSelectedMap } from "@/utils/hydrateServiceFromSelectedMap";

const office = (id: string): OfficeImpl =>
  ({
    id,
    name: `Office ${id}`,
    providers: [],
  }) as unknown as OfficeImpl;

describe("hydrateServiceFromSelectedMap", () => {
  it("restores main and combinable subservice counts with providers", () => {
    const combinable = {
      a: { "10": [1, 2] },
      b: { "20": [2] },
    } as Combinable;

    const main = new ServiceImpl(
      "10",
      "Main",
      5,
      combinable,
      [],
      undefined,
      1,
      null,
      null,
      true
    );

    const catalog: Service[] = [
      { id: "10", name: "Main", maxQuantity: 5, combinable },
      { id: "20", name: "Sub", maxQuantity: 3 },
    ];

    const getProviders = (serviceId: string, providerIds: string[] | null) => {
      const all = [office("1"), office("2")];
      if (!providerIds) {
        return all.filter((o) =>
          serviceId === "10" ? true : o.id === "2"
        );
      }
      return all.filter((o) => providerIds.includes(String(o.id)));
    };

    hydrateServiceFromSelectedMap(
      main,
      new Map([
        ["10", 2],
        ["20", 1],
      ]),
      catalog,
      getProviders
    );

    expect(main.count).toBe(2);
    expect(main.providers?.map((p) => String(p.id))).toEqual(["1", "2"]);
    expect(main.subServiceSelectionOrder).toEqual(["20"]);
    expect(main.subServices).toHaveLength(1);
    expect(main.subServices?.[0].id).toBe("20");
    expect(main.subServices?.[0].count).toBe(1);
    expect(main.subServices?.[0].providers.map((p) => String(p.id))).toEqual([
      "2",
    ]);
  });

  it("keeps zero-count combinable subs listed so the combination step can show them", () => {
    const combinable = {
      a: { "10": [1] },
      b: { "20": [1] },
    } as Combinable;
    const main = new ServiceImpl(
      "10",
      "Main",
      5,
      combinable,
      [],
      undefined,
      1,
      null,
      null,
      true
    );
    const catalog: Service[] = [
      { id: "10", name: "Main", maxQuantity: 5, combinable },
      { id: "20", name: "Sub", maxQuantity: 3 },
    ];

    hydrateServiceFromSelectedMap(
      main,
      new Map([["10", 1]]),
      catalog,
      () => [office("1")]
    );

    expect(main.subServices).toHaveLength(1);
    expect(main.subServices?.[0].count).toBe(0);
    expect(main.subServiceSelectionOrder).toEqual([]);
  });
});
