package dto.zmscitizenapi;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

/**
 * Service model based on schema: zmsentities/schema/citizenapi/service.json
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class Service {
    @JsonProperty("id")
    private Integer id;

    @JsonProperty("name")
    private String name;

    @JsonProperty("maxQuantity")
    private Integer maxQuantity;

    @JsonProperty("combinable")
    private Object combinable; // Complex nested structure - using Object for now

    @JsonProperty("public")
    private Boolean isPublic;

    @JsonProperty("parent_id")
    private Integer parentId;

    @JsonProperty("variant_id")
    private Integer variantId;

    public Integer getId() {
        return id;
    }

    public void setId(Integer id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public Integer getMaxQuantity() {
        return maxQuantity;
    }

    public void setMaxQuantity(Integer maxQuantity) {
        this.maxQuantity = maxQuantity;
    }

    public Object getCombinable() {
        return combinable;
    }

    public void setCombinable(Object combinable) {
        this.combinable = combinable;
    }

    public Boolean getIsPublic() {
        return isPublic;
    }

    public void setIsPublic(Boolean isPublic) {
        this.isPublic = isPublic;
    }

    public Integer getParentId() {
        return parentId;
    }

    public void setParentId(Integer parentId) {
        this.parentId = parentId;
    }

    public Integer getVariantId() {
        return variantId;
    }

    public void setVariantId(Integer variantId) {
        this.variantId = variantId;
    }
}
