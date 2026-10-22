/**
 * The Bürgerservice search uses Choices.js, which substring-matches service
 * names and shows at most four hits. When the query is only digits, these
 * helpers take over and keep a service only when its id is equal.
 */

const RESULT_ROW_ATTRIBUTE = "data-exact-id-result";
const HIDDEN_CHOICE_ATTRIBUTE = "data-exact-id-hidden";

const SEARCH_FIELD_CLASS = "choices__input";
const DROPDOWN_SELECTOR = ".choices__list--dropdown";
const NAME_MATCH_SELECTOR = ".choices__item--choice";

export type ServiceIdMatch = {
  id: string | number;
  name: string;
};

export type ExactServiceIdSearchOptions<T extends ServiceIdMatch> = {
  services: T[];
  noResultMessage: string;
  onSelect: (service: T) => void;
};

export const isExactServiceIdQuery = (query: string): boolean =>
  /^\d+$/.test(query.trim());

export const findServiceWithExactId = <T extends { id: string | number }>(
  services: T[],
  query: string
): T | undefined => {
  const id = query.trim();
  if (!isExactServiceIdQuery(id)) return undefined;
  return services.find((service) => String(service.id) === id);
};

const searchFieldFrom = (event: Event): HTMLInputElement | null => {
  const target = event.target;
  if (!(target instanceof HTMLInputElement)) return null;
  if (!target.classList.contains(SEARCH_FIELD_CLASS)) return null;
  return target;
};

const formRootFrom = (event: Event): HTMLElement | null => {
  const root = event.currentTarget;
  return root instanceof HTMLElement ? root : null;
};

const isIdSearchNavigationKey = (key: string): boolean =>
  key === "Enter" || key === "ArrowDown" || key === "ArrowUp";

/** Put the dropdown back to the name matches Choices.js rendered. */
const restoreNameSearch = (root: ParentNode) => {
  root
    .querySelectorAll<HTMLElement>(`[${HIDDEN_CHOICE_ATTRIBUTE}]`)
    .forEach((choice) => {
      choice.style.display = "";
      choice.removeAttribute(HIDDEN_CHOICE_ATTRIBUTE);
    });
  root.querySelector(`[${RESULT_ROW_ATTRIBUTE}]`)?.remove();
};

const openDropdown = (root: ParentNode) => {
  root.querySelector(".choices")?.classList.add("is-open", "is-focused");

  const dropdown = root.querySelector<HTMLElement>(DROPDOWN_SELECTOR);
  dropdown?.classList.add("is-active");
  dropdown?.setAttribute("aria-expanded", "true");
};

const hideNameMatches = (dropdown: ParentNode) => {
  dropdown
    .querySelectorAll<HTMLElement>(NAME_MATCH_SELECTOR)
    .forEach((choice) => {
      choice.style.display = "none";
      choice.setAttribute(HIDDEN_CHOICE_ATTRIBUTE, "true");
    });
};

const showIdSearchResult = <T extends ServiceIdMatch>(
  dropdown: ParentNode,
  match: T | undefined,
  noResultMessage: string,
  onSelect: (service: T) => void
) => {
  const row = document.createElement("div");
  row.setAttribute(RESULT_ROW_ATTRIBUTE, "true");
  row.className = "choices__item choices__item--choice";

  if (!match) {
    row.classList.add("choices__notice", "has-no-results");
    row.textContent = noResultMessage;
    dropdown.appendChild(row);
    return;
  }

  row.classList.add("choices__item--selectable", "is-highlighted");
  row.setAttribute("role", "option");
  row.textContent = match.name;
  row.addEventListener("mousedown", (mouseEvent) => {
    mouseEvent.preventDefault();
    mouseEvent.stopPropagation();
    onSelect(match);
  });
  dropdown.appendChild(row);
};

export const handleExactServiceIdInput = <T extends ServiceIdMatch>(
  event: Event,
  options: ExactServiceIdSearchOptions<T>
) => {
  const input = searchFieldFrom(event);
  const root = formRootFrom(event);
  if (!input || !root) return;

  if (!isExactServiceIdQuery(input.value)) {
    restoreNameSearch(root);
    return;
  }

  const dropdown = root.querySelector<HTMLElement>(DROPDOWN_SELECTOR);
  if (!dropdown) return;

  // Choices would otherwise substring-match this keystroke against names.
  event.stopPropagation();
  restoreNameSearch(root);
  openDropdown(root);
  hideNameMatches(dropdown);
  showIdSearchResult(
    dropdown,
    findServiceWithExactId(options.services, input.value),
    options.noResultMessage,
    options.onSelect
  );
};

export const handleExactServiceIdKeydown = <T extends ServiceIdMatch>(
  event: KeyboardEvent,
  options: ExactServiceIdSearchOptions<T>
) => {
  const input = searchFieldFrom(event);
  if (!input || !isExactServiceIdQuery(input.value)) return;
  if (!isIdSearchNavigationKey(event.key)) return;

  // Arrow keys would move the highlight onto a hidden name match.
  event.preventDefault();
  event.stopPropagation();
  if (event.key !== "Enter") return;

  const match = findServiceWithExactId(options.services, input.value);
  if (match) options.onSelect(match);
};
