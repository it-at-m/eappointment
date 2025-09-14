// API types
export interface CalldisplayConfig {
  collections: {
    scopelist?: number[];
    clusterlist?: number[];
  };
  template?: string;
  display?: string;
  queue?: {
    status?: string[];
  };
}

export interface CalldisplayEntity {
  scopes: Scope[];
  clusters: Cluster[];
  queueList: QueueItem[];
  config: Config;
}

export interface QueueList {
  id: string;
  queueNumber: string;
  number: string;
  status: string;
  destination: string;
  estimatedWaitTime?: number;
  scopeId: number;
  createdAt: string;
  updatedAt: string;
}

export interface Scope {
  id: number;
  name: string;
  shortName: string;
  description?: string;
  status: string;
  preferences?: {
    queue?: {
      callDisplayText?: string;
    };
  };
}

export interface Cluster {
  id: number;
  name: string;
  description?: string;
  status: string;
}

export interface QueueItem {
  id: string;
  queueNumber: string;
  status: string;
  estimatedWaitTime?: number;
  scopeId: number;
  createdAt: string;
  updatedAt: string;
}

export interface Config {
  template: string;
  display: string;
  refreshInterval: number;
  organisation?: {
    name: string;
    id: number;
  };
  preferences?: {
    ticketPrinterProtectionEnabled: boolean;
  };
}

/**
 * API client for calldisplay endpoints
 */
export class CalldisplayAPI {
  private baseUrl: string;

  constructor(baseUrl?: string) {
    this.baseUrl =
      baseUrl ||
      import.meta.env.VITE_VUE_APP_API_URL ||
      "http://localhost:8084";
    console.log("CalldisplayAPI initialized with baseUrl:", this.baseUrl);
  }

  /**
   * Get calldisplay configuration
   */
  async getCalldisplay(config: CalldisplayConfig): Promise<CalldisplayEntity> {
    const apiPayload: any = {
      serverTime: Math.floor(Date.now() / 1000),
      organisation: { name: "" },
    };

    if (
      config.collections.scopelist &&
      config.collections.scopelist.length > 0
    ) {
      // Convert scope IDs to scope objects like the old PHP code does
      apiPayload.scopes = config.collections.scopelist.map((id) => ({
        id: id.toString(),
        source: "dldb",
        provider: { id: 0, name: "", source: "dldb" },
        shortName: "",
      }));
    }

    if (
      config.collections.clusterlist &&
      config.collections.clusterlist.length > 0
    ) {
      // Convert cluster IDs to cluster objects
      apiPayload.clusters = config.collections.clusterlist.map((id) => ({
        id: id.toString(),
        source: "dldb",
        provider: { id: 0, name: "", source: "dldb" },
        shortName: "",
      }));
    }

    const url = `${this.baseUrl}/buergeransicht/api/calldisplay/`;
    console.log("Making API request to:", url);
    console.log("Base URL:", this.baseUrl);
    console.log("API Payload:", JSON.stringify(apiPayload, null, 2));

    const response = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(apiPayload),
    });

    const data = await response.json();
    console.log("Calldisplay API response:", JSON.stringify(data, null, 2));

    if (!response.ok || (data.meta && data.meta.error)) {
      throw new Error(
        `Failed to fetch calldisplay: ${data.meta?.message || response.statusText}`,
      );
    }

    return data.data || data;
  }

  /**
   * Get queue data
   */
  async getQueue(
    config: CalldisplayConfig,
    statusList: string[] = ["waiting", "called", "confirmed"],
  ): Promise<QueueList[]> {
    const apiPayload: any = {
      serverTime: Math.floor(Date.now() / 1000),
      organisation: { name: "" },
    };

    if (
      config.collections.scopelist &&
      config.collections.scopelist.length > 0
    ) {
      // Convert scope IDs to scope objects like the old PHP code does
      apiPayload.scopes = config.collections.scopelist.map((id) => ({
        id: id.toString(),
        source: "dldb",
        provider: { id: 0, name: "", source: "dldb" },
        shortName: "",
      }));
    }

    if (
      config.collections.clusterlist &&
      config.collections.clusterlist.length > 0
    ) {
      // Convert cluster IDs to cluster objects
      apiPayload.clusters = config.collections.clusterlist.map((id) => ({
        id: id.toString(),
        source: "dldb",
        provider: { id: 0, name: "", source: "dldb" },
        shortName: "",
      }));
    }

    const url = `${this.baseUrl}/buergeransicht/api/calldisplay/queue/?statusList=${statusList.join(",")}`;
    console.log("Making queue API request to:", url);
    console.log("Queue API Payload:", JSON.stringify(apiPayload, null, 2));

    const response = await fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(apiPayload),
    });

    const data = await response.json();

    if (!response.ok || (data.meta && data.meta.error)) {
      throw new Error(
        `Failed to fetch queue: ${data.meta?.message || response.statusText}`,
      );
    }

    return data.data || [];
  }

  /**
   * Get scope information
   */
  async getScope(scopeId: number): Promise<Scope> {
    const response = await fetch(
      `${this.baseUrl}/buergeransicht/api/scope/${scopeId}/`,
    );

    if (!response.ok) {
      throw new Error(`Failed to fetch scope: ${response.statusText}`);
    }

    const data = await response.json();
    return data.data || data;
  }

  /**
   * Get system status
   */
  async getStatus(): Promise<any> {
    const response = await fetch(`${this.baseUrl}/buergeransicht/api/status/`);

    if (!response.ok) {
      throw new Error(`Failed to fetch status: ${response.statusText}`);
    }

    return response.json();
  }

  /**
   * Get configuration
   */
  async getConfig(): Promise<any> {
    const response = await fetch(`${this.baseUrl}/buergeransicht/api/config/`);

    if (!response.ok) {
      throw new Error(`Failed to fetch config: ${response.statusText}`);
    }

    return response.json();
  }
}
