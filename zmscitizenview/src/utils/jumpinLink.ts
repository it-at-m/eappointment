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
