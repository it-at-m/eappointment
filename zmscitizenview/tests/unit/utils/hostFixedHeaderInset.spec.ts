import { afterEach, describe, expect, it, vi } from "vitest";

import { hostFixedHeaderInsetPx } from "@/utils/hostFixedHeaderInset";

function rect(bottom: number, width: number): DOMRect {
  return {
    top: 0,
    bottom,
    left: 0,
    right: width,
    width,
    height: bottom,
    x: 0,
    y: 0,
    toJSON() {
      return {};
    },
  } as DOMRect;
}

describe("hostFixedHeaderInsetPx", () => {
  afterEach(() => {
    document.body.replaceChildren();
    vi.restoreAllMocks();
  });

  it("uses the bottom of a full-width fixed host bar", () => {
    const header = document.createElement("header");
    header.style.position = "fixed";
    document.body.appendChild(header);
    vi.spyOn(header, "getBoundingClientRect").mockReturnValue(rect(53, 800));
    document.elementsFromPoint = () => [header];
    vi.spyOn(window, "innerWidth", "get").mockReturnValue(800);

    expect(hostFixedHeaderInsetPx()).toBe(53);
  });

  it("ignores fixed elements inside a shadow root", () => {
    const host = document.createElement("div");
    const inner = document.createElement("div");
    inner.style.position = "fixed";
    host.attachShadow({ mode: "open" }).append(inner);
    document.body.appendChild(host);
    vi.spyOn(inner, "getBoundingClientRect").mockReturnValue(rect(40, 800));
    document.elementsFromPoint = () => [inner];
    vi.spyOn(window, "innerWidth", "get").mockReturnValue(800);

    expect(hostFixedHeaderInsetPx()).toBe(0);
  });
});
