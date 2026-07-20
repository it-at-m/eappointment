package zms.ataf.rest.dto.zmscitizenapi;

import java.util.List;
import java.util.Map;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;

import lombok.Data;

/**
 * Response data for GET /available-days/ and GET /available-days-by-office/.
 * By-office may return availableDays as array of objects with "time" and "providerIDs";
 * non-by-office returns array of date strings (YYYY-MM-DD).
 * Use {@link #getFirstAvailableDay()} to obtain the first date for the test.
 */
@Data
@JsonIgnoreProperties(ignoreUnknown = true)
public class AvailableDaysResponse {

    private List<Object> availableDays;

    /**
     * Returns the first available day as YYYY-MM-DD for use in the next step.
     * Handles both plain string elements and object elements with "time" (by-office).
     */
    @SuppressWarnings("unchecked")
    public String getFirstAvailableDay() {
        if (availableDays == null || availableDays.isEmpty()) {
            return null;
        }
        Object first = availableDays.get(0);
        if (first instanceof String) {
            return (String) first;
        }
        if (first instanceof Map) {
            Object time = ((Map<String, Object>) first).get("time");
            return time != null ? time.toString() : null;
        }
        return null;
    }
}
