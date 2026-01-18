import { Combinable } from "@/api/models/Combinable";
import { Service } from "@/api/models/Service";
import { OfficeImpl } from "@/types/OfficeImpl";
import { SubService } from "@/types/SubService";

export class ServiceImpl implements Service {
  id: string;

  name: string;

  maxQuantity: number;

  combinable?: Combinable;

  providers?: OfficeImpl[];

  subServices?: SubService[];

  count?: number;

  parentId: string | number | null;
  variantId: number | null;

  showOnStartPage?: boolean;

  constructor(
    id: string,
    name: string,
    maxQuantity: number,
    combinable: Combinable | undefined,
    providers: OfficeImpl[] | undefined,
    subServices: SubService[] | undefined,
    count: number | undefined,
    parentId: string | number | null,
    variantId: number | null,
    showOnStartPage: boolean | undefined
  ) {
    this.id = id;
    this.name = name;
    this.maxQuantity = maxQuantity;
    this.combinable = combinable;
    this.providers = providers;
    this.subServices = subServices;
    this.count = count;
    this.parentId = parentId;
    this.variantId = variantId;
    this.showOnStartPage = showOnStartPage;
  }
}
