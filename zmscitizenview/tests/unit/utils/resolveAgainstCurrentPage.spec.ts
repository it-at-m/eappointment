import { afterEach, describe, expect, it, vi } from "vitest";

import { resolveAgainstCurrentPage } from "@/utils/Constants";

describe("resolveAgainstCurrentPage", () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it("keeps relative appointment-detail under /buergeransicht/", () => {
    vi.stubGlobal("location", {
      href: "https://zms-dev.muenchen.de/buergeransicht/",
    });

    expect(resolveAgainstCurrentPage("appointment-detail.html").pathname).toBe(
      "/buergeransicht/appointment-detail.html"
    );
  });

  it("keeps absolute URLs unchanged (Magnolia embed)", () => {
    vi.stubGlobal("location", {
      href: "https://zms-dev.muenchen.de/buergeransicht/",
    });

    const absolute =
      "https://stadt.muenchen.de/buergerservice/terminvereinbarung/detail.html";
    expect(resolveAgainstCurrentPage(absolute).href).toBe(absolute);
  });

  it("does not resolve relative URLs against origin alone", () => {
    vi.stubGlobal("location", {
      href: "https://zms-dev.muenchen.de/buergeransicht/",
    });

    expect(resolveAgainstCurrentPage("appointment-detail.html").href).not.toBe(
      "https://zms-dev.muenchen.de/appointment-detail.html"
    );
  });
});
