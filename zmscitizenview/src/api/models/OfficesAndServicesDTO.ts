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
  offices: Office[];
  /**
   *
   * @type {Array<Service>}
   * @memberof OfficesAndServicesDTO
   */
  services: Service[];
  /**
   *
   * @type {Array<Relation>}
   * @memberof OfficesAndServicesDTO
   */
  relations: Relation[];
}
