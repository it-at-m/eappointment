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

    /** V52 WB04 one-seat day for same-timestamp Wartebereich switch (office 10489). */
    private static final int SCOPE_SWITCH_OPENING_HOURS_ID = 136511;

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
     * Start date seeded by {@code V43__ZMSKVR-88_ZMSKVR-472_single_seat_after_v19.sql} (row
     * {@value #SINGLE_SEAT_OPENING_HOURS_ID}). Read from the DB so API and UI share the same day Flyway
     * wrote at suite start, instead of recalculating after 20:55 / midnight.
     *
     * <p>After zmsbackend expand/contract migrations, {@code Startdatum} is dropped and only
     * {@code start_date} remains. Prefer the English column when present.
     */
    public static LocalDate singleSeatDayAfterV19RuppertstrasseRange() {
        return openingHoursStartDate(
                SINGLE_SEAT_OPENING_HOURS_ID, "V43 single-seat opening hours");
    }

    /**
     * Start date seeded by {@code V52__ZMSKVR-1051_ZMSKVR-1309_scope_switch_day.sql} (row
     * {@value #SCOPE_SWITCH_OPENING_HOURS_ID}): one internet seat each on WB03/WB04 (and Schalter
     * A/B) so a second same-timestamp reserve switches scope without fighting the shared V19
     * calendar.
     */
    public static LocalDate scopeSwitchDayAfterV19RuppertstrasseRange() {
        return openingHoursStartDate(
                SCOPE_SWITCH_OPENING_HOURS_ID, "V52 scope-switch opening hours");
    }

    private static LocalDate openingHoursStartDate(int openingHoursId, String label) {
        try (Connection connection = openZmsConnection()) {
            String startColumn = columnExists(connection, "start_date") ? "start_date" : "Startdatum";
            try (PreparedStatement statement =
                    connection.prepareStatement(
                            "SELECT "
                                    + startColumn
                                    + " FROM oeffnungszeit WHERE OeffnungszeitID = ?")) {
                statement.setInt(1, openingHoursId);
                try (ResultSet rows = statement.executeQuery()) {
                    if (!rows.next()) {
                        throw new IllegalStateException(
                                label
                                        + " (OeffnungszeitID "
                                        + openingHoursId
                                        + ") not found. Run Flyway migrate first.");
                    }
                    java.sql.Date start = rows.getDate(1);
                    if (start == null) {
                        throw new IllegalStateException(
                                label
                                        + " "
                                        + startColumn
                                        + " is null for OeffnungszeitID "
                                        + openingHoursId);
                    }
                    return start.toLocalDate();
                }
            }
        } catch (SQLException e) {
            throw new IllegalStateException(
                    "Could not read " + label + " start date from oeffnungszeit.", e);
        }
    }

    private static boolean columnExists(Connection connection, String column) throws SQLException {
        try (PreparedStatement statement =
                connection.prepareStatement(
                        "SELECT COUNT(*) FROM information_schema.COLUMNS "
                                + "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'oeffnungszeit' "
                                + "AND COLUMN_NAME = ?")) {
            statement.setString(1, column);
            try (ResultSet rows = statement.executeQuery()) {
                rows.next();
                return rows.getInt(1) > 0;
            }
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
