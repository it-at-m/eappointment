/**
 * Utility for creating blinking effects on DOM elements
 */

export interface BlinkOptions {
  duration?: number; // Total duration in ms
  interval?: number; // Blink interval in ms
  times?: number; // Number of blinks
  className?: string; // CSS class to toggle
}

const defaultOptions: Required<BlinkOptions> = {
  duration: 3000,
  interval: 300,
  times: 3,
  className: "blink",
};

/**
 * Create a blinking effect on an element
 */
export function blinkElement(
  element: HTMLElement,
  options: BlinkOptions = {},
): void {
  const opts = { ...defaultOptions, ...options };
  let blinkCount = 0;
  let isVisible = true;

  // Add the blink class initially
  element.classList.add(opts.className);

  const blinkInterval = setInterval(() => {
    if (blinkCount >= opts.times * 2) {
      // Stop blinking and ensure element is visible
      clearInterval(blinkInterval);
      element.classList.remove(opts.className);
      element.style.visibility = "visible";
      element.style.opacity = "1";
      return;
    }

    // Toggle visibility
    isVisible = !isVisible;
    element.style.visibility = isVisible ? "visible" : "hidden";
    element.style.opacity = isVisible ? "1" : "0.3";

    blinkCount++;
  }, opts.interval);

  // Safety timeout to ensure blinking stops
  setTimeout(() => {
    clearInterval(blinkInterval);
    element.classList.remove(opts.className);
    element.style.visibility = "visible";
    element.style.opacity = "1";
  }, opts.duration);
}

/**
 * Blink multiple elements with the same options
 */
export function blinkElements(
  elements: HTMLElement[],
  options: BlinkOptions = {},
): void {
  elements.forEach((element) => blinkElement(element, options));
}

/**
 * Blink elements by selector
 */
export function blinkElementsBySelector(
  selector: string,
  options: BlinkOptions = {},
): void {
  const elements = document.querySelectorAll(
    selector,
  ) as NodeListOf<HTMLElement>;
  blinkElements(Array.from(elements), options);
}

/**
 * Blink elements that match specific appointment IDs
 */
export function blinkElementsByAppointmentIds(
  appointmentIds: string[],
  options: BlinkOptions = {},
): void {
  appointmentIds.forEach((id) => {
    const elements = document.querySelectorAll(
      `[data-appointment="${id}"]`,
    ) as NodeListOf<HTMLElement>;
    blinkElements(Array.from(elements), options);
  });
}
