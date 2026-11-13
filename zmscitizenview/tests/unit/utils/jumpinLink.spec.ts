import { describe, expect, it } from "vitest";

import {
  buildJumpinHref,
  citizenviewPageHref,
  citizenviewRootHref,
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
