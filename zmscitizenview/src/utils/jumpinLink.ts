/**
 * Host-page URLs for the citizenview shells.
 * `/` locally, `/buergeransicht/` on zms hosts.
 */
export function citizenviewRootHref(page: {
  origin: string;
  pathname: string;
}): string {
  if (page.pathname.startsWith("/buergeransicht")) {
    return `${page.origin}/buergeransicht/`;
  }
  return `${page.origin}/`;
}

export function citizenviewPageHref(
  page: { origin: string; pathname: string },
  file: string
): string {
  if (!file || file === "/" || file === "index.html") {
    return citizenviewRootHref(page);
  }
  return `${citizenviewRootHref(page)}${file.replace(/^\//, "")}`;
}

export type JumpinLinkInput = {
  serviceId: string;
  locationId?: string;
  exclusiveLocation?: boolean;
};

function numericId(value: string): string | null {
  const trimmed = value.trim();
  if (!/^\d+$/.test(trimmed)) {
    return null;
  }
  return trimmed;
}

export type JumpinLinkError =
  | "jumpinServiceIdMissing"
  | "jumpinServiceIdNumeric"
  | "jumpinLocationIdNumeric";

/** Why the current fields do not make a link. Empty fields are not an error. */
export function jumpinLinkError(
  input: JumpinLinkInput
): JumpinLinkError | null {
  const service = input.serviceId.trim();
  const location = (input.locationId ?? "").trim();
  if (!service && !location) {
    return null;
  }
  if (!service) {
    return "jumpinServiceIdMissing";
  }
  if (!numericId(service)) {
    return "jumpinServiceIdNumeric";
  }
  if (location && !numericId(location)) {
    return "jumpinLocationIdNumeric";
  }
  return null;
}

/**
 * Public Magnolia pages for an internal ZMS host.
 * The builder stays on zms*.muenchen.de; these are the links it hands out.
 * zms-test and zms-load have no public page.
 */
const EXTERNAL_JUMPIN_PAGES: Record<string, readonly string[]> = {
  "zms-dev.muenchen.de": [
    "https://stadt-i.muenchen.de/buergerservice/terminvereinbarung.html",
    "https://lhm-i.muenchen.swm.de/buergerservice/terminvereinbarung.html",
  ],
  "zms-demo.muenchen.de": [
    "https://stadt-k.muenchen.de/buergerservice/terminvereinbarung.html",
    "https://lhm-k.muenchen.swm.de/buergerservice/terminvereinbarung.html",
  ],
  "zms.muenchen.de": [
    "https://stadt.muenchen.de/buergerservice/terminvereinbarung.html",
  ],
};

export function externalJumpinPages(hostname: string): readonly string[] {
  const host = hostname.trim().toLowerCase().replace(/\.$/, "");
  return EXTERNAL_JUMPIN_PAGES[host] ?? [];
}

export type JumpinBuiltLink = {
  id: string;
  label: string;
  href: string;
};

/**
 * Internal booking link, then the public pages for this environment.
 * `internalLabel` is the visible name of the internal link.
 */
export function buildJumpinLinks(
  internalPageHref: string,
  hostname: string,
  input: JumpinLinkInput,
  internalLabel: string
): JumpinBuiltLink[] {
  const pages: { id: string; label: string; page: string }[] = [
    { id: "internal", label: internalLabel, page: internalPageHref },
  ];
  for (const page of externalJumpinPages(hostname)) {
    const label = new URL(page).hostname;
    pages.push({ id: label, label, page });
  }
  return pages.flatMap((entry) => {
    const href = buildJumpinHref(entry.page, input);
    if (!href) {
      return [];
    }
    return [{ id: entry.id, label: entry.label, href }];
  });
}

/**
 * Booking-page jump-in: `#/services/{id}` and optional `/locations/{id}`,
 * plus `exclusiveLocation=1` when the link should pin that Standort.
 */
export function buildJumpinHref(
  bookingPageHref: string,
  input: JumpinLinkInput
): string | null {
  if (jumpinLinkError(input)) {
    return null;
  }
  const serviceId = numericId(input.serviceId);
  if (!serviceId) {
    return null;
  }
  const locationId = numericId(input.locationId ?? "");
  let hash = `/services/${serviceId}`;
  if (locationId) {
    hash += `/locations/${locationId}`;
  }

  const url = new URL(bookingPageHref);
  if (input.exclusiveLocation) {
    url.searchParams.set("exclusiveLocation", "1");
  }
  url.hash = hash;
  return url.href;
}
