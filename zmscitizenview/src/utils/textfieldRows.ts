import type { Ref } from "vue";

const MIN_ROWS = 2;
const WIDTH_FACTOR = 0.8;
const MAX_WIDTH = 558;
const FALLBACK_LINE_HEIGHT = 16;

let measurer: HTMLDivElement | null = null;

// Returns the shared hidden element we use to measure rendered text height
const getMeasurer = (): HTMLDivElement | null => {
  if (measurer) return measurer;
  if (typeof document === "undefined") return null;

  const el = document.createElement("div");
  el.style.visibility = "hidden";
  el.style.position = "absolute";
  el.style.whiteSpace = "pre-wrap";
  el.style.font = "inherit";
  el.setAttribute("aria-hidden", "true");
  document.body.appendChild(el);

  measurer = el;
  return measurer;
};

export const countLines = (text: string): number => {
  if (typeof window === "undefined") return MIN_ROWS;

  const el = getMeasurer();
  if (!el) return MIN_ROWS;

  el.style.width = `${Math.min(MAX_WIDTH, window.innerWidth * WIDTH_FACTOR)}px`;
  el.textContent = text ?? "";

  const lineHeight =
    parseFloat(getComputedStyle(el).lineHeight) || FALLBACK_LINE_HEIGHT;
  const lines =
    lineHeight > 0 ? Math.ceil(el.scrollHeight / lineHeight) : MIN_ROWS;

  return Math.max(MIN_ROWS, lines + 2);
};

export const updateInputLines = (
  text: string,
  inputLines: Ref<number>
): void => {
  inputLines.value = countLines(text);
};

export const handleInput = (inputLines: Ref<number>, event: Event): void => {
  const target = event.target as HTMLTextAreaElement | null;
  if (!target) return;
  updateInputLines(target.value ?? "", inputLines);
};
