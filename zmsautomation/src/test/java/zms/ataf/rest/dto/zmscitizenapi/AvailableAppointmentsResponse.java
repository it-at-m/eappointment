package zms.ataf.rest.dto.zmscitizenapi;

import java.util.List;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import lombok.Data;

/**
 * Appointment slots for a selected day and office, extracted from calendar availability.
 * Can be either:
 * - plain: { "appointmentTimestamps": [ ... ] }
 * - grouped by office: { "offices": [ { "officeId": 10433958, "appointments": [ ... ] }, ... ] }
 *
 * Use {@link #getFirstAppointmentTimestamp()} to obtain a timestamp for the next step.
 */
@Data
@JsonIgnoreProperties(ignoreUnknown = true)
public class AvailableAppointmentsResponse {

    private List<Long> appointmentTimestamps;
    private List<OfficeAppointments> offices;

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

    /**
     * Returns the first appointment timestamp that is in the future (avoids "Ihr Termin liegt in der Vergangenheit").
     * Timestamps are seconds since epoch. Uses a 60s buffer past now.
     * Falls back to {@link #getFirstAppointmentTimestamp()} if no future slot is found.
     */
    public Long getFirstFutureAppointmentTimestamp() {
        long nowSeconds = System.currentTimeMillis() / 1000;
        long minFuture = nowSeconds + 60;
        if (appointmentTimestamps != null) {
            for (Long ts : appointmentTimestamps) {
                if (ts != null && ts > minFuture) {
                    return ts;
                }
            }
        }
        if (offices != null) {
            for (OfficeAppointments office : offices) {
                if (office.getAppointments() != null) {
                    for (Long ts : office.getAppointments()) {
                        if (ts != null && ts > minFuture) {
                            return ts;
                        }
                    }
                }
            }
        }
        return getFirstAppointmentTimestamp();
    }

    @Data
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class OfficeAppointments {
        private Integer officeId;
        private List<Long> appointments;
    }
}
