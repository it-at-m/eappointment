/**
 * Utility functions for handling infoForAllAppointments functionality
 */

/**
 * Cleans provider names by removing dashes, commas, numbers, and extra whitespace
 */
export const cleanProviderName = (name: string): string => {
  return name
    .replace(/[-–—]/g, " ") // Replace various types of dashes with spaces
    .replace(/[,;]/g, " ") // Replace commas and semicolons with spaces
    .replace(/\d+/g, " ") // Replace numbers with spaces
    .replace(/\s+/g, " ") // Replace multiple spaces with single space
    .trim(); // Remove leading/trailing whitespace
};

/**
 * Optimizes provider names by removing common prefixes when multiple providers share the same info
 */
export const optimizeProviderNames = (providerNames: string[]): string => {
  if (providerNames.length <= 1) {
    return cleanProviderName(providerNames[0] || "");
  }

  // Clean all provider names first
  const cleanedNames = providerNames.map(cleanProviderName);

  // Find the longest common prefix
  const first = cleanedNames[0];
  let commonPrefix = "";

  for (let i = 0; i < first.length; i++) {
    const char = first[i];
    if (cleanedNames.every((name) => name[i] === char)) {
      commonPrefix += char;
    } else {
      break;
    }
  }

  // If we found a meaningful common prefix (at least 3 characters and ends with a space)
  if (commonPrefix.length >= 3 && commonPrefix.endsWith(" ")) {
    const prefix = commonPrefix.trim();
    const suffixes = cleanedNames.map((name) =>
      name.substring(commonPrefix.length)
    );
    return `${prefix} ${suffixes.join(", ")}`;
  }

  // If no meaningful common prefix, return as is
  return cleanedNames.join(", ");
};

/**
 * Interface for provider info data
 */
export interface ProviderInfo {
  id?: string | number;
  name?: string;
  scope?: {
    infoForAllAppointments?: string | null;
  };
}

/**
 * Generates HTML for availability info based on selected providers
 */
export const generateAvailabilityInfoHtml = (
  selectedProviders: { [id: string]: boolean },
  selectableProviders: ProviderInfo[] | undefined,
  selectedProvider: ProviderInfo | undefined,
  sanitizeHtml: (html: string) => string
): string => {
  // Get all selected providers
  const selectedProviderIds = Object.keys(selectedProviders).filter(
    (id) => selectedProviders[id]
  );

  // If no providers are selected but we have a selectedProvider, use that
  if (selectedProviderIds.length === 0 && selectedProvider) {
    const info = selectedProvider.scope?.infoForAllAppointments || "";
    return info && info.trim() ? sanitizeHtml(info.trim()) : "";
  }

  if (selectedProviderIds.length === 0) {
    return "";
  }

  // Get all selected providers with their info
  const selectedProvidersWithInfo = selectedProviderIds
    .map((id) => {
      const provider = selectableProviders?.find(
        (p) => p.id?.toString() === id
      );
      return {
        id: provider?.id,
        name: provider?.name,
        info: provider?.scope?.infoForAllAppointments || "",
      };
    })
    .filter((provider) => provider.info.trim() !== "");

  if (selectedProvidersWithInfo.length === 0) {
    return "";
  }

  // If all providers have the same info, show it without location names
  const uniqueInfos = [
    ...new Set(selectedProvidersWithInfo.map((p) => p.info.trim())),
  ];
  if (uniqueInfos.length === 1) {
    return sanitizeHtml(uniqueInfos[0]);
  }

  // If providers have different info, group them by info and show with provider names
  const infoGroups: { [info: string]: string[] } = {};

  selectedProvidersWithInfo.forEach((provider) => {
    const info = provider.info.trim();
    if (!infoGroups[info]) {
      infoGroups[info] = [];
    }
    if (provider.name) {
      infoGroups[info].push(provider.name);
    }
  });

  // Generate HTML with provider names and info
  let html = "";
  let isFirst = true;
  Object.entries(infoGroups).forEach(([info, providerNames]) => {
    if (providerNames.length === 1) {
      const cleanedName = cleanProviderName(providerNames[0]);
      html += `<h3${isFirst ? ' class="first-provider"' : ""}>${sanitizeHtml(cleanedName)}</h3>`;
    } else {
      const optimizedNames = optimizeProviderNames(providerNames);
      html += `<h3${isFirst ? ' class="first-provider"' : ""}>${sanitizeHtml(optimizedNames)}</h3>`;
    }
    html += `<div>${sanitizeHtml(info)}</div>`;
    isFirst = false;
  });
  return sanitizeHtml(html);
};
