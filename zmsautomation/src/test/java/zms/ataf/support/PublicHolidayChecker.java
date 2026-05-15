package zms.ataf.support;

import java.io.IOException;
import java.io.InputStream;
import java.nio.charset.StandardCharsets;
import java.time.LocalDate;
import java.time.ZoneId;
import java.util.Set;
import java.util.regex.Pattern;
import java.util.stream.Collectors;

/**
 * Detects whether today is a public holiday according to zmsautomation feiertage test data (V10 migration).
 */
public final class PublicHolidayChecker {

    private static final ZoneId ZONE = ZoneId.of("Europe/Berlin");
    private static final Pattern DATE_IN_INSERT = Pattern.compile("'(\\d{4}-\\d{2}-\\d{2})'");
    private static final String HOLIDAY_MIGRATION = "db/migration/V10__add_day_off_holidays_test_data.sql";
    private static final Set<LocalDate> HOLIDAYS = loadHolidays();

    private PublicHolidayChecker() {}

    public static boolean isTodayPublicHoliday() {
        if (Boolean.parseBoolean(System.getenv().getOrDefault("ZMS_FORCE_PUBLIC_HOLIDAY", "false"))) {
            return true;
        }
        if (Boolean.parseBoolean(System.getenv().getOrDefault("ZMS_FORCE_NOT_PUBLIC_HOLIDAY", "false"))) {
            return false;
        }
        return isPublicHoliday(LocalDate.now(ZONE));
    }

    public static boolean isPublicHoliday(LocalDate date) {
        return HOLIDAYS.contains(date);
    }

    private static Set<LocalDate> loadHolidays() {
        try (InputStream in = PublicHolidayChecker.class.getClassLoader().getResourceAsStream(HOLIDAY_MIGRATION)) {
            if (in == null) {
                throw new IllegalStateException("Holiday migration not on classpath: " + HOLIDAY_MIGRATION);
            }
            String sql = new String(in.readAllBytes(), StandardCharsets.UTF_8);
            return DATE_IN_INSERT.matcher(sql).results()
                    .map(match -> LocalDate.parse(match.group(1)))
                    .collect(Collectors.toUnmodifiableSet());
        } catch (IOException e) {
            throw new IllegalStateException("Failed to read holiday migration: " + HOLIDAY_MIGRATION, e);
        }
    }
}
