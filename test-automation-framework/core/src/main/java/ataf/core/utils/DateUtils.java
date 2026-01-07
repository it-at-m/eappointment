package ataf.core.utils;

import java.security.SecureRandom;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.LocalTime;
import java.time.ZoneId;
import java.time.format.DateTimeFormatter;
import java.time.temporal.ChronoUnit;
import java.util.Locale;

/**
 * Utility class for date and time operations. This class provides methods to generate formatted
 * timestamps, calculate dates with offsets, and generate random
 * birth dates within a specified age range.
 *
 * <p>
 * All date and time calculations are performed using the "Europe/Berlin" time zone.
 * </p>
 *
 * @author Ludwig Haas (ex.haas02)
 */
public class DateUtils {

    /**
     * Generates a timestamp string based on the current date and time. The format of the timestamp is
     * "yyyyMMdd_HHmmss".
     *
     * @return A formatted string representing the current timestamp. For example, "20230812_153045" for
     *         August 12, 2023, at 15:30:45.
     */
    public static String getFileTimestamp() {
        LocalDateTime localDateTime = LocalDateTime.now(ZoneId.of("Europe/Berlin"));
        String year = String.valueOf(localDateTime.getYear());
        String month = String.valueOf(localDateTime.getMonthValue());
        if (month.length() == 1) {
            month = "0" + month;
        }
        String day = String.valueOf(localDateTime.getDayOfMonth());
        if (day.length() == 1) {
            day = "0" + day;
        }
        String hour = String.valueOf(localDateTime.getHour());
        if (hour.length() == 1) {
            hour = "0" + hour;
        }
        String minute = String.valueOf(localDateTime.getMinute());
        if (minute.length() == 1) {
            minute = "0" + minute;
        }
        String second = String.valueOf(localDateTime.getSecond());
        if (second.length() == 1) {
            second = "0" + second;
        }
        return year + month + day + "_" + hour + minute + second;
    }

    /**
     * Calculates a date-time based on the current date-time with an offset. The offset can be positive
     * or negative and is applied using the specified time unit
     * (e.g., hours, minutes, seconds).
     *
     * @param offset The amount to adjust the current date-time by. This value can be positive,
     *            negative, or zero. A positive value adds time, while a
     *            negative value subtracts time.
     * @param chronoUnit The unit of time to apply the offset (e.g., hours, minutes, seconds). Must not
     *            be null.
     * @return A {@link LocalDateTime} object representing the calculated date-time. If the offset is
     *         zero, the current date-time is returned.
     * @throws NullPointerException if chronoUnit is null.
     */
    public static LocalDateTime getDateTimeWithOffset(int offset, ChronoUnit chronoUnit) {
        LocalDateTime localDateTime = LocalDateTime.now(ZoneId.of("Europe/Berlin"));
        if (chronoUnit == null || offset == 0) {
            return localDateTime;
        } else if (offset > 0) {
            return localDateTime.plus(offset, chronoUnit);
        } else {
            return localDateTime.minus(-offset, chronoUnit);
        }
    }

    /**
     * Retrieves the current date with an offset applied. Uses
     * {@link #getDateTimeWithOffset(int, ChronoUnit)} and extracts the LocalDate.
     *
     * @param offset The amount to adjust the current date by.
     * @param chronoUnit The unit of time to apply the offset.
     * @return A {@link LocalDate} object representing the calculated date.
     * @throws NullPointerException if chronoUnit is null.
     */
    public static LocalDate getDateWithOffset(int offset, ChronoUnit chronoUnit) {
        return getDateTimeWithOffset(offset, chronoUnit).toLocalDate();
    }

    /**
     * Retrieves the current time with an offset applied. Uses
     * {@link #getDateTimeWithOffset(int, ChronoUnit)} and extracts the LocalTime.
     *
     * @param offset The amount to adjust the current time by.
     * @param chronoUnit The unit of time to apply the offset.
     * @return A {@link LocalTime} object representing the calculated time.
     * @throws NullPointerException if chronoUnit is null.
     */
    public static LocalTime getTimeWithOffset(int offset, ChronoUnit chronoUnit) {
        return getDateTimeWithOffset(offset, chronoUnit).toLocalTime();
    }

    /**
     * Generates a random birth date within a specified age range. The age range is defined by the
     * minimum and maximum ages.
     *
     * @param ageMin The minimum age (inclusive) of the generated birth date.
     * @param ageMax The maximum age (inclusive) of the generated birth date.
     * @return A {@link LocalDate} object representing a randomly generated birth date. The birth date
     *         will be calculated such that it falls within the
     *         specified age range.
     * @throws IllegalArgumentException if ageMin is greater than ageMax.
     */
    public static LocalDate getRandomBirthDate(int ageMin, int ageMax) {
        if (ageMin > ageMax) {
            throw new IllegalArgumentException("ageMin cannot be greater than ageMax");
        }

        LocalDate currentLocalDate = LocalDate.now(ZoneId.of("Europe/Berlin"));
        SecureRandom secureRandom = new SecureRandom();
        int yearOfBirth = currentLocalDate.getYear() - (secureRandom.nextInt(ageMax - ageMin + 1) + ageMin);
        int monthOfBirth = secureRandom.nextInt(12) + 1;
        int dayOfBirth = secureRandom.nextInt(currentLocalDate.minusYears(yearOfBirth).withMonth(monthOfBirth).lengthOfMonth()) + 1;
        return LocalDate.parse(dayOfBirth + "." + monthOfBirth + "." + yearOfBirth, DateTimeFormatter.ofPattern("d.M.yyyy", Locale.GERMAN));
    }
}
