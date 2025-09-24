import type { CalldisplayConfig } from "../api/CalldisplayAPI";

/**
 * Parse URL parameters for calldisplay configuration
 */
export function parseCalldisplayUrlParams(): CalldisplayConfig {
  const urlParams = new URLSearchParams(window.location.search);

  const config: CalldisplayConfig = {
    collections: {},
    template: "default_counter",
  };

  // Parse collections
  const scopelist = urlParams.get("collections[scopelist]");
  const clusterlist = urlParams.get("collections[clusterlist]");

  if (scopelist) {
    config.collections.scopelist = scopelist
      .split(",")
      .map((id) => parseInt(id.trim()))
      .filter((id) => !isNaN(id));
  }

  if (clusterlist) {
    config.collections.clusterlist = clusterlist
      .split(",")
      .map((id) => parseInt(id.trim()))
      .filter((id) => !isNaN(id));
  }

  // Parse template
  const template = urlParams.get("template");
  if (template) {
    config.template = template;
  }

  // Parse display number
  const display = urlParams.get("display");
  if (display) {
    const displayNum = parseInt(display);
    if (!isNaN(displayNum)) {
      config.display = displayNum;
    }
  }

  // Parse queue status
  const queueStatus = urlParams.get("queue[status]");
  if (queueStatus) {
    config.queue = {
      status: queueStatus.split(",").map((s) => s.trim()),
    };
  }

  return config;
}

/**
 * Build URL with calldisplay parameters
 */
export function buildCalldisplayUrl(
  baseUrl: string,
  config: CalldisplayConfig,
): string {
  const params = new URLSearchParams();

  if (config.collections.scopelist && config.collections.scopelist.length > 0) {
    params.set(
      "collections[scopelist]",
      config.collections.scopelist.join(","),
    );
  }

  if (
    config.collections.clusterlist &&
    config.collections.clusterlist.length > 0
  ) {
    params.set(
      "collections[clusterlist]",
      config.collections.clusterlist.join(","),
    );
  }

  if (config.template) {
    params.set("template", config.template);
  }

  if (config.display) {
    params.set("display", config.display.toString());
  }

  if (config.queue?.status && config.queue.status.length > 0) {
    params.set("queue[status]", config.queue.status.join(","));
  }

  const queryString = params.toString();
  return queryString ? `${baseUrl}?${queryString}` : baseUrl;
}

/**
 * Get display number from URL
 */
export function getDisplayNumber(): number | null {
  const urlParams = new URLSearchParams(window.location.search);
  const display = urlParams.get("display");

  if (display) {
    const displayNum = parseInt(display);
    return isNaN(displayNum) ? null : displayNum;
  }

  return null;
}

/**
 * Check if URL has valid calldisplay parameters
 */
export function hasValidCalldisplayParams(): boolean {
  const config = parseCalldisplayUrlParams();

  return !!(
    (config.collections.scopelist && config.collections.scopelist.length > 0) ||
    (config.collections.clusterlist &&
      config.collections.clusterlist.length > 0)
  );
}
