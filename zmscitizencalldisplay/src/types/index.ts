// Import API types
import type {
  CalldisplayConfig,
  CalldisplayEntity,
  Scope,
  Cluster,
  QueueItem,
  QueueList,
  Config,
} from "../api/CalldisplayAPI";

// Re-export API types for convenience
export type {
  CalldisplayConfig,
  CalldisplayEntity,
  Scope,
  Cluster,
  QueueItem,
  QueueList,
  Config,
};

// Additional types specific to the UI
export interface DisplaySettings {
  template: string;
  displayNumber?: number;
  autoRefresh: boolean;
  refreshInterval: number;
}

export interface WaitingTimeInfo {
  waitingClients: number;
  waitingTime: number;
  waitingTimeOptimistic: number;
}

export interface CalldisplayState {
  loading: boolean;
  error: string | null;
  calldisplay: CalldisplayEntity | null;
  queue: QueueList | null;
  scope: Scope | null;
  config: Config | null;
  displayNumber: number | null;
}
