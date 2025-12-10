package dto.zmscitizenapi;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

/**
 * Office-Service relation model based on schema: zmsentities/schema/citizenapi/officeServiceRelation.json
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class OfficeServiceRelation {
    @JsonProperty("officeId")
    private Integer officeId;

    @JsonProperty("serviceId")
    private Integer serviceId;

    @JsonProperty("slots")
    private Integer slots; // Required field

    public Integer getOfficeId() {
        return officeId;
    }

    public void setOfficeId(Integer officeId) {
        this.officeId = officeId;
    }

    public Integer getServiceId() {
        return serviceId;
    }

    public void setServiceId(Integer serviceId) {
        this.serviceId = serviceId;
    }

    public Integer getSlots() {
        return slots;
    }

    public void setSlots(Integer slots) {
        this.slots = slots;
    }
}
