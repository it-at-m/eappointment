import { describe, expect, it } from "vitest";

import {
  officeChoices,
  recordsForOfficeId,
  recordsForScopeId,
  scopeChoices,
  scopeOfficeRecords,
} from "@/utils/scopeOfficeLookup";

const offices = [
  {
    id: 9999998,
    name: "Unittest",
    scope: { id: 1, shortName: "Scope 1" },
  },
  {
    id: 9999999,
    name: "Unittest 2",
    scope: { id: 2, shortName: "Scope 2" },
  },
  {
    id: 42,
    name: "Ohne Scope",
    scope: null,
  },
];

describe("scopeOfficeRecords", () => {
  it("pairs each office with the scope on the catalog payload", () => {
    expect(scopeOfficeRecords(offices)).toEqual([
      {
        officeId: "9999998",
        officeName: "Unittest",
        scopeId: "1",
        scopeName: "Scope 1",
      },
      {
        officeId: "9999999",
        officeName: "Unittest 2",
        scopeId: "2",
        scopeName: "Scope 2",
      },
      {
        officeId: "42",
        officeName: "Ohne Scope",
        scopeId: "",
        scopeName: "Ohne Scope",
      },
    ]);
  });
});

describe("scope and office lookup", () => {
  const records = scopeOfficeRecords(offices);

  it("finds the office for a scope id and the scope for an office id", () => {
    expect(recordsForScopeId(records, "1").map((row) => row.officeId)).toEqual([
      "9999998",
    ]);
    expect(recordsForOfficeId(records, "9999999")).toMatchObject([
      {
        scopeId: "2",
        scopeName: "Scope 2",
        officeName: "Unittest 2",
      },
    ]);
  });

  it("lists every scope of one office", () => {
    const several = scopeOfficeRecords([
      {
        id: 10,
        name: "Pasing",
        scope: { id: 1, shortName: "Schalter A" },
        scopes: [
          { id: 1, shortName: "Schalter A" },
          { id: 2, shortName: "Schalter B" },
        ],
      },
    ]);

    expect(several.map((row) => row.scopeId)).toEqual(["1", "2"]);
    expect(officeChoices(several).map((choice) => choice.id)).toEqual(["10"]);
    expect(scopeChoices(several).map((choice) => choice.label)).toEqual([
      "(Id: 1) Schalter A",
      "(Id: 2) Schalter B",
    ]);
    expect(recordsForOfficeId(several, "10")).toHaveLength(2);
    expect(recordsForScopeId(several, "2").map((row) => row.officeId)).toEqual([
      "10",
    ]);
  });

  it("lists scopes and offices by name, and omits offices without a scope from the scope list", () => {
    expect(scopeChoices(records).map((choice) => choice.label)).toEqual([
      "(Id: 1) Scope 1",
      "(Id: 2) Scope 2",
    ]);
    expect(officeChoices(records).map((choice) => choice.id)).toEqual([
      "42",
      "9999998",
      "9999999",
    ]);
  });

  it("returns nothing for an unknown id", () => {
    expect(recordsForScopeId(records, "9")).toEqual([]);
    expect(recordsForOfficeId(records, "7")).toEqual([]);
  });
});
