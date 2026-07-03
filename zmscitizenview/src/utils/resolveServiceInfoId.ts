import { Service } from "@/api/models/Service";

/**
 * Resolves the service info id used for the "service info" jump-in link.
 *
 * Variants can be nested (e.g. a "Telefon" variant points to its base variant
 * which in turn points to the actual service). Walking up the parent chain
 * ensures the link always targets the root service instead of just the
 * immediate parent.
 */
export const resolveServiceInfoId = (
  service:
    | { parentId?: string | number | null; id?: string | number }
    | undefined
    | null,
  services: Service[] = []
): string => {
  if (!service) return "";

  let current: {
    parentId?: string | number | null;
    id?: string | number;
  } = service;
  const visited = new Set<string>();

  while (current.parentId != null) {
    const parentIdStr = String(current.parentId);
    if (visited.has(parentIdStr)) break;
    visited.add(parentIdStr);

    const parent = services.find((s) => String(s.id) === parentIdStr);
    if (!parent) break;
    current = parent;
  }

  return String(current.parentId ?? current.id ?? "");
};
