import type { JumpinChoice } from "@/utils/jumpinCatalog";

import { jumpinChoiceLabel } from "@/utils/jumpinCatalog";

/**
 * One Standort and one of its scopes.
 * An office can have several scopes. `scopes` is the full list;
 * `scope` is the single one the booking UI already uses.
 */
export type ScopeOfficeRecord = {
  officeId: string;
  officeName: string;
  scopeId: string;
  scopeName: string;
};

type ScopeInput = { id?: unknown; shortName?: unknown } | null;

type OfficeInput = {
  id?: unknown;
  name?: unknown;
  scope?: ScopeInput;
  scopes?: readonly ScopeInput[] | null;
};

function text(value: unknown): string {
  if (typeof value === "number" && Number.isFinite(value)) {
    return String(value);
  }
  if (typeof value !== "string") {
    return "";
  }
  return value.trim();
}

export function scopeOfficeRecords(
  offices: readonly OfficeInput[]
): ScopeOfficeRecord[] {
  const records: ScopeOfficeRecord[] = [];
  for (const office of offices) {
    const officeId = text(office?.id);
    if (!officeId) {
      continue;
    }
    const officeName = text(office?.name);
    const scopes = listedScopes(office);
    if (scopes.length === 0) {
      records.push({
        officeId,
        officeName,
        scopeId: "",
        scopeName: officeName,
      });
      continue;
    }
    for (const scope of scopes) {
      records.push({
        officeId,
        officeName,
        scopeId: scope.id,
        scopeName: scope.name || officeName,
      });
    }
  }
  return records;
}

function listedScopes(office: OfficeInput): { id: string; name: string }[] {
  const fromList = (office.scopes ?? [])
    .map((scope) => ({
      id: text(scope?.id),
      name: text(scope?.shortName),
    }))
    .filter((scope) => scope.id !== "");
  if (fromList.length > 0) {
    return fromList;
  }
  const id = text(office.scope?.id);
  if (!id) {
    return [];
  }
  return [{ id, name: text(office.scope?.shortName) }];
}

function byName(a: JumpinChoice, b: JumpinChoice): number {
  return a.name.localeCompare(b.name, "de");
}

function choice(id: string, name: string): JumpinChoice {
  return { id, name, label: jumpinChoiceLabel(id, name) };
}

export function scopeChoices(
  records: readonly ScopeOfficeRecord[]
): JumpinChoice[] {
  const byId = new Map<string, JumpinChoice>();
  for (const record of records) {
    if (!record.scopeId || byId.has(record.scopeId)) {
      continue;
    }
    byId.set(record.scopeId, choice(record.scopeId, record.scopeName));
  }
  return [...byId.values()].sort(byName);
}

export function officeChoices(
  records: readonly ScopeOfficeRecord[]
): JumpinChoice[] {
  const byId = new Map<string, JumpinChoice>();
  for (const record of records) {
    if (!byId.has(record.officeId)) {
      byId.set(record.officeId, choice(record.officeId, record.officeName));
    }
  }
  return [...byId.values()].sort(byName);
}

export function recordsForScopeId(
  records: readonly ScopeOfficeRecord[],
  scopeId: string
): ScopeOfficeRecord[] {
  return records.filter((record) => record.scopeId === scopeId);
}

export function recordsForOfficeId(
  records: readonly ScopeOfficeRecord[],
  officeId: string
): ScopeOfficeRecord[] {
  return records.filter((record) => record.officeId === officeId);
}
