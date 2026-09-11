/**
 * Push tracking to the host-page etracker snippet (Magnolia).
 *
 * Tag-manager CSS cannot see into shadow trees. Clicks are declared with
 * `data-etracker` on the control; a capture listener uses composedPath()
 * so nested open shadows still match. Outcomes that are not clicks (wizard
 * steps, booking success) still call the API.
 *
 * No-ops when etracker is not on the page. Never send personal data.
 */

export const ETRACKER_AREAS = "Buergerservice/Terminvereinbarung";
export const ETRACKER_CATEGORY = "Terminvereinbarung";
export const ETRACKER_LOADER_ID = "_etLoader";
export const ETRACKER_ATTR = "data-etracker";
export const ETRACKER_ACTION_ATTR = "data-etracker-action";

export const ETRACKER_COMPONENT = {
  appointment: "zms-appointment",
  detail: "zms-appointment-detail",
  overview: "zms-appointment-overview",
  slider: "zms-appointment-slider",
} as const;

export const ETRACKER_PAGES = {
  service: "Terminvereinbarung/Leistung",
  appointment: "Terminvereinbarung/Termin",
  contact: "Terminvereinbarung/Kontakt",
  overview: "Terminvereinbarung/Ueberblick",
  preconfirm: "Terminvereinbarung/Preconfirm",
  cancel: "Terminvereinbarung/Storno",
  confirmation: "Terminvereinbarung/Bestaetigung",
} as const;

export type EtrackerComponent =
  (typeof ETRACKER_COMPONENT)[keyof typeof ETRACKER_COMPONENT];

const COMPONENT_BY_TAG: Record<string, EtrackerComponent> = {
  "zms-appointment": ETRACKER_COMPONENT.appointment,
  "zms-appointment-wrapped": ETRACKER_COMPONENT.appointment,
  "zms-appointment-i18n-host": ETRACKER_COMPONENT.appointment,
  "zms-appointment-detail": ETRACKER_COMPONENT.detail,
  "zms-appointment-detail-wrapped": ETRACKER_COMPONENT.detail,
  "zms-appointment-detail-i18n-host": ETRACKER_COMPONENT.detail,
  "zms-appointment-overview": ETRACKER_COMPONENT.overview,
  "zms-appointment-overview-wrapped": ETRACKER_COMPONENT.overview,
  "zms-appointment-overview-i18n-host": ETRACKER_COMPONENT.overview,
  "zms-appointment-slider": ETRACKER_COMPONENT.slider,
  "zms-appointment-slider-wrapped": ETRACKER_COMPONENT.slider,
  "zms-appointment-slider-i18n-host": ETRACKER_COMPONENT.slider,
};

type EtrackerUserDefinedEventCtor = new (
  objectName: string,
  category: string,
  action?: string,
  type?: string
) => unknown;

interface EtrackerApi {
  sendEvent: (event: unknown) => void;
}

interface EtrackerWindow extends Window {
  _etracker?: EtrackerApi;
  _etrackerOnReady?: Array<() => void>;
  et_eC_Wrapper?: (params: {
    et_et?: string;
    et_pagename?: string;
    et_areas?: string;
  }) => void;
  et_UserDefinedEvent?: EtrackerUserDefinedEventCtor;
}

function etrackerWindow(): EtrackerWindow {
  return window as EtrackerWindow;
}

function isEtrackerReady(): boolean {
  const tracker = etrackerWindow()._etracker;
  return typeof tracker === "object" && tracker !== null;
}

function whenEtrackerReady(fn: () => void): void {
  if (isEtrackerReady()) {
    fn();
    return;
  }
  const w = etrackerWindow();
  w._etrackerOnReady = w._etrackerOnReady ?? [];
  w._etrackerOnReady.push(fn);
}

export function getEtrackerAccountKey(): string | undefined {
  const fromLoader = document
    .getElementById(ETRACKER_LOADER_ID)
    ?.getAttribute("data-secure-code");
  if (fromLoader) {
    return fromLoader;
  }
  return undefined;
}

/**
 * Map booking wizard `currentView` to a virtual pagename.
 * View 4 is either the preconfirm (e-mail) screen or a cancel result.
 */
export function bookingPageName(
  view: number,
  canceled: boolean
): string | undefined {
  switch (view) {
    case 0:
      return ETRACKER_PAGES.service;
    case 1:
      return ETRACKER_PAGES.appointment;
    case 2:
      return ETRACKER_PAGES.contact;
    case 3:
      return ETRACKER_PAGES.overview;
    case 4:
      return canceled ? ETRACKER_PAGES.cancel : ETRACKER_PAGES.preconfirm;
    case 5:
      return ETRACKER_PAGES.confirmation;
    default:
      return undefined;
  }
}

export function trackAppointmentPage(pagename: string): void {
  whenEtrackerReady(() => {
    const wrapper = etrackerWindow().et_eC_Wrapper;
    if (typeof wrapper !== "function") {
      return;
    }
    const params: {
      et_et?: string;
      et_pagename: string;
      et_areas: string;
    } = {
      et_pagename: pagename,
      et_areas: ETRACKER_AREAS,
    };
    const accountKey = getEtrackerAccountKey();
    if (accountKey) {
      params.et_et = accountKey;
    }
    wrapper(params);
  });
}

export function trackBookingView(view: number, canceled: boolean): void {
  const pagename = bookingPageName(view, canceled);
  if (pagename) {
    trackAppointmentPage(pagename);
  }
}

export function trackCitizenEvent(options: {
  object: string;
  action: string;
  type: string;
}): void {
  whenEtrackerReady(() => {
    const w = etrackerWindow();
    if (
      typeof w._etracker !== "object" ||
      typeof w._etracker.sendEvent !== "function" ||
      typeof w.et_UserDefinedEvent !== "function"
    ) {
      return;
    }
    w._etracker.sendEvent(
      new w.et_UserDefinedEvent(
        options.object,
        ETRACKER_CATEGORY,
        options.action,
        options.type
      )
    );
  });
}

function componentTypeFromPath(path: EventTarget[]): string {
  for (const node of path) {
    if (!(node instanceof Element)) {
      continue;
    }
    const mapped = COMPONENT_BY_TAG[node.localName];
    if (mapped) {
      return mapped;
    }
  }
  return "zms-citizenview";
}

function onTrackedClick(event: Event): void {
  const path = event.composedPath();
  for (const node of path) {
    if (!(node instanceof Element)) {
      continue;
    }
    const object = node.getAttribute(ETRACKER_ATTR);
    if (!object) {
      continue;
    }
    trackCitizenEvent({
      object,
      action: node.getAttribute(ETRACKER_ACTION_ATTR) ?? "click",
      type: componentTypeFromPath(path),
    });
    return;
  }
}

let clickTrackingInstalled = false;

export function installEtrackerClickTracking(): void {
  if (clickTrackingInstalled || typeof document === "undefined") {
    return;
  }
  clickTrackingInstalled = true;
  document.addEventListener("click", onTrackedClick, true);
}

export function resetEtrackerClickTrackingForTests(): void {
  if (!clickTrackingInstalled || typeof document === "undefined") {
    return;
  }
  document.removeEventListener("click", onTrackedClick, true);
  clickTrackingInstalled = false;
}

installEtrackerClickTracking();
