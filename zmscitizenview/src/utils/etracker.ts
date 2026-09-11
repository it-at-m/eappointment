/**
 * Push tracking to the host-page etracker snippet (Magnolia).
 *
 * CSS selectors in the etracker tag manager cannot see into the webcomponent
 * shadow trees, so the components call etracker's JS API instead.
 * No-ops when etracker is not on the page (local, tests, ATAF).
 * Never send personal data (name, mail, phone, auth hash, process id).
 */

export const ETRACKER_AREAS = "Buergerservice/Terminvereinbarung";
export const ETRACKER_CATEGORY = "Terminvereinbarung";
export const ETRACKER_LOADER_ID = "_etLoader";

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
