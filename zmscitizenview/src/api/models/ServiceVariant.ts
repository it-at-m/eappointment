import type { Service } from "@/api/models/Service";

export interface ServiceVariant extends Service {
  variant_id: number;
  parent_id: string | null;
}
