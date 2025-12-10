package dto.zmscitizenapi;

import java.util.List;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

/**
 * Office model based on schema: zmsentities/schema/citizenapi/office.json
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class Office {
    @JsonProperty("id")
    private Integer id;

    @JsonProperty("name")
    private String name;

    @JsonProperty("showAlternativeLocations")
    private Boolean showAlternativeLocations;

    @JsonProperty("address")
    private Address address;

    @JsonProperty("displayNameAlternatives")
    private List<String> displayNameAlternatives;

    @JsonProperty("organization")
    private String organization;

    @JsonProperty("organizationUnit")
    private String organizationUnit;

    @JsonProperty("slotTimeInMinutes")
    private Integer slotTimeInMinutes;

    @JsonProperty("version")
    private Double version;

    @JsonProperty("geo")
    private GeoCoordinates geo;

    @JsonProperty("parentId")
    private Integer parentId;

    @JsonProperty("scope")
    private Object scope; // Complex nested structure - using Object for now

    @JsonProperty("disabledByServices")
    private List<Integer> disabledByServices;

    @JsonProperty("maxSlotsPerAppointment")
    private String maxSlotsPerAppointment;

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

    public Boolean getShowAlternativeLocations() {
        return showAlternativeLocations;
    }

    public void setShowAlternativeLocations(Boolean showAlternativeLocations) {
        this.showAlternativeLocations = showAlternativeLocations;
    }

    public Address getAddress() {
        return address;
    }

    public void setAddress(Address address) {
        this.address = address;
    }

    public List<String> getDisplayNameAlternatives() {
        return displayNameAlternatives;
    }

    public void setDisplayNameAlternatives(List<String> displayNameAlternatives) {
        this.displayNameAlternatives = displayNameAlternatives;
    }

    public String getOrganization() {
        return organization;
    }

    public void setOrganization(String organization) {
        this.organization = organization;
    }

    public String getOrganizationUnit() {
        return organizationUnit;
    }

    public void setOrganizationUnit(String organizationUnit) {
        this.organizationUnit = organizationUnit;
    }

    public Integer getSlotTimeInMinutes() {
        return slotTimeInMinutes;
    }

    public void setSlotTimeInMinutes(Integer slotTimeInMinutes) {
        this.slotTimeInMinutes = slotTimeInMinutes;
    }

    public Double getVersion() {
        return version;
    }

    public void setVersion(Double version) {
        this.version = version;
    }

    public GeoCoordinates getGeo() {
        return geo;
    }

    public void setGeo(GeoCoordinates geo) {
        this.geo = geo;
    }

    public Integer getParentId() {
        return parentId;
    }

    public void setParentId(Integer parentId) {
        this.parentId = parentId;
    }

    public Object getScope() {
        return scope;
    }

    public void setScope(Object scope) {
        this.scope = scope;
    }

    public List<Integer> getDisabledByServices() {
        return disabledByServices;
    }

    public void setDisabledByServices(List<Integer> disabledByServices) {
        this.disabledByServices = disabledByServices;
    }

    public String getMaxSlotsPerAppointment() {
        return maxSlotsPerAppointment;
    }

    public void setMaxSlotsPerAppointment(String maxSlotsPerAppointment) {
        this.maxSlotsPerAppointment = maxSlotsPerAppointment;
    }

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class Address {
        @JsonProperty("house_number")
        private String houseNumber;

        @JsonProperty("city")
        private String city;

        @JsonProperty("postal_code")
        private String postalCode;

        @JsonProperty("street")
        private String street;

        @JsonProperty("hint")
        private Boolean hint;

        public String getHouseNumber() {
            return houseNumber;
        }

        public void setHouseNumber(String houseNumber) {
            this.houseNumber = houseNumber;
        }

        public String getCity() {
            return city;
        }

        public void setCity(String city) {
            this.city = city;
        }

        public String getPostalCode() {
            return postalCode;
        }

        public void setPostalCode(String postalCode) {
            this.postalCode = postalCode;
        }

        public String getStreet() {
            return street;
        }

        public void setStreet(String street) {
            this.street = street;
        }

        public Boolean getHint() {
            return hint;
        }

        public void setHint(Boolean hint) {
            this.hint = hint;
        }
    }

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class GeoCoordinates {
        @JsonProperty("lat")
        private Double lat;

        @JsonProperty("lon")
        private Double lon;

        public Double getLat() {
            return lat;
        }

        public void setLat(Double lat) {
            this.lat = lat;
        }

        public Double getLon() {
            return lon;
        }

        public void setLon(Double lon) {
            this.lon = lon;
        }
    }
}
