import { Combinable } from "@/api/models/Combinable";
import { Service, VariantOverwrite } from "@/api/models/Service";
import { OfficeImpl } from "@/types/OfficeImpl";
import { SubService } from "@/types/SubService";

export class ServiceImpl implements Service {
  id: string;

  name: string;

  maxQuantity: number;

  combinable?: Combinable;

  providers?: OfficeImpl[];

  subServices?: SubService[];

  /** Click order for reserve payload; display list stays stable (ZMSKVR-1088). */
  subServiceSelectionOrder?: string[];

  count?: number;

  parentId: string | number | null;
  rootParentId?: string | number | null;
  variantId: number | null;
  variantOverwrite?: VariantOverwrite;

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
    showOnStartPage: boolean | undefined,
    variantOverwrite?: VariantOverwrite
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
    this.variantOverwrite = variantOverwrite;
  }
}
