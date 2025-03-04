import { Service } from "@/api/models/Service";
import { OfficeImpl } from "@/types/OfficeImpl";
import { SubService } from "@/types/SubService";

export class ServiceImpl implements Service {
  id: string;

  name: string;

  maxQuantity: number;

  combinable?: string[][];

  providers?: OfficeImpl[];

  subServices?: SubService[];

  count?: number;

  constructor(
    id: string,
    name: string,
    maxQuantity: number,
    combinable: string[][] | undefined,
    providers: OfficeImpl[] | undefined,
    subServices: SubService[] | undefined,
    count: number | undefined
  ) {
    this.id = id;
    this.name = name;
    this.maxQuantity = maxQuantity;
    this.combinable = combinable;
    this.providers = providers;
    this.subServices = subServices;
    this.count = count;
  }
}
