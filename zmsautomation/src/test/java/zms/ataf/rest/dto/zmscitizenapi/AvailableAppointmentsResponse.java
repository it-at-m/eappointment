package zms.ataf.rest.dto.zmscitizenapi;

import java.util.ArrayList;
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
     * Minimum lead so confirm/rebook/cancel still see a future appointment under shard load.
     * A 60s buffer let ZMSKVR-1576 book a near-now slot that was already past when canceling
     * the rebooking source ({@code appointmentCanNotBeCanceled}).
     */
    private static final long MIN_LEAD_SECONDS = 20L * 60L;

    /**
     * Returns the first appointment timestamp that is in the future (avoids "Ihr Termin liegt in der Vergangenheit").
     * Timestamps are seconds since epoch. Uses a {@link #MIN_LEAD_SECONDS} buffer past now.
     * Falls back to {@link #getFirstAppointmentTimestamp()} if no future slot is found.
     */
    public Long getFirstFutureAppointmentTimestamp() {
        long nowSeconds = System.currentTimeMillis() / 1000;
        long minFuture = nowSeconds + MIN_LEAD_SECONDS;
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

    /** Future slots in calendar order, so a taken one can be skipped for the next. */
    public List<Long> futureAppointmentTimestamps() {
        long minFuture = System.currentTimeMillis() / 1000 + MIN_LEAD_SECONDS;
        List<Long> timestamps = new ArrayList<>();
        if (appointmentTimestamps != null) {
            for (Long ts : appointmentTimestamps) {
                if (ts != null && ts > minFuture) {
                    timestamps.add(ts);
                }
            }
        }
        if (offices != null) {
            for (OfficeAppointments office : offices) {
                if (office.getAppointments() == null) {
                    continue;
                }
                for (Long ts : office.getAppointments()) {
                    if (ts != null && ts > minFuture) {
                        timestamps.add(ts);
                    }
                }
            }
        }
        if (timestamps.isEmpty()) {
            Long fallback = getFirstAppointmentTimestamp();
            if (fallback != null) {
                timestamps.add(fallback);
            }
        }
        return timestamps;
    }

    @Data
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class OfficeAppointments {
        private Integer officeId;
        private List<Long> appointments;
    }
}
