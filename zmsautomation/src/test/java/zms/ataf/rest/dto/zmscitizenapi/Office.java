package zms.ataf.rest.dto.zmscitizenapi;

import java.util.List;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;
import com.fasterxml.jackson.databind.annotation.JsonDeserialize;

import lombok.Data;

/**
 * Office model based on schema: zmsentities/schema/citizenapi/office.json
 */
@Data
@JsonIgnoreProperties(ignoreUnknown = true)
@JsonDeserialize(using = OfficeDeserializer.class)
public class Office {

    private Integer id;
    private String name;
    private Boolean showAlternativeLocations;
    private Address address;
    private List<String> displayNameAlternatives;
    private String organization;
    private String organizationUnit;
    private Integer slotTimeInMinutes;
    private Double version;
    private GeoCoordinates geo;
    private Integer parentId;
    private Object scope;
    private List<Integer> disabledByServices;
    private String slotsPerAppointment;

    @Data
    @JsonIgnoreProperties(ignoreUnknown = true)
    @JsonDeserialize(using = AddressDeserializer.class)
    public static class Address {
        @JsonProperty("house_number")
        private String houseNumber;

        private String city;

        @JsonProperty("postal_code")
        private String postalCode;

        private String street;
        private Boolean hint;
    }

    @Data
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class GeoCoordinates {
        private Double lat;
        private Double lon;
    }
}
