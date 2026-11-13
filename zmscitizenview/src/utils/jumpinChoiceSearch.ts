/**
 * MucSelect searches through Choices, which keeps the first four hits in fuse
 * rank order. An exact id is often past that cutoff, so typing "1" never
 * shows service 1. Ranking that id first happens before the cutoff.
 */
import { promoteExactIdResults } from "@/utils/jumpinCatalog";

type RankedHit = { item: { label?: string }; rank: number };

type ChoiceSearcher = {
  search: (query: string) => RankedHit[];
};

const scheduled = new WeakSet<object>();
const patchedSearchers = new WeakSet<object>();
let installed = false;

function isChoicesInstance(
  value: unknown
): value is { _searcher?: ChoiceSearcher } {
  if (!value || typeof value !== "object") {
    return false;
  }
  const config = (
    value as {
      config?: {
        searchResultLimit?: unknown;
        classNames?: { inputCloned?: unknown };
      };
    }
  ).config;
  const inputCloned = config?.classNames?.inputCloned;
  return (
    config?.searchResultLimit === 4 &&
    Array.isArray(inputCloned) &&
    inputCloned.includes("choices__input--cloned")
  );
}

/** Re-rank one Choices search so an exact id is the first hit. */
export function patchChoicesSearcher(instance: {
  _searcher?: ChoiceSearcher;
}): void {
  const searcher = instance._searcher;
  if (!searcher || patchedSearchers.has(searcher)) {
    return;
  }
  patchedSearchers.add(searcher);
  const original = searcher.search;
  searcher.search = function (query: string) {
    return promoteExactIdResults(original.call(searcher, query), query);
  };
}

/**
 * Choices is constructed inside MucSelect and is not configurable from here.
 * Its methods are bound during construction, which is the one moment the
 * instance is visible. The search index itself is created just after that.
 */
export function installJumpinExactIdSearch(): void {
  if (installed) {
    return;
  }
  installed = true;
  const originalBind = Function.prototype.bind;
  Function.prototype.bind = function (
    this: (...args: unknown[]) => unknown,
    thisArg: unknown,
    ...boundArgs: unknown[]
  ) {
    const bound = originalBind.apply(this, [thisArg, ...boundArgs]);
    if (isChoicesInstance(thisArg) && !scheduled.has(thisArg)) {
      scheduled.add(thisArg);
      queueMicrotask(() => patchChoicesSearcher(thisArg));
    }
    return bound;
  } as typeof Function.prototype.bind;
}
