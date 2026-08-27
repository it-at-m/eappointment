export const toFeatureAnchorId = (rel) =>
  `feature-${rel
    .replace(/\.feature$/i, "")
    .replace(/[^A-Za-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "")
    .toLowerCase()}`;

const uniquePreserveOrder = (tags) => {
  const seen = new Set();
  const out = [];
  for (const tag of tags) {
    const key = tag.toLowerCase();
    if (seen.has(key)) {
      continue;
    }
    seen.add(key);
    out.push(tag);
  }
  return out;
};

export const sortFeatureTags = (tags) => {
  const unique = uniquePreserveOrder(tags);
  unique.sort((a, b) => {
    const aTicket = /^@(?:ZMSKVR|ZMS)-\d+$/i.test(a);
    const bTicket = /^@(?:ZMSKVR|ZMS)-\d+$/i.test(b);
    if (aTicket !== bTicket) {
      return aTicket ? -1 : 1;
    }
    return a.localeCompare(b);
  });
  return unique;
};

export const parseFeatureMeta = (raw) => {
  const featureTags = [];
  const allTags = [];
  let title = "";
  let firstScenario = "";
  let scenarioCount = 0;
  let pendingTags = [];
  let seenFeature = false;

  for (const line of String(raw || "").split(/\r?\n/)) {
    const trimmed = line.trim();
    if (!trimmed || trimmed.startsWith("#")) {
      continue;
    }
    const tagCandidate = trimmed.split("#", 1)[0].trim();
    if (tagCandidate && /^@(?:\S+)(?:\s+@\S+)*$/.test(tagCandidate)) {
      const tags = tagCandidate
        .split(/\s+/)
        .filter((part) => part.startsWith("@"));
      pendingTags.push(...tags);
      allTags.push(...tags);
      continue;
    }
    const featureMatch = trimmed.match(/^(?:Feature|Funktionalität):\s*(.+)$/);
    if (featureMatch) {
      title = featureMatch[1].trim();
      if (!seenFeature) {
        featureTags.push(...pendingTags);
        seenFeature = true;
      }
      pendingTags = [];
      continue;
    }
    const scenarioMatch = trimmed.match(
      /^(?:Scenario Outline|Scenario Template|Szenariogrundriss|Scenario|Szenario):\s*(.*)$/
    );
    if (scenarioMatch) {
      scenarioCount += 1;
      if (!firstScenario) {
        firstScenario = scenarioMatch[1].trim();
      }
      pendingTags = [];
    }
  }

  const genericTitle = !title || /^default$/i.test(title);

  return {
    title: genericTitle ? firstScenario : title,
    tags: sortFeatureTags(featureTags.length ? featureTags : allTags),
    scenarioCount,
  };
};
