package zms.ataf.rest.dto.zmscitizenapi;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

import lombok.Data;

/**
 * Service model based on schema: zmsentities/schema/citizenapi/service.json
 */
@Data
@JsonIgnoreProperties(ignoreUnknown = true)
public class Service {

    private Integer id;
    private String name;
    private Integer maxQuantity;
    private Object combinable;

    @JsonProperty("public")
    private Boolean isPublic;

    @JsonProperty("parent_id")
    private Integer parentId;

    @JsonProperty("variant_id")
    private Integer variantId;
}
