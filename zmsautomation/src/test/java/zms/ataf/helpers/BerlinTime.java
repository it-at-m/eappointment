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
}
