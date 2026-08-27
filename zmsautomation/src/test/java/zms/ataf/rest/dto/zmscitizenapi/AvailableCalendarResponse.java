package zms.ataf.rest.dto.zmscitizenapi;

import java.util.ArrayList;
import java.util.List;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import lombok.Data;

/**
 * Response data for GET /available-calendar/.
 */
@Data
@JsonIgnoreProperties(ignoreUnknown = true)
public class AvailableCalendarResponse {

    private String startDate;
    private String endDate;
    private List<CalendarDay> availableDays;

    public String getFirstAvailableDay() {
        if (availableDays == null || availableDays.isEmpty()) {
            return null;
        }
        // Prefer a day that already has free-slot appointments in the combined response.
        for (CalendarDay day : availableDays) {
            if (day == null || day.getDate() == null || day.getOffices() == null) {
                continue;
            }
            for (OfficeSlot office : day.getOffices()) {
                if (office != null && office.getAppointments() != null && !office.getAppointments().isEmpty()) {
                    return day.getDate();
                }
            }
        }
        CalendarDay first = availableDays.get(0);
        return first != null ? first.getDate() : null;
    }

    public AvailableAppointmentsResponse getAppointmentsForDayAndOffice(String date, int officeId) {
        AvailableAppointmentsResponse response = new AvailableAppointmentsResponse();
        List<AvailableAppointmentsResponse.OfficeAppointments> matchingOffices = new ArrayList<>();

        if (availableDays != null) {
            for (CalendarDay day : availableDays) {
                if (day == null || !date.equals(day.getDate()) || day.getOffices() == null) {
                    continue;
                }
                for (OfficeSlot office : day.getOffices()) {
                    if (office != null && office.matchesOfficeId(officeId)) {
                        AvailableAppointmentsResponse.OfficeAppointments officeAppointments =
                            new AvailableAppointmentsResponse.OfficeAppointments();
                        officeAppointments.setOfficeId(officeId);
                        officeAppointments.setAppointments(office.getAppointments());
                        matchingOffices.add(officeAppointments);
                    }
                }
            }
        }

        response.setOffices(matchingOffices);
        return response;
    }

    /** First day that already has free-slot appointments for the given office bucket. */
    public String getFirstAvailableDayForOffice(int officeId) {
        if (availableDays == null) {
            return null;
        }
        for (CalendarDay day : availableDays) {
            if (day == null || day.getDate() == null || day.getOffices() == null) {
                continue;
            }
            for (OfficeSlot office : day.getOffices()) {
                if (office != null
                        && office.matchesOfficeId(officeId)
                        && office.getAppointments() != null
                        && !office.getAppointments().isEmpty()) {
                    return day.getDate();
                }
            }
        }
        return null;
    }

    /** True when any day has at least one appointment timestamp for each given office id. */
    public boolean hasAppointmentsForAllOffices(int... officeIds) {
        if (officeIds == null || officeIds.length == 0) {
            return false;
        }
        for (int officeId : officeIds) {
            if (getFirstAvailableDayForOffice(officeId) == null) {
                return false;
            }
        }
        return true;
    }

    @Data
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class CalendarDay {
        /** ISO date YYYY-MM-DD (citizenapi availableCalendar schema). */
        private String date;
        private String providerIDs;
        private List<OfficeSlot> offices;
    }

    @Data
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class OfficeSlot {
        private Object officeId;
        private List<Long> appointments;

        public boolean matchesOfficeId(int expectedOfficeId) {
            if (officeId == null) {
                return false;
            }
            if (officeId instanceof Number number) {
                return number.intValue() == expectedOfficeId;
            }
            try {
                return Integer.parseInt(officeId.toString()) == expectedOfficeId;
            } catch (NumberFormatException e) {
                return false;
            }
        }
    }
}
