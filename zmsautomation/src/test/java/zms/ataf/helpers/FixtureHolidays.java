package zms.ataf.helpers;

import java.time.LocalDate;
import java.util.HashSet;
import java.util.Set;

/**
 * Closed days inserted by {@code V11__add_day_off_holidays_test_data.sql} for every authority
 * ({@code BehoerdenID = 0}). 2026-10-03 is commented out in that migration and is not closed.
 * Keep this set in step with the inserted rows.
 */
public final class FixtureHolidays {
    private static final Set<LocalDate> CLOSED = parse(
            "2025-08-15",
            "2025-10-03",
            "2025-11-01",
            "2025-12-24",
            "2025-12-25",
            "2025-12-26",
            "2025-12-31",
            "2026-01-01",
            "2026-01-06",
            "2026-04-03",
            "2026-04-06",
            "2026-05-01",
            "2026-05-14",
            "2026-05-25",
            "2026-06-04",
            "2026-08-15",
            "2026-11-01",
            "2026-12-24",
            "2026-12-25",
            "2026-12-26",
            "2026-12-31",
            "2027-01-01",
            "2027-01-06",
            "2027-03-26",
            "2027-03-29",
            "2027-05-01",
            "2027-05-06",
            "2027-05-17",
            "2027-05-27",
            "2027-08-15",
            "2027-10-03",
            "2027-11-01",
            "2027-12-24",
            "2027-12-25",
            "2027-12-26",
            "2027-12-31",
            "2028-01-01",
            "2028-01-06",
            "2028-04-14",
            "2028-04-17",
            "2028-05-01",
            "2028-05-25",
            "2028-06-05",
            "2028-06-15",
            "2028-08-15",
            "2028-10-03",
            "2028-11-01",
            "2028-12-24",
            "2028-12-25",
            "2028-12-26",
            "2028-12-31");

    private FixtureHolidays() {
        throw new UnsupportedOperationException("Utility class");
    }

    public static boolean isClosed(LocalDate day) {
        return CLOSED.contains(day);
    }

    /** First day on or after {@code day} that V11 does not close. */
    public static LocalDate firstOpenOnOrAfter(LocalDate day) {
        LocalDate cursor = day;
        while (CLOSED.contains(cursor)) {
            cursor = cursor.plusDays(1);
        }
        return cursor;
    }

    /**
     * Same offsets the capacity scenario asks for, moved forward when that day is a V11 holiday.
     * A range that would otherwise end before it starts collapses to the open start day.
     */
    public static LocalDate[] openRangeDaysAfterToday(int fromOffset, int untilOffset) {
        LocalDate today = BerlinTime.today();
        LocalDate from = firstOpenOnOrAfter(today.plusDays(fromOffset));
        LocalDate until = firstOpenOnOrAfter(today.plusDays(untilOffset));
        if (until.isBefore(from)) {
            until = from;
        }
        return new LocalDate[] {from, until};
    }

    private static Set<LocalDate> parse(String... isoDays) {
        Set<LocalDate> days = new HashSet<>();
        for (String isoDay : isoDays) {
            days.add(LocalDate.parse(isoDay));
        }
        return Set.copyOf(days);
    }
}
