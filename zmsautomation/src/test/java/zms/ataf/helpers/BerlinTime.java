package zms.ataf.helpers;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.LocalDate;
import java.time.LocalTime;
import java.time.ZoneId;

/**
 * Calendar and clock used when zmsautomation compares dates to PHP or MariaDB.
 * Both zms-web and zms-db use {@code TZ=Europe/Berlin} (GH-3257).
 */
public final class BerlinTime {
    public static final ZoneId ZONE = ZoneId.of("Europe/Berlin");

    /** V43 single-seat opening hours on Passkalender Standort 172 / office 10502. */
    private static final int SINGLE_SEAT_OPENING_HOURS_ID = 136510;

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
     * Startdatum seeded by {@code V43__ZMSKVR-88_ZMSKVR-472_single_seat_after_v19.sql} (row
     * {@value #SINGLE_SEAT_OPENING_HOURS_ID}). Read from the DB so API and UI share the same day Flyway
     * wrote at suite start, instead of recalculating after 20:55 / midnight.
     */
    public static LocalDate singleSeatDayAfterV19RuppertstrasseRange() {
        try (Connection connection = openZmsConnection();
                PreparedStatement statement =
                        connection.prepareStatement(
                                "SELECT Startdatum FROM oeffnungszeit WHERE OeffnungszeitID = ?")) {
            statement.setInt(1, SINGLE_SEAT_OPENING_HOURS_ID);
            try (ResultSet rows = statement.executeQuery()) {
                if (!rows.next()) {
                    throw new IllegalStateException(
                            "V43 single-seat opening hours (OeffnungszeitID "
                                    + SINGLE_SEAT_OPENING_HOURS_ID
                                    + ") not found. Run Flyway migrate first.");
                }
                java.sql.Date start = rows.getDate(1);
                if (start == null) {
                    throw new IllegalStateException(
                            "V43 single-seat Startdatum is null for OeffnungszeitID "
                                    + SINGLE_SEAT_OPENING_HOURS_ID);
                }
                return start.toLocalDate();
            }
        } catch (SQLException e) {
            throw new IllegalStateException(
                    "Could not read V43 single-seat Startdatum from oeffnungszeit.", e);
        }
    }

    private static Connection openZmsConnection() throws SQLException {
        String host = envOrDefault("MYSQL_HOST", "db");
        String port = mysqlPort(envOrDefault("MYSQL_PORT", "3306"));
        String database = envOrDefault("MYSQL_DATABASE", "db");
        String user = envOrDefault("MYSQL_USER", "db");
        String url = "jdbc:mysql://" + host + ":" + port + "/" + database;
        return DriverManager.getConnection(url, user, envOrDefault("MYSQL_PASSWORD", "db"));
    }

    private static String mysqlPort(String raw) {
        int colon = raw.lastIndexOf(':');
        if (colon >= 0 && colon < raw.length() - 1) {
            return raw.substring(colon + 1);
        }
        return raw;
    }

    private static String envOrDefault(String name, String fallback) {
        String value = System.getenv(name);
        if (value == null || value.isBlank()) {
            return fallback;
        }
        return value.trim();
    }
}
