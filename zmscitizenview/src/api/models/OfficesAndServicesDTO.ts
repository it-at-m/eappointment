import { Office } from "@/api/models/Office";
import { Relation } from "@/api/models/Relation";
import { Service } from "@/api/models/Service";

/**
 *
 * @export
 * @interface OfficesAndServicesDTO
 */
export interface OfficesAndServicesDTO {
  /**
   *
   * @type {Array<Office>}
   * @memberof OfficesAndServicesDTO
   */
  offices: Array<Office>;
  /**
   *
   * @type {Array<Service>}
   * @memberof OfficesAndServicesDTO
   */
  services: Array<Service>;
  /**
   *
   * @type {Array<Relation>}
   * @memberof OfficesAndServicesDTO
   */
  relations: Array<Relation>;
}
