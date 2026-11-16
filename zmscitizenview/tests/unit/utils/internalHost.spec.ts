import { describe, expect, it } from "vitest";

import { isInternalZmsHost } from "@/utils/internalHost";

describe("isInternalZmsHost", () => {
  it("accepts the internal prod host and the other ZMS environments", () => {
    expect(isInternalZmsHost("zms.muenchen.de")).toBe(true);
    expect(isInternalZmsHost("ZMS.muenchen.de")).toBe(true);
    expect(isInternalZmsHost("zms-dev.muenchen.de")).toBe(true);
    expect(isInternalZmsHost("zms-test.muenchen.de")).toBe(true);
    expect(isInternalZmsHost("zms-demo.muenchen.de")).toBe(true);
    expect(isInternalZmsHost("zms-load.muenchen.de")).toBe(true);
  });

  it("accepts local development hosts", () => {
    expect(isInternalZmsHost("localhost")).toBe(true);
    expect(isInternalZmsHost("127.0.0.1")).toBe(true);
    expect(isInternalZmsHost("[::1]")).toBe(true);
    expect(isInternalZmsHost("zms.ddev.site")).toBe(true);
  });

  it("rejects the public booking hosts", () => {
    expect(isInternalZmsHost("stadt.muenchen.de")).toBe(false);
    expect(isInternalZmsHost("www.stadt.muenchen.de")).toBe(false);
    expect(isInternalZmsHost("stadt-i.muenchen.de")).toBe(false);
    expect(isInternalZmsHost("stadt-k.muenchen.de")).toBe(false);
    expect(isInternalZmsHost("www48.muenchen.de")).toBe(false);
    expect(isInternalZmsHost("muenchen.de")).toBe(false);
    expect(isInternalZmsHost("lhm-i.muenchen.swm.de")).toBe(false);
    expect(isInternalZmsHost("")).toBe(false);
    expect(isInternalZmsHost(undefined)).toBe(false);
  });
});
