import { OfficeImpl } from "@/types/OfficeImpl";

export class SubService {
  id: string;

  name: string;

  maxQuantity: number;

  providers: Array<OfficeImpl>;

  count: number;

  constructor(
    id: string,
    name: string,
    maxQuantity: number,
    providers: Array<OfficeImpl>,
    count: number
  ) {
    this.id = id;
    this.name = name;
    this.maxQuantity = maxQuantity;
    this.providers = providers;
    this.count = count;
  }
}
