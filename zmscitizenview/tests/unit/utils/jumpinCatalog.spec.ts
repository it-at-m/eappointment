import { describe, expect, it } from "vitest";

import {
  jumpinChoiceLabel,
  jumpinChoices,
  jumpinIdFromLabel,
  promoteExactIdResults,
} from "@/utils/jumpinCatalog";
import {
  installJumpinExactIdSearch,
  patchChoicesSearcher,
} from "@/utils/jumpinChoiceSearch";

const reisepass = { id: "10", name: "Reisepass" };
const ausweis = { id: "20", name: "Personalausweis" };
const heer = { id: "100", name: "Bürgeramt Heerstraße" };
const pasing = {
  id: "200",
  name: "Bürgeramt Pasing",
  disabledByServices: ["20"],
};
const mixOffice = {
  id: "300",
  name: "Bürgeramt Mix",
  disabledByServices: ["20"],
  allowDisabledServicesMix: true,
};

const catalog = {
  services: [reisepass, ausweis],
  offices: [heer, pasing, mixOffice],
  relations: [
    { serviceId: "10", officeId: "100" },
    { serviceId: "10", officeId: "200" },
    { serviceId: "20", officeId: "200" },
    { serviceId: "20", officeId: "300" },
    { serviceId: "99", officeId: "100" },
  ],
};

describe("jumpinChoices", () => {
  it("labels a row so the search can match the id or the name", () => {
    expect(jumpinChoiceLabel("100", "Bürgeramt Heerstraße")).toBe(
      "(Id: 100) Bürgeramt Heerstraße"
    );
  });

  it("lists bookable services and offices from this environment", () => {
    const choices = jumpinChoices(catalog);
    expect(choices.services.map((service) => service.id)).toEqual(["20", "10"]);
    expect(choices.offices.map((office) => office.id)).toEqual([
      "100",
      "300",
      "200",
    ]);
  });

  it("keeps only offices that offer the selected service", () => {
    const choices = jumpinChoices(catalog, { serviceId: "10" });
    expect(choices.offices.map((office) => office.id)).toEqual(["100", "200"]);
    expect(choices.services.map((service) => service.id)).toEqual(["20", "10"]);
  });

  it("keeps only services offered at the selected office", () => {
    const choices = jumpinChoices(catalog, { officeId: "100" });
    expect(choices.services.map((service) => service.id)).toEqual(["10"]);
    expect(choices.offices.map((office) => office.name)).toContain(
      "Bürgeramt Heerstraße"
    );
  });

  it("puts the exact id first when the query is only digits", () => {
    const hits = [
      { item: { label: "(Id: 10) Reisepass" }, rank: 1, score: 0 },
      { item: { label: "(Id: 21) Fundbüro" }, rank: 2, score: 0 },
      { item: { label: "(Id: 1) Auskunft" }, rank: 3, score: 0 },
      { item: { label: "(Id: 100) Heerstraße" }, rank: 4, score: 0 },
    ];

    const byOne = promoteExactIdResults(hits, "1");
    expect(byOne.map((hit) => jumpinIdFromLabel(hit.item.label))).toEqual([
      "1",
      "10",
      "21",
      "100",
    ]);
    expect(byOne.map((hit) => hit.rank)).toEqual([1, 2, 3, 4]);

    const byTen = promoteExactIdResults(hits, " 10 ");
    expect(byTen.map((hit) => jumpinIdFromLabel(hit.item.label))).toEqual([
      "10",
      "21",
      "1",
      "100",
    ]);
  });

  it("leaves name searches and an already-first id in place", () => {
    const hits = [
      { item: { label: "(Id: 10) Reisepass" }, rank: 1 },
      { item: { label: "(Id: 1) Auskunft" }, rank: 2 },
    ];
    expect(promoteExactIdResults(hits, "reisepass")).toBe(hits);
    expect(promoteExactIdResults(hits, "10")).toBe(hits);
    expect(promoteExactIdResults(hits, "1a")).toBe(hits);
  });

  it("re-ranks a Choices search so the exact id is the first hit", () => {
    const searcher = {
      search(query: string) {
        return [
          { item: { label: `(Id: 10) ${query}` }, rank: 1 },
          { item: { label: "(Id: 1) Auskunft" }, rank: 2 },
        ];
      },
    };
    patchChoicesSearcher({ _searcher: searcher });
    patchChoicesSearcher({ _searcher: searcher });

    const found = searcher.search("1");
    expect(found.map((hit) => jumpinIdFromLabel(hit.item.label))).toEqual([
      "1",
      "10",
    ]);
    expect(found.map((hit) => hit.rank)).toEqual([1, 2]);
  });

  it("patches Choices when the select binds its methods", async () => {
    installJumpinExactIdSearch();
    const instance = {
      config: {
        searchResultLimit: 4,
        classNames: { inputCloned: ["choices__input--cloned"] },
      },
      _searcher: {
        search(_query: string) {
          return [
            { item: { label: "(Id: 12) Zwölf" }, rank: 1 },
            { item: { label: "(Id: 2) Zwei" }, rank: 2 },
          ];
        },
      },
    };
    function ready(this: typeof instance) {
      return this;
    }
    ready.bind(instance);
    await Promise.resolve();

    const found = instance._searcher.search("2");
    expect(found.map((hit) => jumpinIdFromLabel(hit.item.label))).toEqual([
      "2",
      "12",
    ]);
  });

  it("drops a service an office has disabled, unless mixing is allowed", () => {
    const atPasing = jumpinChoices(catalog, { officeId: "200" });
    expect(atPasing.services.map((service) => service.id)).toEqual(["10"]);

    const atMix = jumpinChoices(catalog, { officeId: "300" });
    expect(atMix.services.map((service) => service.id)).toEqual(["20"]);
  });
});
