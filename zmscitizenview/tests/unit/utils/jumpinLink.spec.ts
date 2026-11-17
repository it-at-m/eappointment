import { describe, expect, it } from "vitest";

import {
  buildJumpinHref,
  buildJumpinLinks,
  citizenviewPageHref,
  citizenviewRootHref,
  externalJumpinPages,
  jumpinLinkError,
} from "@/utils/jumpinLink";

const local = { origin: "http://localhost:8082", pathname: "/" };
const hosted = {
  origin: "https://zms-dev.muenchen.de",
  pathname: "/buergeransicht/appointment-detail.html",
};

describe("citizenviewPageHref", () => {
  it("uses the site root locally", () => {
    expect(citizenviewRootHref(local)).toBe("http://localhost:8082/");
    expect(citizenviewPageHref(local, "jumpin-link-builder.html")).toBe(
      "http://localhost:8082/jumpin-link-builder.html"
    );
  });

  it("keeps /buergeransicht/ on zms hosts", () => {
    expect(citizenviewRootHref(hosted)).toBe(
      "https://zms-dev.muenchen.de/buergeransicht/"
    );
    expect(citizenviewPageHref(hosted, "jumpin-link-builder.html")).toBe(
      "https://zms-dev.muenchen.de/buergeransicht/jumpin-link-builder.html"
    );
  });
});

describe("buildJumpinHref", () => {
  const booking = "https://zms-dev.muenchen.de/buergeransicht/";

  it("builds a service jump-in", () => {
    expect(buildJumpinHref(booking, { serviceId: "10242339" })).toBe(
      "https://zms-dev.muenchen.de/buergeransicht/#/services/10242339"
    );
  });

  it("adds a location and the exclusive Standort flag", () => {
    expect(
      buildJumpinHref(booking, {
        serviceId: " 10242339 ",
        locationId: "102522",
        exclusiveLocation: true,
      })
    ).toBe(
      "https://zms-dev.muenchen.de/buergeransicht/?exclusiveLocation=1#/services/10242339/locations/102522"
    );
  });

  it("returns nothing until the service id is a number", () => {
    expect(buildJumpinHref(booking, { serviceId: "" })).toBeNull();
    expect(buildJumpinHref(booking, { serviceId: "abc" })).toBeNull();
    expect(jumpinLinkError({ serviceId: "abc" })).toBe(
      "jumpinServiceIdNumeric"
    );
  });

  it("rejects a non-numeric Standort id", () => {
    expect(
      buildJumpinHref(booking, { serviceId: "12", locationId: "nord" })
    ).toBeNull();
    expect(jumpinLinkError({ serviceId: "12", locationId: "nord" })).toBe(
      "jumpinLocationIdNumeric"
    );
  });
});

describe("externalJumpinPages", () => {
  it("maps each internal environment to its public pages", () => {
    expect(externalJumpinPages("zms-dev.muenchen.de")).toEqual([
      "https://stadt-i.muenchen.de/buergerservice/terminvereinbarung.html",
      "https://lhm-i.muenchen.swm.de/buergerservice/terminvereinbarung.html",
    ]);
    expect(externalJumpinPages("zms-demo.muenchen.de")).toEqual([
      "https://stadt-k.muenchen.de/buergerservice/terminvereinbarung.html",
      "https://lhm-k.muenchen.swm.de/buergerservice/terminvereinbarung.html",
    ]);
    expect(externalJumpinPages("zms.muenchen.de")).toEqual([
      "https://stadt.muenchen.de/buergerservice/terminvereinbarung.html",
    ]);
  });

  it("returns nothing for hosts without a public page", () => {
    expect(externalJumpinPages("zms-test.muenchen.de")).toEqual([]);
    expect(externalJumpinPages("zms-load.muenchen.de")).toEqual([]);
    expect(externalJumpinPages("localhost")).toEqual([]);
    expect(externalJumpinPages("stadt.muenchen.de")).toEqual([]);
    expect(externalJumpinPages("www48.muenchen.de")).toEqual([]);
  });
});

describe("buildJumpinLinks", () => {
  const input = { serviceId: "1080582", locationId: "54261" };
  const internal = "https://zms-dev.muenchen.de/buergeransicht/";

  it("keeps the internal link and adds the public pages for that environment", () => {
    expect(
      buildJumpinLinks(internal, "zms-dev.muenchen.de", input, "Intern")
    ).toEqual([
      {
        id: "internal",
        label: "Intern",
        href: "https://zms-dev.muenchen.de/buergeransicht/#/services/1080582/locations/54261",
      },
      {
        id: "stadt-i.muenchen.de",
        label: "stadt-i.muenchen.de",
        href: "https://stadt-i.muenchen.de/buergerservice/terminvereinbarung.html#/services/1080582/locations/54261",
      },
      {
        id: "lhm-i.muenchen.swm.de",
        label: "lhm-i.muenchen.swm.de",
        href: "https://lhm-i.muenchen.swm.de/buergerservice/terminvereinbarung.html#/services/1080582/locations/54261",
      },
    ]);
  });

  it("uses the prod public page on zms.muenchen.de", () => {
    expect(
      buildJumpinLinks(
        "https://zms.muenchen.de/buergeransicht/",
        "zms.muenchen.de",
        input,
        "Intern"
      ).map((link) => link.href)
    ).toEqual([
      "https://zms.muenchen.de/buergeransicht/#/services/1080582/locations/54261",
      "https://stadt.muenchen.de/buergerservice/terminvereinbarung.html#/services/1080582/locations/54261",
    ]);
  });

  it("pins the Standort on the public page too", () => {
    const [external] = buildJumpinLinks(
      "https://zms-demo.muenchen.de/buergeransicht/",
      "zms-demo.muenchen.de",
      { ...input, exclusiveLocation: true },
      "Intern"
    ).filter((link) => link.id === "stadt-k.muenchen.de");
    expect(external?.href).toBe(
      "https://stadt-k.muenchen.de/buergerservice/terminvereinbarung.html?exclusiveLocation=1#/services/1080582/locations/54261"
    );
  });
});
