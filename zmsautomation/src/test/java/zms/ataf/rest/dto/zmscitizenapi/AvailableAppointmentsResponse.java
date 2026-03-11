package zms.ataf.rest.dto.zmscitizenapi;

import java.util.List;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonProperty;

/**
 * Response data for GET /available-appointments/ and GET /available-appointments-by-office/.
 * Can be either:
 * - plain: { "appointmentTimestamps": [ ... ] }
 * - grouped by office: { "offices": [ { "officeId": 10433958, "appointments": [ ... ] }, ... ] }
 *
 * Use {@link #getFirstAppointmentTimestamp()} to obtain a timestamp for the next step.
 */
@JsonIgnoreProperties(ignoreUnknown = true)
public class AvailableAppointmentsResponse {

    @JsonProperty("appointmentTimestamps")
    private List<Long> appointmentTimestamps;

    @JsonProperty("offices")
    private List<OfficeAppointments> offices;

    public List<Long> getAppointmentTimestamps() {
        return appointmentTimestamps;
    }

    public void setAppointmentTimestamps(List<Long> appointmentTimestamps) {
        this.appointmentTimestamps = appointmentTimestamps;
    }

    public List<OfficeAppointments> getOffices() {
        return offices;
    }

    public void setOffices(List<OfficeAppointments> offices) {
        this.offices = offices;
    }

    /**
     * Returns the first appointment timestamp for use in the reserve step.
     * Prefers the flat "appointmentTimestamps" array, but falls back to the first
     * entry in "offices[*].appointments" if needed.
     */
    public Long getFirstAppointmentTimestamp() {
        if (appointmentTimestamps != null && !appointmentTimestamps.isEmpty()) {
            return appointmentTimestamps.get(0);
        }
        if (offices != null && !offices.isEmpty()) {
            OfficeAppointments office = offices.get(0);
            if (office.getAppointments() != null && !office.getAppointments().isEmpty()) {
                return office.getAppointments().get(0);
            }
        }
        return null;
    }

    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class OfficeAppointments {
        @JsonProperty("officeId")
        private Integer officeId;

        @JsonProperty("appointments")
        private List<Long> appointments;

        public Integer getOfficeId() {
            return officeId;
        }

        public void setOfficeId(Integer officeId) {
            this.officeId = officeId;
        }

        public List<Long> getAppointments() {
            return appointments;
        }

        public void setAppointments(List<Long> appointments) {
            this.appointments = appointments;
        }
    }
}
