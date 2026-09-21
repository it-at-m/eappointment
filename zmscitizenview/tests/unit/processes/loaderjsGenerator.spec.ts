import { readFileSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";
import { describe, expect, it } from "vitest";

import { compatRootLoaderSource } from "../../../processes/lib/loaderjsGenerator.js";

const template = readFileSync(
  join(dirname(fileURLToPath(import.meta.url)), "../../../processes/lib/loader.js.template"),
  "utf8"
);

describe("compatRootLoaderSource", () => {
  it("loads wrapper.js next to the root loader, not one directory above", () => {
    const source = compatRootLoaderSource(
      template,
      "zms-appointment-webcomponent/loader.js"
    );

    expect(source).toContain(
      "new URL('./zms-appointment-webcomponent/loader.js', document.currentScript.src)"
    );
    expect(source).toContain("new URL('./wrapper.js', document.currentScript.src)");
    expect(source).not.toContain("./../wrapper.js");
  });
});
