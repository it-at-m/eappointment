package zms.ataf.rest.dto.zmscitizenapi;

import java.util.List;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

/**
 * Request body for POST /reserve-appointment/.
 * timestamp: Unix seconds; officeId: int; serviceId: array; serviceCount: array (default [1]).
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class ReserveAppointmentRequest {

    @JsonProperty("timestamp")
    private Long timestamp;

    @JsonProperty("officeId")
    private Integer officeId;

    @JsonProperty("serviceId")
    private List<Integer> serviceId;

    @JsonProperty("serviceCount")
    private List<Integer> serviceCount;

    @JsonProperty("captchaToken")
    private String captchaToken;

    public Long getTimestamp() {
        return timestamp;
    }

    public void setTimestamp(Long timestamp) {
        this.timestamp = timestamp;
    }

    public Integer getOfficeId() {
        return officeId;
    }

    public void setOfficeId(Integer officeId) {
        this.officeId = officeId;
    }

    public List<Integer> getServiceId() {
        return serviceId;
    }

    public void setServiceId(List<Integer> serviceId) {
        this.serviceId = serviceId;
    }

    public List<Integer> getServiceCount() {
        return serviceCount;
    }

    public void setServiceCount(List<Integer> serviceCount) {
        this.serviceCount = serviceCount;
    }

    public String getCaptchaToken() {
        return captchaToken;
    }

    public void setCaptchaToken(String captchaToken) {
        this.captchaToken = captchaToken;
    }
}
