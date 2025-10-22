import type { Service } from "@/api/models/Service";

interface ServiceVariant extends Service {
  variant_id: number;
  parent_id: string | null;
}
