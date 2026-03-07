package zms.ataf.rest.dto.zmscitizenapi;

import java.util.List;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

/**
 * Response data for GET /available-appointments/ and GET /available-appointments-by-office/.
 * Contains appointment timestamps (Unix seconds) for the selected date, office, and service.
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class AvailableAppointmentsResponse {

    @JsonProperty("appointmentTimestamps")
    private List<Long> appointmentTimestamps;

    public List<Long> getAppointmentTimestamps() {
        return appointmentTimestamps;
    }

    public void setAppointmentTimestamps(List<Long> appointmentTimestamps) {
        this.appointmentTimestamps = appointmentTimestamps;
    }

    /**
     * Returns the first appointment timestamp for use in the reserve step.
     */
    public Long getFirstAppointmentTimestamp() {
        if (appointmentTimestamps == null || appointmentTimestamps.isEmpty()) {
            return null;
        }
        return appointmentTimestamps.get(0);
    }
}
