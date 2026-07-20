package zms.ataf.rest.dto.zmscitizenapi;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import lombok.Data;

/**
 * Office-Service relation model based on schema: zmsentities/schema/citizenapi/officeServiceRelation.json
 */
@Data
@JsonIgnoreProperties(ignoreUnknown = true)
public class OfficeServiceRelation {

    private Integer officeId;
    private Integer serviceId;
    private Integer slots;
}
