package zms.ataf.rest.dto.zmscitizenapi;

import java.util.ArrayList;
import java.util.List;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import lombok.Data;

/**
 * Response data for GET /available-calendar-by-office/.
 */
@Data
@JsonIgnoreProperties(ignoreUnknown = true)
public class AvailableCalendarByOfficeResponse {

    private String startDate;
    private String endDate;
    private List<CalendarDay> availableDays;

    public String getFirstAvailableDay() {
        if (availableDays == null || availableDays.isEmpty()) {
            return null;
        }
        CalendarDay first = availableDays.get(0);
        return first != null ? first.getTime() : null;
    }

    public AvailableAppointmentsResponse getAppointmentsForDayAndOffice(String date, int officeId) {
        AvailableAppointmentsResponse response = new AvailableAppointmentsResponse();
        List<AvailableAppointmentsResponse.OfficeAppointments> matchingOffices = new ArrayList<>();

        if (availableDays != null) {
            for (CalendarDay day : availableDays) {
                if (day == null || !date.equals(day.getTime()) || day.getOffices() == null) {
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

    @Data
    @JsonIgnoreProperties(ignoreUnknown = true)
    public static class CalendarDay {
        private String time;
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
