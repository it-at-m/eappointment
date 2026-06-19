package zms.ataf.rest.dto.zmscitizenapi.collections;

import java.util.List;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import lombok.Data;
import zms.ataf.rest.dto.zmscitizenapi.Office;
import zms.ataf.rest.dto.zmscitizenapi.OfficeServiceRelation;
import zms.ataf.rest.dto.zmscitizenapi.Service;

/**
 * Response model for the /offices-and-services/ endpoint.
 * Based on schema: zmsentities/schema/citizenapi/collections/officeServiceAndRelationList.json
 */
@Data
@JsonIgnoreProperties(ignoreUnknown = true)
public class OfficesAndServicesResponse {

    private List<Office> offices;
    private List<Service> services;
    private List<OfficeServiceRelation> relations;
}
