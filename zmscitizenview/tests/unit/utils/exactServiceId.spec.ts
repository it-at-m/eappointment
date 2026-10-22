import { describe, expect, it, vi } from "vitest";

import {
  findServiceWithExactId,
  handleExactServiceIdInput,
  handleExactServiceIdKeydown,
  isExactServiceIdQuery,
} from "@/utils/exactServiceId";

const services = [
  { id: "3", name: "Auskunft zur Rente" },
  { id: "1063422", name: "Gewerbe-Ummeldung" },
  { id: "1063423", name: "Gewerbe-Anmeldung" },
];

describe("exactServiceId", () => {
  it("treats a digits-only query as a service id", () => {
    expect(isExactServiceIdQuery("1063423")).toBe(true);
    expect(isExactServiceIdQuery(" 3 ")).toBe(true);
    expect(isExactServiceIdQuery("10634")).toBe(true);
    expect(isExactServiceIdQuery("Reisepass")).toBe(false);
    expect(isExactServiceIdQuery("1063423a")).toBe(false);
    expect(isExactServiceIdQuery("")).toBe(false);
  });

  it("returns the service only when the id is equal to the query", () => {
    expect(findServiceWithExactId(services, "1063423")?.name).toBe(
      "Gewerbe-Anmeldung"
    );
    expect(findServiceWithExactId(services, "3")?.name).toBe(
      "Auskunft zur Rente"
    );
    expect(findServiceWithExactId(services, "10634")).toBeUndefined();
    expect(findServiceWithExactId(services, "106342")).toBeUndefined();
    expect(findServiceWithExactId(services, "Reisepass")).toBeUndefined();
  });

  describe("dropdown override", () => {
    const mountSearch = () => {
      const form = document.createElement("form");
      form.innerHTML = `
        <div class="choices">
          <input class="choices__input choices__input--cloned" />
          <div class="choices__list choices__list--dropdown">
            <div class="choices__item choices__item--choice">Reisepass</div>
            <div class="choices__item choices__item--choice">Personalausweis</div>
          </div>
        </div>
      `;
      document.body.appendChild(form);
      const input = form.querySelector("input") as HTMLInputElement;
      const reachedChoices = vi.fn();
      input.addEventListener("input", reachedChoices);
      input.addEventListener("keydown", reachedChoices);
      form.addEventListener(
        "input",
        (event) => handleExactServiceIdInput(event, options),
        true
      );
      form.addEventListener(
        "keydown",
        (event) => handleExactServiceIdKeydown(event as KeyboardEvent, options),
        true
      );
      return { form, input, reachedChoices };
    };

    const options = {
      services,
      noResultMessage: "Keine Leistung gefunden",
      onSelect: vi.fn(),
    };

    const visibleLabels = (form: HTMLElement) =>
      [...form.querySelectorAll<HTMLElement>(".choices__item")]
        .filter((el) => el.style.display !== "none")
        .map((el) => el.textContent);

    it("shows only the service whose id equals the query", () => {
      const { form, input, reachedChoices } = mountSearch();
      input.value = "1063423";
      input.dispatchEvent(new Event("input", { bubbles: true }));

      expect(reachedChoices).not.toHaveBeenCalled();
      expect(visibleLabels(form)).toEqual(["Gewerbe-Anmeldung"]);
      form.remove();
    });

    it("shows no result for a partial id and restores name search afterwards", () => {
      const { form, input, reachedChoices } = mountSearch();
      input.value = "10634";
      input.dispatchEvent(new Event("input", { bubbles: true }));
      expect(visibleLabels(form)).toEqual(["Keine Leistung gefunden"]);

      input.value = "Reisepass";
      input.dispatchEvent(new Event("input", { bubbles: true }));
      expect(reachedChoices).toHaveBeenCalledOnce();
      expect(visibleLabels(form)).toEqual(["Reisepass", "Personalausweis"]);
      form.remove();
    });

    it("selects the exact service on enter and ignores arrow keys", () => {
      const { form, input } = mountSearch();
      options.onSelect.mockClear();
      input.value = "3";
      input.dispatchEvent(
        new KeyboardEvent("keydown", { key: "ArrowDown", bubbles: true })
      );
      expect(options.onSelect).not.toHaveBeenCalled();

      input.dispatchEvent(
        new KeyboardEvent("keydown", { key: "Enter", bubbles: true })
      );
      expect(options.onSelect).toHaveBeenCalledWith(services[0]);
      form.remove();
    });
  });
});
