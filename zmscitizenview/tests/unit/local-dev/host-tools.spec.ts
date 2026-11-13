import { afterEach, describe, expect, it } from "vitest";

import { renderHostTools } from "@/local-dev/host-tools";

describe("renderHostTools", () => {
  afterEach(() => {
    document.getElementById("zms-host-tools")?.remove();
    document.getElementById("zms-host-tools-menu")?.remove();
    document.getElementById("zms-host-tools-style")?.remove();
  });

  it("pins a Tools menu to the top right with the jump-in builder", () => {
    renderHostTools();

    const host = document.getElementById("zms-host-tools");
    expect(host?.getAttribute("style")).toContain("right:0");
    const button = host?.querySelector("button");
    expect(button?.textContent).toBe("Tools");
    expect(button?.className).toBe("m-button m-button--primary");
    expect(button?.querySelector("svg")?.getAttribute("class")).toContain(
      "m-button__icon--before"
    );

    const item = document.querySelector("#zms-host-tools-menu a");
    expect(item?.textContent).toBe("Jump-in Link Builder");
    expect(item?.getAttribute("href")).toMatch(/jumpin-link-builder\.html$/);

    host?.querySelector("button")?.click();
    expect(document.getElementById("zms-host-tools-menu")?.hidden).toBe(false);

    const home = host?.querySelector("[aria-label='Startseite']");
    expect(home?.tagName).toBe("BUTTON");
    expect(home?.querySelector("svg")).not.toBeNull();
    expect(home?.compareDocumentPosition(button!)).toBe(
      Node.DOCUMENT_POSITION_PRECEDING
    );
    const toolsRight = button?.getBoundingClientRect().right ?? 0;
    const homeLeft = home?.getBoundingClientRect().left ?? 0;
    expect(homeLeft - toolsRight).toBe(0);
    const menu = document.getElementById("zms-host-tools-menu");
    expect(menu ? getComputedStyle(menu).position : "").toBe("fixed");
    expect(menu?.parentElement).toBe(document.body);
    expect(button?.parentElement).toBe(host);
    const focusStyle = document.getElementById(
      "zms-host-tools-style"
    )?.textContent;
    expect(focusStyle).toContain("outline:none");
    expect(focusStyle).toContain("box-shadow:none");
  });

  it("returns focus to Tools when Escape closes a focused menu", () => {
    renderHostTools();
    const button = document.querySelector(
      "#zms-host-tools button"
    ) as HTMLButtonElement;
    button.click();
    const item = document.querySelector(
      "#zms-host-tools-menu a"
    ) as HTMLAnchorElement;
    item.focus();

    document.dispatchEvent(new KeyboardEvent("keydown", { key: "Escape" }));

    expect(document.getElementById("zms-host-tools-menu")?.hidden).toBe(true);
    expect(document.activeElement).toBe(button);
  });

  it("leaves focus outside the menu when Escape is pressed there", () => {
    renderHostTools();
    const home = document.querySelector(
      "[aria-label='Startseite']"
    ) as HTMLButtonElement;
    home.focus();

    document.dispatchEvent(new KeyboardEvent("keydown", { key: "Escape" }));

    expect(document.activeElement).toBe(home);
  });

  it("renders the menu once", () => {
    renderHostTools();
    renderHostTools();
    expect(document.querySelectorAll("#zms-host-tools")).toHaveLength(1);
  });
});
