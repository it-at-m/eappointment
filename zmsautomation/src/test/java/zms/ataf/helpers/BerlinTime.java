package zms.ataf.helpers;

import java.time.LocalDate;
import java.time.LocalTime;
import java.time.ZoneId;

/**
 * Calendar and clock used when zmsautomation compares dates to PHP or MariaDB.
 * Both zms-web and zms-db use {@code TZ=Europe/Berlin} (GH-3257).
 */
public final class BerlinTime {
    public static final ZoneId ZONE = ZoneId.of("Europe/Berlin");

    private BerlinTime() {
        throw new UnsupportedOperationException("Utility class");
    }

    public static LocalDate today() {
        return LocalDate.now(ZONE);
    }

    public static LocalTime now() {
        return LocalTime.now(ZONE);
    }

    /**
     * Day after {@code V19} Ruppertstraße {@code @range_end} — same math as
     * {@code V42__ZMSKVR-88_ZMSKVR-472_single_seat_after_v19.sql} (one internet seat on Pass 172 / office 10502).
     */
    public static LocalDate singleSeatDayAfterV19RuppertstrasseRange() {
        LocalTime latestEnd = LocalTime.of(23, 55);
        long slotSeconds = 300L;
        long nowSec = now().toSecondOfDay();
        long roundedStartSec = ((nowSec + slotSeconds - 1) / slotSeconds) * slotSeconds;
        boolean useNextDay =
                roundedStartSec >= 24 * 3600
                        || latestEnd.toSecondOfDay() <= roundedStartSec
                        || (latestEnd.toSecondOfDay() - roundedStartSec) < 3 * 3600;
        LocalDate v19RangeEnd = today().plusDays(useNextDay ? 8 : 7);
        return v19RangeEnd.plusDays(1);
    }
}
