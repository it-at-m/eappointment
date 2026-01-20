package ataf.core.utils;

import ataf.core.clients.HttpClient;
import ataf.core.logging.ScenarioLogManager;
import com.google.gson.Gson;
import com.google.gson.JsonArray;
import com.google.gson.JsonElement;
import com.google.gson.JsonObject;

import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.format.DateTimeFormatter;
import java.util.ArrayList;
import java.util.List;

/**
 * Utility class for fetching holiday dates and calculating business days. This class retrieves
 * public holiday dates from an external API and provides
 * functionality to determine the previous business day considering weekends and holidays.
 *
 * <p>
 * The current implementation fetches holidays specific to Bavaria (BY) in Germany.
 * </p>
 *
 * @author Mohamad Daaeboul
 */
public class HolidayUtil {

    private static final String TARGET_URL = "https://get.api-feiertage.de?states=by";

    /**
     * Fetches public holiday dates from an external API. The method performs an HTTP GET request to
     * retrieve holiday data, parses the response, and returns a
     * list of holiday dates.
     *
     * @return A list of {@link LocalDate} representing public holidays. Returns null if the API call is
     *         unsuccessful or an error occurs.
     */
    public static List<LocalDate> fetchDates() {
        try (HttpClient httpClient = new HttpClient(HttpClient.PROXY_PAC_URL, TARGET_URL)) {
            String jsonResult = httpClient.executeHttpGetRequest(TARGET_URL, HttpClient.AuthenticationMethod.None);

            Gson gson = new Gson();
            JsonObject parsedResponse = gson.fromJson(jsonResult, JsonObject.class);

            // Check if the API call was successful
            if (!"success".equals(parsedResponse.get("status").getAsString())) {
                ScenarioLogManager.getLogger().error("API call unsuccessful: {}", parsedResponse);
                return null;
            }

            JsonArray holidays = parsedResponse.getAsJsonArray("feiertage");
            List<LocalDate> dates = new ArrayList<>();
            for (JsonElement element : holidays) {
                JsonObject holiday = element.getAsJsonObject();
                String dateString = holiday.get("date").getAsString();
                LocalDate date = LocalDate.parse(dateString, DateTimeFormatter.ISO_DATE); // Parse string to LocalDate
                dates.add(date);
            }

            return dates;
        } catch (Exception e) {
            ScenarioLogManager.getLogger().error("Error fetching holiday dates:", e);
            return null;
        }
    }

    /**
     * Calculates the previous business day, considering weekends and public holidays. The method checks
     * the current date and iterates backward to find the last
     * business day.
     *
     * @return A {@link LocalDate} representing the previous business day. If today is a business day,
     *         it will return yesterday; otherwise, it will find the
     *         last business day before that.
     */
    public static LocalDate getPreviousBusinessDay() {
        List<LocalDate> holidays = HolidayUtil.fetchDates();
        LocalDate previousDay = LocalDate.now().minusDays(1);

        // Continue to find the previous business day if it falls on a weekend or holiday
        while (previousDay.getDayOfWeek() == DayOfWeek.SATURDAY || previousDay.getDayOfWeek() == DayOfWeek.SUNDAY || (holidays != null && holidays.contains(
                previousDay))) {
            previousDay = previousDay.minusDays(1);
        }

        return previousDay;
    }
}
