import {Office} from "@/api/models/Office";
import {Service} from "@/api/models/Service";
import {Relation} from "@/api/models/Relation";

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
