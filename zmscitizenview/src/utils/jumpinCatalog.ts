/**
 * Offices and services offered by the jump-in builder.
 * The lists come from this environment's catalog. A chosen service keeps only
 * its offices, and a chosen office keeps only its services.
 */

export type JumpinService = { id: string | number; name: string };

export type JumpinOffice = {
  id: string | number;
  name: string;
  disabledByServices?: Array<string | number>;
  allowDisabledServicesMix?: boolean | number[];
};

export type JumpinRelation = {
  serviceId: string | number;
  officeId: string | number;
};

export type JumpinChoice = {
  id: string;
  name: string;
  label: string;
};

export type JumpinCatalog = {
  services: JumpinService[];
  offices: JumpinOffice[];
  relations: JumpinRelation[];
};

/** Same "(Id: …) name" text the workstation Standort search uses. */
export function jumpinChoiceLabel(id: string, name: string): string {
  return `(Id: ${id}) ${name}`;
}

function allowsDisabledService(office: JumpinOffice): boolean {
  const mix = office.allowDisabledServicesMix;
  return mix === true || (Array.isArray(mix) && mix.length > 0);
}

/** A relation the booking page would accept for a jump-in link. */
export function isBookableJumpinPair(
  office: JumpinOffice,
  serviceId: string
): boolean {
  const disabled = (office.disabledByServices ?? []).map((id) => String(id));
  if (!disabled.includes(serviceId)) {
    return true;
  }
  return allowsDisabledService(office);
}

function choice(id: string, name: string): JumpinChoice {
  return { id, name, label: jumpinChoiceLabel(id, name) };
}

function byName(a: JumpinChoice, b: JumpinChoice): number {
  return a.name.localeCompare(b.name, "de");
}

const idInLabel = /^\(Id: ([^)]+)\)/;

/** The id shown at the start of a workstation-style "(Id: …) name" label. */
export function jumpinIdFromLabel(label: string): string | null {
  return label.trim().match(idInLabel)?.[1] ?? null;
}

/**
 * When the query is only digits, the choice whose id equals that query comes
 * first. Every other hit keeps its relative order.
 */
export function promoteExactIdResults<
  T extends { item: { label?: string }; rank: number },
>(results: T[], query: string): T[] {
  const id = query.trim();
  if (!/^\d+$/.test(id)) {
    return results;
  }
  const index = results.findIndex(
    (result) => jumpinIdFromLabel(result.item.label ?? "") === id
  );
  if (index <= 0) {
    return results;
  }
  const ordered = [
    results[index],
    ...results.slice(0, index),
    ...results.slice(index + 1),
  ];
  return ordered.map((result, position) => ({
    ...result,
    rank: position + 1,
  }));
}

export function jumpinChoices(
  catalog: JumpinCatalog,
  selected: { serviceId?: string; officeId?: string } = {}
): { services: JumpinChoice[]; offices: JumpinChoice[] } {
  const servicesById = new Map(
    catalog.services.map((service) => [String(service.id), service])
  );
  const officesById = new Map(
    catalog.offices.map((office) => [String(office.id), office])
  );

  const pairs: { serviceId: string; officeId: string }[] = [];
  for (const relation of catalog.relations) {
    const serviceId = String(relation.serviceId);
    const officeId = String(relation.officeId);
    const service = servicesById.get(serviceId);
    const office = officesById.get(officeId);
    if (!service || !office || !isBookableJumpinPair(office, serviceId)) {
      continue;
    }
    pairs.push({ serviceId, officeId });
  }

  const serviceIds = new Set(
    pairs
      .filter(
        (pair) => !selected.officeId || pair.officeId === selected.officeId
      )
      .map((pair) => pair.serviceId)
  );
  const officeIds = new Set(
    pairs
      .filter(
        (pair) => !selected.serviceId || pair.serviceId === selected.serviceId
      )
      .map((pair) => pair.officeId)
  );

  const services = [...serviceIds]
    .map((id) => choice(id, servicesById.get(id)?.name ?? id))
    .sort(byName);
  const offices = [...officeIds]
    .map((id) => choice(id, officesById.get(id)?.name ?? id))
    .sort(byName);

  return { services, offices };
}
