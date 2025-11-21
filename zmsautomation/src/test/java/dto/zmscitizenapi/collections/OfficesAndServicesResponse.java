package dto.zmscitizenapi.collections;

import java.util.List;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

import dto.zmscitizenapi.Office;
import dto.zmscitizenapi.OfficeServiceRelation;
import dto.zmscitizenapi.Service;

/**
 * Response model for the /offices-and-services/ endpoint.
 * Based on schema: zmsentities/schema/citizenapi/collections/officeServiceAndRelationList.json
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class OfficesAndServicesResponse {

    @JsonProperty("offices")
    private List<Office> offices;

    @JsonProperty("services")
    private List<Service> services;

    @JsonProperty("relations")
    private List<OfficeServiceRelation> relations;

    public List<Office> getOffices() {
        return offices;
    }

    public void setOffices(List<Office> offices) {
        this.offices = offices;
    }

    public List<Service> getServices() {
        return services;
    }

    public void setServices(List<Service> services) {
        this.services = services;
    }

    public List<OfficeServiceRelation> getRelations() {
        return relations;
    }

    public void setRelations(List<OfficeServiceRelation> relations) {
        this.relations = relations;
    }
}
